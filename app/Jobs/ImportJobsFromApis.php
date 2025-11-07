<?php

namespace App\Jobs;

use App\Models\Job;
use App\Services\JobApis\AiJobsNetService;
use App\Services\JobApis\RemotiveService;
use App\Services\JobApis\TheMuseService;
use App\Services\JobApis\HimalayasService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportJobsFromApis implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $services = [
            new AiJobsNetService(),
            new RemotiveService(),
            new TheMuseService(),
            new HimalayasService(),
        ];

        $totalImported = 0;
        $stats = [];

        foreach ($services as $service) {
            $sourceName = $service->getSourceName();
            Log::info("Starting import from {$sourceName}");

            try {
                $jobs = $service->fetchJobs();
                $imported = 0;
                $skipped = 0;

                foreach ($jobs as $jobData) {
                    try {
                        // Use external_id + source as unique identifier
                        $uniqueKey = [
                            'external_id' => $jobData['external_id'],
                            'source' => $jobData['source'],
                        ];

                        // Only update if external_id exists, otherwise always create
                        if ($jobData['external_id']) {
                            $job = Job::updateOrCreate($uniqueKey, $jobData);
                        } else {
                            // If no external_id, use apply_url as fallback unique key
                            $job = Job::updateOrCreate(
                                ['apply_url' => $jobData['apply_url']],
                                $jobData
                            );
                        }

                        // Sync locations after creating/updating job
                        $job->syncLocationsFromString($jobData['location'] ?? null);

                        $imported++;
                    } catch (\Exception $e) {
                        $skipped++;
                        Log::warning("Failed to import job from {$sourceName}", [
                            'error' => $e->getMessage(),
                            'job_title' => $jobData['title'] ?? 'unknown',
                        ]);
                    }
                }

                $stats[$sourceName] = [
                    'imported' => $imported,
                    'skipped' => $skipped,
                ];

                $totalImported += $imported;

                Log::info("Completed import from {$sourceName}", [
                    'imported' => $imported,
                    'skipped' => $skipped,
                ]);

            } catch (\Exception $e) {
                Log::error("Failed to fetch jobs from {$sourceName}", [
                    'error' => $e->getMessage(),
                ]);
                $stats[$sourceName] = [
                    'imported' => 0,
                    'skipped' => 0,
                    'error' => $e->getMessage(),
                ];
            }
        }

        Log::info("Job import completed", [
            'total_imported' => $totalImported,
            'stats' => $stats,
        ]);
    }
}
