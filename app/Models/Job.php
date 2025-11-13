<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Job extends Model
{
    protected $table = 'job_listings';

    protected $fillable = [
        'external_id',
        'source',
        'source_url',
        'title',
        'company',
        'slug',
        'company_logo',
        'description',
        'location',
        'remote',
        'job_type',
        'domain',
        'salary_range',
        'apply_url',
        'tags',
        'categories',
        'featured',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'categories' => 'array',
        'remote' => 'boolean',
        'featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->slug)) {
                $job->slug = static::generateUniqueSlug($job->title, $job->company);
            }
        });

        static::updating(function ($job) {
            if ($job->isDirty('title') && empty($job->slug)) {
                $job->slug = static::generateUniqueSlug($job->title, $job->company);
            }
        });
    }

    /**
     * Generate a unique slug for the job
     */
    public static function generateUniqueSlug(string $title, string $company): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 2;

        // Check if slug already exists for this company
        while (static::where('company', $company)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the slugified company name for URLs
     */
    public function getCompanySlugAttribute(): string
    {
        return Str::slug($this->company);
    }

    /**
     * Check if the job is potentially old/filled (published more than 30 days ago)
     */
    public function getIsPotentiallyOldAttribute(): bool
    {
        return $this->published_at && $this->published_at->diffInDays(now()) > 30;
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Get programming languages from tags
     */
    public function getProgrammingLanguagesAttribute(): array
    {
        if (!$this->tags) {
            return [];
        }

        $languages = [
            'Python', 'JavaScript', 'TypeScript', 'Java', 'C++', 'C#', 'PHP', 'Ruby',
            'Go', 'Rust', 'Swift', 'Kotlin', 'Scala', 'R', 'SQL', 'HTML', 'CSS',
            'React', 'Vue', 'Angular', 'Node.js', 'Django', 'Flask', 'Laravel',
            'Spring', 'Express', 'Next.js', 'Svelte', 'jQuery'
        ];

        return array_values(array_filter($this->tags, function($tag) use ($languages) {
            return in_array($tag, $languages, true);
        }));
    }

    /**
     * Get generic/category tags (excluding programming languages)
     */
    public function getGenericTagsAttribute(): array
    {
        if (!$this->tags) {
            return [];
        }

        $languages = [
            'Python', 'JavaScript', 'TypeScript', 'Java', 'C++', 'C#', 'PHP', 'Ruby',
            'Go', 'Rust', 'Swift', 'Kotlin', 'Scala', 'R', 'SQL', 'HTML', 'CSS',
            'React', 'Vue', 'Angular', 'Node.js', 'Django', 'Flask', 'Laravel',
            'Spring', 'Express', 'Next.js', 'Svelte', 'jQuery'
        ];

        return array_values(array_filter($this->tags, function($tag) use ($languages) {
            return !in_array($tag, $languages, true);
        }));
    }

    /**
     * Locations relationship (many-to-many)
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'job_location');
    }

    /**
     * Sync locations from a location string
     */
    public function syncLocationsFromString(?string $locationString): void
    {
        $normalizedLocations = \App\Services\LocationNormalizerService::normalize($locationString);
        $locationIds = \App\Services\LocationNormalizerService::getOrCreateLocations($normalizedLocations);
        $this->locations()->sync($locationIds);
    }

    /**
     * Get badge labels with emojis for the categories
     */
    public function getBadgeLabelsAttribute(): array
    {
        if (!$this->categories) {
            return [];
        }

        return \App\Services\PromptEngineeringFilterService::getBadgeLabels($this->categories);
    }

    /**
     * Detect and set categories based on title and description
     * Should be called when creating/updating a job
     */
    public function detectAndSetCategories(): void
    {
        $categories = \App\Services\PromptEngineeringFilterService::detectCategories(
            $this->title ?? '',
            $this->description ?? ''
        );

        $this->categories = $categories;
    }
}
