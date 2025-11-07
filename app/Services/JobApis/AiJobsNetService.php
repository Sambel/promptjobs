<?php

namespace App\Services\JobApis;

use App\Services\JobDomainService;
use App\Services\RemoteDetectionService;
use App\Services\TextCleanerService;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiJobsNetService implements JobApiInterface
{
    private const API_URL = 'https://foorilla.com/hiring/';
    private const SOURCE_NAME = 'ai-jobs.net';

    public function fetchJobs(): array
    {
        // Note: ai-jobs.net API has been discontinued and now redirects to foorilla.com/hiring/
        // which is an HTML page, not a JSON API. Temporarily disabled until alternative found.
        Log::info('AiJobsNet API is currently unavailable (redirects to HTML page)');
        return [];

        /* Original implementation commented out
        try {
            $response = Http::timeout(30)->get(self::API_URL);

            if (!$response->successful()) {
                Log::error('AiJobsNet API error', ['status' => $response->status()]);
                return [];
            }

            $data = $response->json();

            if (!isset($data['jobs']) || !is_array($data['jobs'])) {
                Log::error('AiJobsNet API unexpected format', ['data' => $data]);
                return [];
            }

            return array_map(function ($job) {
                return $this->transformJob($job);
            }, $data['jobs']);

        } catch (\Exception $e) {
            Log::error('AiJobsNet API exception', ['message' => $e->getMessage()]);
            return [];
        }
        */
    }

    public function getSourceName(): string
    {
        return self::SOURCE_NAME;
    }

    private function transformJob(array $job): array
    {
        $title = $job['title'] ?? 'Untitled Position';
        $location = $job['location'] ?? null;
        $description = $job['description'] ?? '';

        return [
            'external_id' => $job['id'] ?? null,
            'source' => self::SOURCE_NAME,
            'source_url' => $job['url'] ?? null,
            'title' => $title,
            'company' => $job['company'] ?? 'Unknown Company',
            'company_logo' => $this->getCompanyLogo($job['company'] ?? null),
            'description' => $description,
            'location' => $location,
            'remote' => RemoteDetectionService::isRemote($location, $description),
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

        // Extract domain from company name (simple heuristic)
        $domain = strtolower(str_replace(' ', '', $company)) . '.com';
        return "https://logo.clearbit.com/{$domain}";
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

    private function extractTags(array $job): array
    {
        $tags = [];

        // Extract from tags if available
        if (isset($job['tags']) && is_array($job['tags'])) {
            $tags = array_merge($tags, $job['tags']);
        }

        // Extract from categories if available
        if (isset($job['categories']) && is_array($job['categories'])) {
            $tags = array_merge($tags, $job['categories']);
        }

        // Extract from title and description
        $text = strtolower(($job['title'] ?? '') . ' ' . ($job['description'] ?? ''));
        $keywords = ['AI', 'ML', 'Machine Learning', 'Deep Learning', 'NLP', 'Computer Vision',
                     'LLM', 'GPT', 'PyTorch', 'TensorFlow', 'Python', 'Data Science'];

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
                // Fallback to now
            }
        }

        if (isset($job['created_at'])) {
            try {
                return new \DateTime($job['created_at']);
            } catch (\Exception $e) {
                // Fallback to now
            }
        }

        return now();
    }
}
