<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Services\RemoteDetectionService;
use Illuminate\Console\Command;

class UpdateRemoteStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:update-remote-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update remote status for all jobs using improved detection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating remote status for all jobs...');

        $jobs = Job::all();
        $updated = 0;
        $nowRemote = 0;

        foreach ($jobs as $job) {
            $oldRemote = $job->remote;
            $newRemote = RemoteDetectionService::isRemote($job->location, $job->description);

            if ($oldRemote !== $newRemote) {
                $job->remote = $newRemote;
                $job->save();
                $updated++;

                if ($newRemote && !$oldRemote) {
                    $nowRemote++;
                    $this->comment("✓ {$job->title} at {$job->company} - now marked as remote");
                }
            }
        }

        $this->info("Completed! Updated {$updated} jobs.");
        $this->info("Found {$nowRemote} additional remote jobs that weren't previously detected.");

        return Command::SUCCESS;
    }
}
