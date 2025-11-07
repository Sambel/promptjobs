<?php

namespace App\Services\JobApis;

use App\Services\AiJobFilterService;
use App\Services\RemoteDetectionService;
use App\Services\TextCleanerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HimalayasService implements JobApiInterface
{
    private const API_URL = 'https://himalayas.app/jobs/api';
    private const SOURCE_NAME = 'himalayas';

    public function fetchJobs(): array
    {
        try {
            $allJobs = [];
            $offset = 0;
            $limit = 20;
            $maxJobs = 100; // Limit total jobs to avoid rate limiting

            while (count($allJobs) < $maxJobs) {
                $response = Http::timeout(30)->get(self::API_URL, [
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

                if (!$response->successful()) {
                    Log::error('Himalayas API error', ['status' => $response->status(), 'offset' => $offset]);
                    break;
                }

                $data = $response->json();

                if (!isset($data['jobs']) || !is_array($data['jobs']) || empty($data['jobs'])) {
                    break;
                }

                foreach ($data['jobs'] as $job) {
                    $transformed = $this->transformJob($job);

                    // Only include AI-related jobs
                    if (AiJobFilterService::isAiRelated($transformed['title'], $transformed['description'])) {
                        $allJobs[] = $transformed;
                    }
                }

                $offset += $limit;

                // If we got less than limit, we've reached the end
                if (count($data['jobs']) < $limit) {
                    break;
                }

                // Small delay to respect rate limits
                usleep(500000); // 0.5 seconds
            }

            Log::info('Himalayas jobs filtered', [
                'ai_related' => count($allJobs),
            ]);

            return $allJobs;

        } catch (\Exception $e) {
            Log::error('Himalayas API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getSourceName(): string
    {
        return self::SOURCE_NAME;
    }

    private function transformJob(array $job): array
    {
        $location = $this->extractLocation($job);
        $description = $this->cleanDescription($job['description'] ?? '');

        return [
            'external_id' => $job['id'] ?? null,
            'source' => self::SOURCE_NAME,
            'source_url' => $job['url'] ?? null,
            'title' => $job['title'] ?? 'Untitled Position',
            'company' => $job['company']['name'] ?? 'Unknown Company',
            'company_logo' => $this->getCompanyLogo($job),
            'description' => $description,
            'location' => $location,
            'remote' => RemoteDetectionService::isRemote($location, $description),
            'job_type' => $this->determineJobType($job),
            'salary_range' => $this->extractSalary($job),
            'apply_url' => $job['url'] ?? '#',
            'tags' => $this->extractTags($job),
            'featured' => false,
            'published_at' => $this->parsePublishedDate($job),
        ];
    }

    private function getCompanyLogo(array $job): ?string
    {
        if (isset($job['company']['logo'])) {
            return $job['company']['logo'];
        }

        // Fallback to Clearbit
        if (isset($job['company']['name'])) {
            $domain = strtolower(str_replace(' ', '', $job['company']['name'])) . '.com';
            return "https://logo.clearbit.com/{$domain}";
        }

        return null;
    }

    private function extractLocation(array $job): ?string
    {
        $locations = [];

        if (isset($job['location'])) {
            $locations[] = $job['location'];
        }

        if (isset($job['geo_restriction'])) {
            $locations[] = $job['geo_restriction'];
        }

        if (empty($locations)) {
            return 'Remote';
        }

        return implode(', ', array_filter($locations));
    }


    private function determineJobType(array $job): string
    {
        $type = strtolower($job['type'] ?? '');

        if (str_contains($type, 'part-time') || str_contains($type, 'part time')) {
            return 'part-time';
        }

        if (str_contains($type, 'contract') || str_contains($type, 'freelance')) {
            return 'contract';
        }

        return 'full-time';
    }

    private function extractSalary(array $job): ?string
    {
        if (isset($job['salary'])) {
            return $job['salary'];
        }

        if (isset($job['salary_min']) && isset($job['salary_max'])) {
            $currency = $job['salary_currency'] ?? '$';
            return "{$currency}{$job['salary_min']}k - {$currency}{$job['salary_max']}k";
        }

        return null;
    }

    private function extractTags(array $job): array
    {
        $tags = [];

        // Extract from tags if available
        if (isset($job['tags']) && is_array($job['tags'])) {
            $tags = array_merge($tags, $job['tags']);
        }

        // Extract from skills
        if (isset($job['skills']) && is_array($job['skills'])) {
            $tags = array_merge($tags, $job['skills']);
        }

        // Extract from categories
        if (isset($job['category'])) {
            $tags[] = $job['category'];
        }

        // Extract keywords from title and description
        $text = strtolower(($job['title'] ?? '') . ' ' . ($job['description'] ?? ''));
        $keywords = ['AI', 'ML', 'Machine Learning', 'Deep Learning', 'NLP', 'Computer Vision',
                     'LLM', 'GPT', 'PyTorch', 'TensorFlow', 'Python', 'React', 'Node.js',
                     'TypeScript', 'JavaScript', 'Data Science', 'Backend', 'Frontend', 'DevOps'];

        foreach ($keywords as $keyword) {
            if (str_contains($text, strtolower($keyword)) && !in_array($keyword, $tags)) {
                $tags[] = $keyword;
            }
        }

        return TextCleanerService::cleanTags(array_unique(array_filter($tags)));
    }

    private function parsePublishedDate(array $job): \DateTime
    {
        if (isset($job['published_at'])) {
            try {
                return new \DateTime($job['published_at']);
            } catch (\Exception $e) {
                // Fallback
            }
        }

        if (isset($job['created_at'])) {
            try {
                return new \DateTime($job['created_at']);
            } catch (\Exception $e) {
                // Fallback
            }
        }

        return now();
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
}
