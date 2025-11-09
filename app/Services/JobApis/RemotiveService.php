<?php

namespace App\Services\JobApis;

use App\Services\AiJobFilterService;
use App\Services\JobDomainService;
use App\Services\RemoteDetectionService;
use App\Services\TextCleanerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemotiveService implements JobApiInterface
{
    private const API_URL = 'https://remotive.com/api/remote-jobs';
    private const SOURCE_NAME = 'remotive';

    public function fetchJobs(): array
    {
        try {
            $response = Http::timeout(30)->get(self::API_URL, [
                'category' => 'software-dev',
                'limit' => 100,
            ]);

            if (!$response->successful()) {
                Log::error('Remotive API error', ['status' => $response->status()]);
                return [];
            }

            $data = $response->json();

            if (!isset($data['jobs']) || !is_array($data['jobs'])) {
                Log::error('Remotive API unexpected format', ['data' => $data]);
                return [];
            }

            // Filter and transform jobs
            $jobs = [];
            foreach ($data['jobs'] as $job) {
                // Skip jobs without a valid company name
                if (empty($job['company_name'] ?? null)) {
                    continue;
                }

                $transformed = $this->transformJob($job);

                // Only include AI-related jobs
                if (AiJobFilterService::isAiRelated($transformed['title'], $transformed['description'])) {
                    $jobs[] = $transformed;
                }
            }

            Log::info('Remotive jobs filtered', [
                'total' => count($data['jobs']),
                'ai_related' => count($jobs),
                'filtered_out' => count($data['jobs']) - count($jobs),
            ]);

            return $jobs;

        } catch (\Exception $e) {
            Log::error('Remotive API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getSourceName(): string
    {
        return self::SOURCE_NAME;
    }

    private function transformJob(array $job): array
    {
        $title = $job['title'] ?? 'Untitled Position';
        $location = $job['candidate_required_location'] ?? 'Remote';
        $description = $this->cleanDescription($job['description'] ?? '');

        return [
            'external_id' => $job['id'] ?? null,
            'source' => self::SOURCE_NAME,
            'source_url' => $job['url'] ?? null,
            'title' => $title,
            'company' => $job['company_name'] ?? 'Unknown Company',
            'company_logo' => $job['company_logo'] ?? $this->getCompanyLogo($job['company_name'] ?? null),
            'description' => $description,
            'location' => $location,
            'remote' => RemoteDetectionService::isRemote($location, $description) ?: true, // Remotive is all remote by default
            'job_type' => $this->determineJobType($job),
            'domain' => JobDomainService::detectDomain($title, $description),
            'salary_range' => $job['salary'] ?? null,
            'apply_url' => $job['url'] ?? '#',
            'tags' => $this->extractTags($job),
            'featured' => false,
            'published_at' => $this->parsePublishedDate($job),
        ];
    }

    private function getCompanyLogo(?string $company): ?string
    {
        if (!$company) {
            return null;
        }

        $domain = strtolower(str_replace(' ', '', $company)) . '.com';
        return "https://logo.clearbit.com/{$domain}";
    }

    private function determineJobType(array $job): string
    {
        $type = strtolower($job['job_type'] ?? '');

        if (str_contains($type, 'part-time') || str_contains($type, 'part time')) {
            return 'part-time';
        }

        if (str_contains($type, 'contract') || str_contains($type, 'freelance')) {
            return 'contract';
        }

        return 'full-time';
    }

    private function extractTags(array $job): array
    {
        $tags = [];

        // Extract from tags if available
        if (isset($job['tags']) && is_array($job['tags'])) {
            $tags = array_merge($tags, $job['tags']);
        }

        // Extract from category
        if (isset($job['category'])) {
            $tags[] = $job['category'];
        }

        // Extract keywords from title and description
        $text = strtolower(($job['title'] ?? '') . ' ' . ($job['description'] ?? ''));
        $keywords = ['AI', 'ML', 'Machine Learning', 'Deep Learning', 'NLP', 'Computer Vision',
                     'LLM', 'GPT', 'PyTorch', 'TensorFlow', 'Python', 'React', 'Node.js',
                     'TypeScript', 'JavaScript', 'Data Science', 'Backend', 'Frontend'];

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
