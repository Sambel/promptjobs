<?php

namespace App\Helpers;

class CountryFlagHelper
{
    /**
     * Map country names to their flag emojis
     */
    private static array $countryFlags = [
        // North America
        'United States' => '🇺🇸',
        'USA' => '🇺🇸',
        'Canada' => '🇨🇦',
        'Mexico' => '🇲🇽',

        // Europe
        'United Kingdom' => '🇬🇧',
        'UK' => '🇬🇧',
        'Germany' => '🇩🇪',
        'France' => '🇫🇷',
        'Spain' => '🇪🇸',
        'Italy' => '🇮🇹',
        'Netherlands' => '🇳🇱',
        'Belgium' => '🇧🇪',
        'Switzerland' => '🇨🇭',
        'Austria' => '🇦🇹',
        'Sweden' => '🇸🇪',
        'Norway' => '🇳🇴',
        'Denmark' => '🇩🇰',
        'Finland' => '🇫🇮',
        'Poland' => '🇵🇱',
        'Portugal' => '🇵🇹',
        'Greece' => '🇬🇷',
        'Ireland' => '🇮🇪',
        'Czech Republic' => '🇨🇿',
        'Romania' => '🇷🇴',
        'Hungary' => '🇭🇺',
        'Bulgaria' => '🇧🇬',
        'Croatia' => '🇭🇷',
        'Slovakia' => '🇸🇰',
        'Slovenia' => '🇸🇮',
        'Estonia' => '🇪🇪',
        'Latvia' => '🇱🇻',
        'Lithuania' => '🇱🇹',
        'Luxembourg' => '🇱🇺',
        'Malta' => '🇲🇹',
        'Cyprus' => '🇨🇾',
        'Iceland' => '🇮🇸',

        // Asia
        'China' => '🇨🇳',
        'Japan' => '🇯🇵',
        'India' => '🇮🇳',
        'South Korea' => '🇰🇷',
        'Singapore' => '🇸🇬',
        'Thailand' => '🇹🇭',
        'Vietnam' => '🇻🇳',
        'Malaysia' => '🇲🇾',
        'Indonesia' => '🇮🇩',
        'Philippines' => '🇵🇭',
        'Taiwan' => '🇹🇼',
        'Hong Kong' => '🇭🇰',
        'Pakistan' => '🇵🇰',
        'Bangladesh' => '🇧🇩',
        'Israel' => '🇮🇱',
        'United Arab Emirates' => '🇦🇪',
        'UAE' => '🇦🇪',
        'Saudi Arabia' => '🇸🇦',
        'Turkey' => '🇹🇷',

        // Oceania
        'Australia' => '🇦🇺',
        'New Zealand' => '🇳🇿',

        // South America
        'Brazil' => '🇧🇷',
        'Argentina' => '🇦🇷',
        'Chile' => '🇨🇱',
        'Colombia' => '🇨🇴',
        'Peru' => '🇵🇪',
        'Uruguay' => '🇺🇾',
        'Venezuela' => '🇻🇪',

        // Africa
        'South Africa' => '🇿🇦',
        'Nigeria' => '🇳🇬',
        'Kenya' => '🇰🇪',
        'Egypt' => '🇪🇬',
        'Morocco' => '🇲🇦',
        'Ghana' => '🇬🇭',
    ];

    /**
     * Get flag emoji for a country name
     */
    public static function getFlag(string $country): ?string
    {
        return self::$countryFlags[$country] ?? null;
    }

    /**
     * Get all country flags
     */
    public static function getAllFlags(): array
    {
        return self::$countryFlags;
    }
}
