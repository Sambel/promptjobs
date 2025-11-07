<?php

namespace App\Services;

use App\Models\Location;

class LocationNormalizerService
{
    /**
     * Region to countries mapping
     */
    private static array $regionMappings = [
        'EMEA' => ['Europe', 'Middle East', 'Africa'],
        'Europe' => ['Austria', 'Belgium', 'Bulgaria', 'Croatia', 'Cyprus', 'Czech Republic', 'Denmark', 
                    'Estonia', 'Finland', 'France', 'Germany', 'Greece', 'Hungary', 'Ireland', 'Italy', 
                    'Latvia', 'Lithuania', 'Luxembourg', 'Malta', 'Netherlands', 'Poland', 'Portugal', 
                    'Romania', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'UK', 'United Kingdom', 'Norway', 
                    'Switzerland', 'Iceland', 'Ukraine', 'Georgia'],
        'Americas' => ['USA', 'United States', 'Canada', 'Mexico', 'Brazil', 'Argentina', 'Chile', 
                      'Colombia', 'Peru', 'Venezuela', 'Uruguay', 'Paraguay', 'Northern America', 'South America'],
        'Asia' => ['China', 'Japan', 'India', 'Singapore', 'South Korea', 'Thailand', 'Vietnam', 
                  'Malaysia', 'Indonesia', 'Philippines', 'Pakistan', 'Bangladesh'],
        'APAC' => ['Asia', 'Australia', 'New Zealand'],
        'LATAM' => ['Brazil', 'Argentina', 'Chile', 'Colombia', 'Peru', 'Mexico', 'Venezuela', 'Uruguay'],
        'Middle East' => ['United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Israel', 'Turkey'],
        'Africa' => ['South Africa', 'Nigeria', 'Kenya', 'Egypt', 'Morocco'],
    ];

    /**
     * Country name normalizations (handle synonyms)
     */
    private static array $countryNormalizations = [
        'UK' => 'United Kingdom',
        'USA' => 'United States',
        'U.S.' => 'United States',
        'U.S.A.' => 'United States',
        'UAE' => 'United Arab Emirates',
    ];

    /**
     * Normalize a location string and return array of location data
     *
     * @param string|null $locationString
     * @return array Array of [name, type, region_parent, timezone_based]
     */
    public static function normalize(?string $locationString): array
    {
        if (empty($locationString)) {
            return [[
                'name' => 'Worldwide',
                'type' => 'worldwide',
                'region_parent' => null,
                'timezone_based' => false,
            ]];
        }

        // Clean the string
        $locationString = TextCleanerService::cleanText($locationString);

        // Handle vague locations
        if (self::isVagueLocation($locationString)) {
            return [[
                'name' => 'Worldwide',
                'type' => 'worldwide',
                'region_parent' => null,
                'timezone_based' => false,
            ]];
        }

        // Handle timezone-based locations
        if (self::isTimezoneLocation($locationString)) {
            return self::normalizeTimezoneLocation($locationString);
        }

        // Split by comma or "and"
        $parts = preg_split('/[,&]|\s+and\s+/i', $locationString);
        $parts = array_map('trim', $parts);
        $parts = array_filter($parts);

        $results = [];
        $seenNames = [];

        foreach ($parts as $part) {
            $normalized = self::normalizeLocation($part);
            
            foreach ($normalized as $location) {
                // Avoid duplicates
                if (!in_array($location['name'], $seenNames)) {
                    $results[] = $location;
                    $seenNames[] = $location['name'];
                }
            }
        }

        return $results ?: [[
            'name' => 'Worldwide',
            'type' => 'worldwide',
            'region_parent' => null,
            'timezone_based' => false,
        ]];
    }

    /**
     * Normalize a single location part
     */
    private static function normalizeLocation(string $location): array
    {
        $location = trim($location);

        // Normalize country names
        if (isset(self::$countryNormalizations[$location])) {
            $location = self::$countryNormalizations[$location];
        }

        // Check if it's a region
        if (self::isRegion($location)) {
            $result = [[
                'name' => $location,
                'type' => 'region',
                'region_parent' => null,
                'timezone_based' => false,
            ]];

            // Also add all countries in that region
            if (isset(self::$regionMappings[$location])) {
                foreach (self::$regionMappings[$location] as $country) {
                    // If it's a sub-region, add it as region
                    if (self::isRegion($country)) {
                        $result[] = [
                            'name' => $country,
                            'type' => 'region',
                            'region_parent' => $location,
                            'timezone_based' => false,
                        ];
                    } else {
                        // It's a country
                        $result[] = [
                            'name' => $country,
                            'type' => 'country',
                            'region_parent' => $location,
                            'timezone_based' => false,
                        ];
                    }
                }
            }

            return $result;
        }

        // It's a country - determine its region
        $region = self::findRegionForCountry($location);

        return [[
            'name' => $location,
            'type' => 'country',
            'region_parent' => $region,
            'timezone_based' => false,
        ]];
    }

    /**
     * Check if location string is vague
     */
    private static function isVagueLocation(string $location): bool
    {
        $vagueTerms = ['worldwide', 'remote', 'flexible', 'global', 'anywhere'];
        $lower = strtolower($location);
        
        foreach ($vagueTerms as $term) {
            if (str_contains($lower, $term)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if location is timezone-based
     */
    private static function isTimezoneLocation(string $location): bool
    {
        return preg_match('/(CET|EST|PST|GMT|UTC|timezone)/i', $location) === 1;
    }

    /**
     * Normalize timezone-based location
     */
    private static function normalizeTimezoneLocation(string $location): array
    {
        // Try to guess region from timezone
        $region = 'Worldwide';
        
        if (preg_match('/CET|GMT|UTC/i', $location)) {
            $region = 'Europe';
        } elseif (preg_match('/EST|PST|CST/i', $location)) {
            $region = 'Americas';
        }

        return [[
            'name' => $region,
            'type' => 'timezone',
            'region_parent' => null,
            'timezone_based' => true,
        ]];
    }

    /**
     * Check if a location is a region
     */
    private static function isRegion(string $location): bool
    {
        return isset(self::$regionMappings[$location]);
    }

    /**
     * Find the region for a given country
     */
    private static function findRegionForCountry(string $country): ?string
    {
        foreach (self::$regionMappings as $region => $countries) {
            if (in_array($country, $countries, true)) {
                return $region;
            }
        }

        return null;
    }

    /**
     * Get or create location records from normalized data
     *
     * @param array $normalizedLocations
     * @return array Location IDs
     */
    public static function getOrCreateLocations(array $normalizedLocations): array
    {
        $locationIds = [];

        foreach ($normalizedLocations as $locationData) {
            $location = Location::firstOrCreate(
                ['name' => $locationData['name']],
                [
                    'type' => $locationData['type'],
                    'region_parent' => $locationData['region_parent'],
                    'timezone_based' => $locationData['timezone_based'],
                ]
            );

            $locationIds[] = $location->id;
        }

        return $locationIds;
    }
}
