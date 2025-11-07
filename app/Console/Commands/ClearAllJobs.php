<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class ClearAllJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:clear-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all jobs from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Job::count();

        if ($count === 0) {
            $this->info('No jobs to delete.');
            return Command::SUCCESS;
        }

        $this->info("Deleting all {$count} jobs...");

        Job::query()->delete();

        $this->info("✅ Successfully deleted {$count} jobs!");

        return Command::SUCCESS;
    }
}
