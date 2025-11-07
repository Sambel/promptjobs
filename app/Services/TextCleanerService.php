<?php

namespace App\Services;

class TextCleanerService
{
    /**
     * Clean HTML description and convert to plain text
     */
    public static function cleanDescription(string $html): string
    {
        // Replace block-level HTML elements with line breaks before stripping tags
        $html = preg_replace('/<\/(div|p|li|h[1-6]|br)>/i', "\n", $html);
        $html = preg_replace('/<(br|hr)\s*\/?>/i', "\n", $html);

        // Convert lists to readable format
        $html = preg_replace('/<li[^>]*>/i', "\n• ", $html);

        // Convert HTML to plain text
        $text = strip_tags($html);

        // Decode HTML entities multiple times for double-encoded entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Clean up excessive line breaks (more than 2 consecutive)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Remove spaces at the beginning/end of lines
        $text = preg_replace('/[ \t]+$/m', '', $text);
        $text = preg_replace('/^[ \t]+/m', '', $text);

        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Clean text field (title, company name, etc.)
     */
    public static function cleanText(string $text): string
    {
        // Decode HTML entities multiple times for double-encoded entities
        // (e.g., &amp;#8211; becomes &#8211; then becomes –)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Remove any remaining HTML tags
        $text = strip_tags($text);

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Clean tags array
     */
    public static function cleanTags(array $tags): array
    {
        return array_map(function($tag) {
            return self::cleanText($tag);
        }, $tags);
    }

    /**
     * Clean all job data fields
     */
    public static function cleanJobData(array $jobData): array
    {
        // Clean title
        if (isset($jobData['title'])) {
            $jobData['title'] = self::cleanText($jobData['title']);
        }

        // Clean company name
        if (isset($jobData['company'])) {
            $jobData['company'] = self::cleanText($jobData['company']);
        }

        // Clean description
        if (isset($jobData['description'])) {
            $jobData['description'] = self::cleanDescription($jobData['description']);
        }

        // Clean location
        if (isset($jobData['location'])) {
            $jobData['location'] = self::cleanText($jobData['location']);
        }

        // Clean salary range
        if (isset($jobData['salary_range'])) {
            $jobData['salary_range'] = self::cleanText($jobData['salary_range']);
        }

        // Clean tags
        if (isset($jobData['tags']) && is_array($jobData['tags'])) {
            $jobData['tags'] = self::cleanTags($jobData['tags']);
        }

        return $jobData;
    }
}
