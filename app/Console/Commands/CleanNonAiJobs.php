<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Services\AiJobFilterService;
use Illuminate\Console\Command;

class CleanNonAiJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:clean-non-ai {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove jobs that are not AI-related from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No jobs will be deleted');
        } else {
            $this->warn('⚠️  This will permanently delete non-AI jobs from the database');
            if (!$this->confirm('Do you want to continue?')) {
                $this->info('Cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('Analyzing all jobs...');

        $jobs = Job::all();
        $bar = $this->output->createProgressBar($jobs->count());
        $bar->start();

        $toDelete = [];
        $toKeep = [];

        foreach ($jobs as $job) {
            $isAiRelated = AiJobFilterService::isAiRelated($job->title, $job->description);

            if ($isAiRelated) {
                $toKeep[] = [
                    'id' => $job->id,
                    'title' => $job->title,
                    'company' => $job->company,
                ];
            } else {
                $toDelete[] = [
                    'id' => $job->id,
                    'title' => $job->title,
                    'company' => $job->company,
                    'source' => $job->source,
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Show summary
        $this->info("📊 Analysis complete!");
        $this->table(
            ['Metric', 'Count', 'Percentage'],
            [
                ['Total jobs', $jobs->count(), '100%'],
                ['AI-related jobs (keep)', count($toKeep), round(count($toKeep) / $jobs->count() * 100, 1) . '%'],
                ['Non-AI jobs (delete)', count($toDelete), round(count($toDelete) / $jobs->count() * 100, 1) . '%'],
            ]
        );

        // Show sample of jobs to be deleted
        if (count($toDelete) > 0) {
            $this->newLine();
            $this->warn('Sample of jobs to be deleted (first 10):');
            $this->table(
                ['ID', 'Company', 'Title', 'Source'],
                array_slice(array_map(function($job) {
                    return [
                        $job['id'],
                        $job['company'],
                        substr($job['title'], 0, 50) . (strlen($job['title']) > 50 ? '...' : ''),
                        $job['source'],
                    ];
                }, $toDelete), 0, 10)
            );

            if (count($toDelete) > 10) {
                $this->info('... and ' . (count($toDelete) - 10) . ' more');
            }
        }

        // Delete if not dry run
        if (!$dryRun && count($toDelete) > 0) {
            $this->newLine();
            $this->info('Deleting non-AI jobs...');

            $deleted = 0;
            $deleteBar = $this->output->createProgressBar(count($toDelete));
            $deleteBar->start();

            foreach ($toDelete as $jobData) {
                try {
                    Job::destroy($jobData['id']);
                    $deleted++;
                } catch (\Exception $e) {
                    $this->error("Failed to delete job {$jobData['id']}: " . $e->getMessage());
                }
                $deleteBar->advance();
            }

            $deleteBar->finish();
            $this->newLine(2);

            $this->info("✅ Successfully deleted {$deleted} non-AI jobs");
        } elseif ($dryRun && count($toDelete) > 0) {
            $this->newLine();
            $this->info('💡 Run without --dry-run to actually delete these jobs:');
            $this->comment('php artisan jobs:clean-non-ai');
        } elseif (count($toDelete) === 0) {
            $this->newLine();
            $this->info('✨ All jobs are AI-related! Nothing to clean.');
        }

        return Command::SUCCESS;
    }
}
