<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Services\TextCleanerService;
use Illuminate\Console\Command;

class CleanJobTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:clean-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean HTML entities in job tags (e.g., &amp; to &)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning job tags...');

        $jobs = Job::whereNotNull('tags')->get();
        $this->info("Found {$jobs->count()} jobs with tags.");

        $bar = $this->output->createProgressBar($jobs->count());
        $bar->start();

        $updated = 0;

        foreach ($jobs as $job) {
            if ($job->tags && is_array($job->tags) && count($job->tags) > 0) {
                $originalTags = $job->tags;
                $cleanedTags = TextCleanerService::cleanTags($job->tags);

                // Only update if tags actually changed
                if ($originalTags !== $cleanedTags) {
                    $job->tags = $cleanedTags;
                    $job->save();
                    $updated++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $alreadyClean = $jobs->count() - $updated;

        $this->info("Cleaned tags for {$updated} jobs.");
        $this->info("{$alreadyClean} jobs already had clean tags.");

        return Command::SUCCESS;
    }
}
