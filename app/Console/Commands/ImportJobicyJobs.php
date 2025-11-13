<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Services\PromptEngineeringFilterService;
use App\Services\LocationNormalizerService;
use App\Services\TextCleanerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportJobicyJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:import-jobicy {--count=100 : Number of jobs to fetch} {--geo= : Geographic filter (e.g., usa, europe)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import remote jobs from Jobicy API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Jobicy jobs import...');

        $count = $this->option('count');
        $geo = $this->option('geo');

        // Build API URL with parameters
        $url = 'https://jobicy.com/api/v2/remote-jobs';
        $params = [
            'count' => $count,
        ];

        if ($geo) {
            $params['geo'] = $geo;
        }

        // Fetch jobs from API
        $this->info("Fetching {$count} jobs from Jobicy API...");

        try {
            $response = Http::get($url, $params);

            if (!$response->successful()) {
                $this->error('Failed to fetch jobs from Jobicy API');
                return Command::FAILURE;
            }

            $data = $response->json();
            $jobs = $data['jobs'] ?? [];

            if (empty($jobs)) {
                $this->warn('No jobs found in API response');
                return Command::SUCCESS;
            }

            $this->info("Found {$data['jobCount']} jobs. Processing...");

            $bar = $this->output->createProgressBar(count($jobs));
            $bar->start();

            $imported = 0;
            $skipped = 0;
            $updated = 0;
            $filteredOut = 0;

            foreach ($jobs as $jobData) {
                try {
                    // Skip jobs without a valid company name
                    if (empty($jobData['companyName'] ?? null)) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    // Filter out non-LLM/GenAI/Prompt Engineering jobs
                    $title = $jobData['jobTitle'] ?? '';
                    $description = ($jobData['jobDescription'] ?? '') . ' ' . ($jobData['jobExcerpt'] ?? '');

                    if (!PromptEngineeringFilterService::isLLMRelated($title, $description)) {
                        $filteredOut++;
                        $bar->advance();
                        continue;
                    }

                    // Detect categories for the job
                    $categories = PromptEngineeringFilterService::detectCategories($title, $description);
                    // Check if job already exists by external_id
                    $existingJob = Job::where('external_id', $jobData['id'])
                        ->where('source', 'jobicy')
                        ->first();

                    // Extract location from jobGeo or use "Remote"
                    $location = $jobData['jobGeo'] ?? 'Remote';

                    // Determine if job is remote
                    $remote = str_contains(strtolower($location), 'remote') ||
                              str_contains(strtolower($jobData['jobTitle'] ?? ''), 'remote');

                    // Extract job type
                    $jobType = !empty($jobData['jobType']) ? strtolower($jobData['jobType'][0]) : 'full-time';

                    // Build tags from jobIndustry
                    $tags = $jobData['jobIndustry'] ?? [];

                    // Prepare salary range
                    $salaryRange = null;
                    if (isset($jobData['salaryMin']) && isset($jobData['salaryMax'])) {
                        $currency = $jobData['salaryCurrency'] ?? 'USD';
                        $period = $jobData['salaryPeriod'] ?? 'yearly';
                        $salaryRange = number_format($jobData['salaryMin']) . ' - ' .
                                      number_format($jobData['salaryMax']) . ' ' .
                                      $currency . ' / ' . $period;
                    }

                    $jobAttributes = [
                        'external_id' => $jobData['id'],
                        'source' => 'jobicy',
                        'source_url' => $jobData['url'],
                        'title' => $jobData['jobTitle'],
                        'company' => $jobData['companyName'],
                        'company_logo' => $jobData['companyLogo'] ?? null,
                        'description' => $jobData['jobDescription'] ?? $jobData['jobExcerpt'] ?? '',
                        'location' => $location,
                        'remote' => $remote,
                        'job_type' => $jobType,
                        'salary_range' => $salaryRange,
                        'apply_url' => $jobData['url'],
                        'tags' => $tags,
                        'categories' => $categories,
                        'featured' => false,
                        'published_at' => isset($jobData['pubDate']) ? date('Y-m-d H:i:s', strtotime($jobData['pubDate'])) : now(),
                    ];

                    // Clean all text fields
                    $jobAttributes = TextCleanerService::cleanJobData($jobAttributes);

                    if ($existingJob) {
                        // Update existing job
                        $existingJob->update($jobAttributes);
                        $job = $existingJob;
                        $updated++;
                    } else {
                        // Create new job
                        $job = Job::create($jobAttributes);
                        $imported++;
                    }

                    // Normalize and sync locations
                    $job->syncLocationsFromString($location);

                } catch (\Exception $e) {
                    $this->error("Error processing job {$jobData['id']}: " . $e->getMessage());
                    $skipped++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("Import completed!");
            $this->table(
                ['Status', 'Count'],
                [
                    ['New jobs imported', $imported],
                    ['Existing jobs updated', $updated],
                    ['Jobs skipped (errors)', $skipped],
                    ['Jobs filtered out (non-AI)', $filteredOut],
                    ['Total fetched', count($jobs)],
                    ['Total AI jobs processed', $imported + $updated],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
