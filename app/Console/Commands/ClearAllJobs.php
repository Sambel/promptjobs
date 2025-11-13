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
    protected $signature = 'jobs:clear-all {--force : Force deletion without confirmation}';

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
            $this->info('No jobs to delete. Database is already empty.');
            return Command::SUCCESS;
        }

        // Ask for confirmation unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("⚠️  Are you sure you want to delete all {$count} jobs? This action cannot be undone.", false)) {
                $this->info('Operation cancelled.');
                return Command::FAILURE;
            }
        }

        $this->info("Deleting {$count} jobs...");

        Job::query()->delete();

        $this->info("✅ Successfully deleted {$count} jobs from the database.");
        $this->newLine();
        $this->comment('You can now import fresh jobs using:');
        $this->comment('  php artisan jobs:import');

        return Command::SUCCESS;
    }
}
