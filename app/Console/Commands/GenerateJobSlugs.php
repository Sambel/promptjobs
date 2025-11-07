<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class GenerateJobSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:generate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for all jobs that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jobs = Job::whereNull('slug')->get();

        if ($jobs->isEmpty()) {
            $this->info('All jobs already have slugs!');
            return Command::SUCCESS;
        }

        $this->info("Generating slugs for {$jobs->count()} jobs...");

        $progressBar = $this->output->createProgressBar($jobs->count());
        $progressBar->start();

        foreach ($jobs as $job) {
            $job->slug = Job::generateUniqueSlug($job->title, $job->company);
            $job->save();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Slugs generated successfully!');

        return Command::SUCCESS;
    }
}
