<?php

namespace App\Services\JobApis;

interface JobApiInterface
{
    /**
     * Fetch jobs from the API
     *
     * @return array
     */
    public function fetchJobs(): array;

    /**
     * Get the source name
     *
     * @return string
     */
    public function getSourceName(): string;
}
