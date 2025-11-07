<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Services\LocationNormalizerService;
use Illuminate\Console\Command;

class NormalizeJobLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:normalize-locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize and migrate existing job location data to the locations table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting location normalization...');

        $jobs = Job::all();
        $this->info("Found {$jobs->count()} jobs to process.");

        $bar = $this->output->createProgressBar($jobs->count());
        $bar->start();

        $stats = [
            'processed' => 0,
            'locations_created' => 0,
            'relations_created' => 0,
            'skipped' => 0,
        ];

        foreach ($jobs as $job) {
            try {
                // Skip if already has normalized locations
                if ($job->locations()->count() > 0) {
                    $stats['skipped']++;
                    $bar->advance();
                    continue;
                }

                // Normalize the location string
                $normalizedLocations = LocationNormalizerService::normalize($job->location);

                // Get or create location records
                $locationIds = LocationNormalizerService::getOrCreateLocations($normalizedLocations);

                // Attach locations to job
                $job->locations()->attach($locationIds);

                $stats['processed']++;
                $stats['locations_created'] += count($normalizedLocations);
                $stats['relations_created'] += count($locationIds);

            } catch (\Exception $e) {
                $this->error("Error processing job {$job->id}: " . $e->getMessage());
                $stats['skipped']++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Location normalization completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Jobs processed', $stats['processed']],
                ['Jobs skipped (already normalized)', $stats['skipped']],
                ['Location entries created', $stats['locations_created']],
                ['Job-Location relations created', $stats['relations_created']],
            ]
        );

        // Show some sample locations
        $this->newLine();
        $this->info('Sample of created locations:');
        $sampleLocations = \App\Models\Location::take(10)->get(['name', 'type', 'region_parent']);
        $this->table(
            ['Name', 'Type', 'Region Parent'],
            $sampleLocations->map(fn($loc) => [$loc->name, $loc->type, $loc->region_parent ?? 'N/A'])->toArray()
        );

        return Command::SUCCESS;
    }
}
