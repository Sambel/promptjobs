<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Services\JobDomainService;
use Illuminate\Console\Command;

class UpdateJobDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:update-domains';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect and update domain for all jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Detecting domains for all jobs...');

        $jobs = Job::all();
        $updated = 0;
        $domainCounts = [];

        foreach ($jobs as $job) {
            $domain = JobDomainService::detectDomain($job->title, $job->description);

            if ($job->domain !== $domain) {
                $job->domain = $domain;
                $job->save();
                $updated++;
            }

            $domainCounts[$domain] = ($domainCounts[$domain] ?? 0) + 1;
        }

        $this->info("Completed! Updated {$updated} jobs.");
        $this->newLine();
        $this->info("Domain distribution:");

        arsort($domainCounts);
        foreach ($domainCounts as $domain => $count) {
            $label = JobDomainService::getDomainLabel($domain);
            $this->line("  {$label}: {$count} jobs");
        }

        return Command::SUCCESS;
    }
}
