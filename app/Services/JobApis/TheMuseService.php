<?php

namespace App\Services\JobApis;

use App\Services\PromptEngineeringFilterService;
use App\Services\JobDomainService;
use App\Services\RemoteDetectionService;
use App\Services\TextCleanerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TheMuseService implements JobApiInterface
{
    private const API_URL = 'https://www.themuse.com/api/public/jobs';
    private const SOURCE_NAME = 'themuse';

    public function fetchJobs(): array
    {
        try {
            $allJobs = [];
            $page = 0;
            $maxPages = 5; // Limit to first 5 pages (100 jobs)

            while ($page < $maxPages) {
                $response = Http::timeout(30)->get(self::API_URL, [
                    'category' => 'Data Science',
                    'page' => $page,
                ]);

                if (!$response->successful()) {
                    Log::error('TheMuse API error', ['status' => $response->status(), 'page' => $page]);
                    break;
                }

                $data = $response->json();

                if (!isset($data['results']) || !is_array($data['results']) || empty($data['results'])) {
                    break;
                }

                foreach ($data['results'] as $job) {
                    // Skip jobs without a valid company name
                    if (empty($job['company']['name'] ?? null)) {
                        continue;
                    }

                    $transformed = $this->transformJob($job);

                    // Only include LLM/GenAI/Prompt Engineering jobs
                    if (PromptEngineeringFilterService::isLLMRelated($transformed['title'], $transformed['description'])) {
                        // Detect and add categories
                        $transformed['categories'] = PromptEngineeringFilterService::detectCategories(
                            $transformed['title'],
                            $transformed['description']
                        );
                        $allJobs[] = $transformed;
                    }
                }

                $page++;

                // Check if there are more pages
                if ($data['page_count'] <= $page) {
                    break;
                }
            }

            Log::info('TheMuse jobs filtered', [
                'llm_related' => count($allJobs),
            ]);

            return $allJobs;

        } catch (\Exception $e) {
            Log::error('TheMuse API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getSourceName(): string
    {
        return self::SOURCE_NAME;
    }

    private function transformJob(array $job): array
    {
        $company = $job['company'] ?? [];
        $locations = $job['locations'] ?? [];
        $title = $job['name'] ?? 'Untitled Position';
        $location = $this->extractLocation($locations);
        $description = $this->extractDescription($job);

        return [
            'external_id' => $job['id'] ?? null,
            'source' => self::SOURCE_NAME,
            'source_url' => $job['refs']['landing_page'] ?? null,
            'title' => $title,
            'company' => $company['name'] ?? 'Unknown Company',
            'company_logo' => $this->getCompanyLogo($company),
            'description' => $description,
            'location' => $location,
            'remote' => RemoteDetectionService::isRemote($location, $description),
            'job_type' => $this->determineJobType($job),
            'domain' => JobDomainService::detectDomain($title, $description),
            'salary_range' => null, // TheMuse doesn't provide salary in API
            'apply_url' => $job['refs']['landing_page'] ?? '#',
            'tags' => $this->extractTags($job),
            'featured' => false,
            'published_at' => $this->parsePublishedDate($job),
        ];
    }

    private function getCompanyLogo(array $company): ?string
    {
        // TheMuse provides company logo in the API
        if (isset($company['refs']['logo'])) {
            return $company['refs']['logo'];
        }

        // Fallback to Clearbit
        if (isset($company['name'])) {
            $domain = strtolower(str_replace(' ', '', $company['name'])) . '.com';
            return "https://logo.clearbit.com/{$domain}";
        }

        return null;
    }

    private function extractDescription(array $job): string
    {
        $description = [];

        if (isset($job['contents'])) {
            $description[] = $this->cleanDescription($job['contents']);
        }

        if (empty($description)) {
            $description[] = 'No description available.';
        }

        return implode("\n\n", $description);
    }

    private function cleanDescription(string $html): string
    {
        // Replace block-level HTML elements with line breaks before stripping tags
        $html = preg_replace('/<\/(div|p|li|h[1-6]|br)>/i', "\n", $html);
        $html = preg_replace('/<(br|hr)\s*\/?>/i', "\n", $html);

        // Convert lists to readable format
        $html = preg_replace('/<li[^>]*>/i', "\n• ", $html);

        // Convert HTML to plain text
        $text = strip_tags($html);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Clean up excessive line breaks (more than 2 consecutive)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Remove spaces at the beginning/end of lines
        $text = preg_replace('/[ \t]+$/m', '', $text);
        $text = preg_replace('/^[ \t]+/m', '', $text);

        // Trim
        $text = trim($text);

        return $text;
    }

    private function extractLocation(array $locations): ?string
    {
        if (empty($locations)) {
            return 'Remote';
        }

        $location = $locations[0];
        return $location['name'] ?? 'Remote';
    }


    private function determineJobType(array $job): string
    {
        $type = strtolower($job['type'] ?? '');

        if (str_contains($type, 'part-time') || str_contains($type, 'part time')) {
            return 'part-time';
        }

        if (str_contains($type, 'contract') || str_contains($type, 'freelance') || str_contains($type, 'temporary')) {
            return 'contract';
        }

        return 'full-time';
    }

    private function extractTags(array $job): array
    {
        $tags = [];

        // Extract from categories
        if (isset($job['categories']) && is_array($job['categories'])) {
            foreach ($job['categories'] as $category) {
                if (isset($category['name'])) {
                    $tags[] = $category['name'];
                }
            }
        }

        // Extract from levels
        if (isset($job['levels']) && is_array($job['levels'])) {
            foreach ($job['levels'] as $level) {
                if (isset($level['name'])) {
                    $tags[] = $level['name'];
                }
            }
        }

        // Extract keywords from title and description
        $text = strtolower(($job['name'] ?? '') . ' ' . ($job['contents'] ?? ''));
        $keywords = ['AI', 'ML', 'Machine Learning', 'Deep Learning', 'NLP', 'Computer Vision',
                     'LLM', 'GPT', 'PyTorch', 'TensorFlow', 'Python', 'Data Science',
                     'Data Engineering', 'Analytics', 'Big Data', 'SQL'];

        foreach ($keywords as $keyword) {
            if (str_contains($text, strtolower($keyword)) && !in_array($keyword, $tags)) {
                $tags[] = $keyword;
            }
        }

        return TextCleanerService::cleanTags(array_unique(array_filter($tags)));
    }

    private function parsePublishedDate(array $job): \DateTime
    {
        if (isset($job['publication_date'])) {
            try {
                return new \DateTime($job['publication_date']);
            } catch (\Exception $e) {
                // Fallback
            }
        }

        return now();
    }
}
