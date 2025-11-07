<?php

namespace App\Console\Commands;

use App\Jobs\ImportJobsFromApis;
use Illuminate\Console\Command;

class ImportJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:import {--sync : Run import synchronously instead of dispatching to queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import jobs from external APIs (ai-jobs.net, Remotive, The Muse, Himalayas)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting job import from external APIs...');

        // Execute synchronously if --sync flag is provided, otherwise dispatch to queue
        if ($this->option('sync')) {
            $job = new ImportJobsFromApis();
            $job->handle();
            $this->info('Job import completed synchronously.');
        } else {
            ImportJobsFromApis::dispatch();
            $this->info('Job import has been dispatched to the queue.');
        }

        $this->comment('Check the logs for detailed import results.');

        return Command::SUCCESS;
    }
}
