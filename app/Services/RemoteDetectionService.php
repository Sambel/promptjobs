<?php

namespace App\Services;

class RemoteDetectionService
{
    /**
     * Detect if a job is remote based on location and description
     */
    public static function isRemote(?string $location, ?string $description): bool
    {
        $text = strtolower(($location ?? '') . ' ' . ($description ?? ''));

        // Common remote indicators
        $remoteKeywords = [
            'remote',
            'work from home',
            'wfh',
            'work from anywhere',
            'distributed',
            'telecommute',
            'home office',
            'remote-first',
            'remote first',
            'fully remote',
            '100% remote',
            'anywhere',
            'worldwide',
            'remote work',
            'remote position',
            'remote role',
            'remote opportunity',
        ];

        foreach ($remoteKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect if a job is hybrid based on description
     */
    public static function isHybrid(?string $location, ?string $description): bool
    {
        $text = strtolower(($location ?? '') . ' ' . ($description ?? ''));

        $hybridKeywords = [
            'hybrid',
            'flexible location',
            'remote/office',
            'office/remote',
            'remote and office',
            'office and remote',
            'flexible work',
        ];

        foreach ($hybridKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
