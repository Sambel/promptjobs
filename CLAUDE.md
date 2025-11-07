# PromptJobs.io - AI Job Board Platform

## Project Overview

**PromptJobs.io** is a Laravel-based job aggregation platform specializing in AI, ML, and tech positions. The application automatically imports jobs from multiple external APIs, normalizes location data, cleans HTML entities, and presents them through a clean, searchable interface.

### Core Purpose
- Aggregate job listings from multiple remote job APIs
- Normalize inconsistent location data (countries, regions, timezones)
- Provide advanced filtering by location, company, tags, job type
- Display jobs with company branding and structured metadata

---

## Technical Stack

### Backend
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: SQLite (default), supports MySQL/PostgreSQL
- **Queue System**: Database-backed queue for async job imports
- **HTTP Client**: Laravel HTTP facade for API integrations

### Frontend
- **CSS Framework**: Tailwind CSS v4 (via CDN in production views)
- **Build Tool**: Vite with Laravel plugin
- **Templates**: Blade templating engine

### Development Tools
- **Code Quality**: Laravel Pint (PHP CS Fixer)
- **Testing**: PHPUnit
- **Dev Server**: Laravel Herd / Artisan serve
- **Logging**: Laravel Pail for real-time log viewing

---

## Architecture & Design Patterns

### 1. Multi-Source Job Aggregation

**Pattern**: Service-based API integration with interface standardization

**Location**: `/app/Services/JobApis/`

All job API integrations implement `JobApiInterface`:
```php
interface JobApiInterface {
    public function fetchJobs(): array;
    public function getSourceName(): string;
}
```

**Current Integrations**:
- `RemotiveService` - Active (Remotive.io API)
- `TheMuseService` - Active (TheMuse.com API)
- `HimalayasService` - Active (Himalayas.app API)
- `AiJobsNetService` - Inactive (API discontinued)

**Key Characteristics**:
- Each service transforms external API data into standardized job array
- Handles API errors gracefully with logging
- Extracts tags/keywords from job content
- Uses Clearbit for company logos fallback
- Double-decodes HTML entities to handle double-encoded data

### 2. Location Normalization System

**Pattern**: Intelligent data normalization with hierarchical relationships

**Location**: `/app/Services/LocationNormalizerService.php`

**Core Logic**:
- Parses location strings (e.g., "USA, Canada & Europe")
- Recognizes regions, countries, timezones, and vague terms
- Creates hierarchical relationships (Country → Region → Global)
- Handles synonyms (UK → United Kingdom, USA → United States)

**Location Types**:
- `country` - Specific countries
- `region` - EMEA, Americas, APAC, Europe, Asia, etc.
- `worldwide` - Global/Remote positions
- `timezone` - Timezone-based locations (CET, EST, PST)

**Example Flow**:
```
Input: "USA, UK and Europe"
↓
Output:
- USA (country, region: Americas)
- United Kingdom (country, region: Europe)
- Europe (region)
  └── All European countries as children
```

**Many-to-Many Relationship**:
- Jobs can have multiple locations
- Pivot table: `job_location` links jobs to normalized locations
- Enables filtering by specific country OR broader region

### 3. Text Cleaning Pipeline

**Pattern**: Centralized text sanitization service

**Location**: `/app/Services/TextCleanerService.php`

**Capabilities**:
- `cleanDescription()` - Converts HTML to formatted plain text with bullets
- `cleanText()` - Sanitizes titles, company names (double HTML entity decode)
- `cleanTags()` - Cleans tag arrays
- `cleanJobData()` - Applies cleaning to all job fields

**Why Double Decode?**:
Some APIs return double-encoded entities:
```
&amp;#8211; → &#8211; → – (em dash)
```

### 4. Slug-Based Routing

**Pattern**: SEO-friendly URLs with automatic slug generation

**URL Structure**:
```
/companies/{company-slug}/{job-slug}
```

**Implementation**:
- Slugs auto-generated on job creation via Eloquent boot events
- Unique per company (same title allowed across companies)
- Route model binding uses slug instead of ID
- Backfill command: `php artisan jobs:generate-slugs`

### 5. Console Command Structure

**Commands**:

| Command | Purpose | Schedule |
|---------|---------|----------|
| `jobs:import` | Import from all APIs | Every 3 hours |
| `jobs:import-jobicy` | Import from Jobicy scraper | Every 6 hours |
| `jobs:normalize-locations` | Backfill location normalization | Manual |
| `jobs:generate-slugs` | Generate missing slugs | Manual |
| `jobs:clean-tags` | Clean HTML entities in tags | Manual |

**Import Flow**:
1. Command dispatches `ImportJobsFromApis` job to queue
2. Job iterates through all API services
3. Each service fetches and transforms jobs
4. Jobs saved via `updateOrCreate()` (keyed by `external_id` + `source`)
5. Locations normalized and synced automatically
6. Detailed logging of stats (imported, skipped, errors)

---

## Database Schema

### Core Tables

**`job_listings`** - Main job records
```
id, external_id, source, source_url, title, company, slug,
company_logo, description, location, remote, job_type,
salary_range, apply_url, tags (JSON), featured, published_at
```

**`locations`** - Normalized location entities
```
id, name (unique), type (enum), region_parent, timezone_based
```

**`job_location`** - Many-to-many pivot
```
id, job_id, location_id (unique constraint)
```

### Key Indexes
- `job_listings`: external_id, source, slug, published_at
- `locations`: type, region_parent
- `job_location`: job_id, location_id

---

## Models & Relationships

### Job Model (`/app/Models/Job.php`)

**Key Features**:
- Table: `job_listings`
- Casts: `tags` (array), `remote`/`featured` (boolean), `published_at` (datetime)
- Route binding: Uses `slug` instead of `id`

**Accessors**:
- `company_slug` - Slugified company name for URLs
- `is_potentially_old` - True if published > 30 days ago
- `programming_languages` - Extracts tech tags from tags array
- `generic_tags` - Non-programming tags

**Scopes**:
- `published()` - Only published jobs
- `featured()` - Featured jobs

**Methods**:
- `generateUniqueSlug()` - Creates unique slug per company
- `syncLocationsFromString()` - Parses location string and syncs relationships

**Relationships**:
- `locations()` - BelongsToMany via `job_location`

### Location Model (`/app/Models/Location.php`)

**Scopes**:
- `countries()` - Filter by type='country'
- `regions()` - Filter by type='region'
- `inRegion($region)` - Filter by region_parent

**Relationships**:
- `jobs()` - BelongsToMany via `job_location`

---

## Controllers & Routes

### Routes (`/routes/web.php`)

```php
GET  /                              → jobs.index (JobController@index)
GET  /companies                     → companies.index (JobController@companies)
GET  /companies/{company}           → companies.jobs (JobController@companyJobs)
GET  /companies/{company}/{job}     → jobs.show (JobController@show)
```

### JobController (`/app/Http/Controllers/JobController.php`)

**`index()`** - Job listing with filters
- Filters: search, company, zone (region), country, remote_type, job_type, tag
- Returns: paginated jobs (50/page), featured jobs (3), filter options

**`show()`** - Individual job view
- Validates company slug matches job
- Finds similar jobs by shared tags
- Fallback: jobs from same company → recent jobs

**`companies()`** - Company directory
- Groups by company with job count
- Orders by job count (most active first)

**`companyJobs()`** - Jobs by company
- Filters by slugified company name
- Manual pagination (20/page)

---

## Queue Jobs

### ImportJobsFromApis (`/app/Jobs/ImportJobsFromApis.php`)

**Implements**: `ShouldQueue` (queued job)

**Execution Flow**:
1. Instantiates all API services
2. For each service:
   - Fetch jobs via `fetchJobs()`
   - Loop through jobs:
     - `updateOrCreate()` by external_id + source
     - Sync locations via `syncLocationsFromString()`
   - Log stats (imported, skipped, errors)
3. Final summary log with totals

**Error Handling**:
- Service-level try/catch (continues on failure)
- Job-level try/catch (logs and skips individual jobs)
- Comprehensive logging at each stage

---

## Views & Frontend

### Layout (`/resources/views/layouts/app.blade.php`)

**Structure**:
- Header: Logo, navigation (Companies)
- Main: `@yield('content')`
- Footer: Copyright

**Styling**: Tailwind CSS via CDN (no build step for production)

### Key Views

- `jobs/index.blade.php` - Job listing with filter sidebar
- `jobs/show.blade.php` - Job detail with description, apply button, similar jobs
- `companies/index.blade.php` - Company directory with logos
- `companies/jobs.blade.php` - Jobs by specific company

**Design Patterns**:
- Card-based layouts
- Responsive grid (Tailwind)
- Tag pills for skills/categories
- Company logo integration
- "Old job" warnings (30+ days)

---

## Configuration & Environment

### Key Environment Variables (`.env`)

```env
APP_NAME=PromptJobs.io
DB_CONNECTION=sqlite                    # Default database
QUEUE_CONNECTION=database               # Use DB for queue
CACHE_STORE=database
SESSION_DRIVER=database
LOG_CHANNEL=stack
```

### Composer Scripts

**Setup**:
```bash
composer run setup    # Install deps, migrate, build assets
```

**Development**:
```bash
composer run dev      # Runs: serve + queue + pail + vite (concurrent)
```

**Testing**:
```bash
composer run test     # Clear config + run PHPUnit
```

---

## Development Workflow

### Initial Setup

1. **Clone & Install**:
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

2. **Database**:
```bash
php artisan migrate
```

3. **Import Jobs**:
```bash
php artisan jobs:import --sync    # Synchronous import
# OR
php artisan jobs:import           # Queue-based (requires queue worker)
```

4. **Normalize Locations** (if backfilling):
```bash
php artisan jobs:normalize-locations
```

### Running Development Server

**Option 1: Composer script (recommended)**
```bash
composer run dev
```
Runs concurrently:
- Laravel dev server (`:8000`)
- Queue worker (database queue)
- Pail (real-time logs)
- Vite (asset compilation)

**Option 2: Manual**
```bash
php artisan serve                 # Terminal 1
php artisan queue:work            # Terminal 2
php artisan pail                  # Terminal 3
npm run dev                       # Terminal 4
```

### Scheduled Tasks

**Schedule Definition** (`/routes/console.php`):
```php
Schedule::command('jobs:import')->everyThreeHours();
Schedule::command('jobs:import-jobicy', ['--count' => 100])->everySixHours();
```

**Run Scheduler**:
```bash
php artisan schedule:work     # Development
```

**Production**: Add cron entry:
```cron
* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
```

---

## Code Conventions & Patterns

### Naming Conventions

**Models**: Singular, PascalCase (`Job`, `Location`)  
**Tables**: Plural, snake_case (`job_listings`, `locations`)  
**Controllers**: Singular + "Controller" (`JobController`)  
**Services**: Descriptive + "Service" (`LocationNormalizerService`)  
**Commands**: Verb + Noun (`ImportJobs`, `NormalizeJobLocations`)  
**Routes**: RESTful resource naming

### Data Handling Patterns

1. **API Integration**:
   - Interface-based (`JobApiInterface`)
   - Transform external data → standardized array
   - Log failures, never throw exceptions up
   - Return empty array on errors

2. **Data Cleaning**:
   - Always clean external data via `TextCleanerService`
   - Double-decode HTML entities (APIs often double-encode)
   - Strip HTML, normalize whitespace

3. **Location Processing**:
   - Always normalize via `LocationNormalizerService`
   - Use `syncLocationsFromString()` for automatic M2M sync
   - Handle vague terms ("Remote", "Worldwide") → "Worldwide" type

4. **Database Updates**:
   - Use `updateOrCreate()` for idempotent imports
   - Unique keys: `external_id` + `source` or `apply_url` (fallback)
   - Log all import stats (imported, skipped, errors)

### Service Layer Pattern

Services are stateless, static-method utility classes:
- `LocationNormalizerService` - Location parsing/normalization
- `TextCleanerService` - HTML/text sanitization
- `JobApis/*Service` - API integrations

No dependency injection needed for utility services (all static methods).

### Error Handling

**API Failures**:
- Log error with context
- Return empty array (never null)
- Continue processing other sources

**Job Import Failures**:
- Log warning with job title
- Skip individual job, continue batch
- Track stats (skipped count)

**Validation**:
- Use Laravel's validation in controllers (future enhancement)
- Model-level data cleaning (boot events)

---

## Extending the Application

### Adding a New Job API

1. **Create Service** (`/app/Services/JobApis/NewApiService.php`):
```php
<?php
namespace App\Services\JobApis;

use App\Services\TextCleanerService;
use Illuminate\Support\Facades\Http;

class NewApiService implements JobApiInterface
{
    private const API_URL = 'https://api.example.com/jobs';
    private const SOURCE_NAME = 'new-api';

    public function fetchJobs(): array
    {
        $response = Http::timeout(30)->get(self::API_URL);
        // ... error handling
        return array_map(fn($job) => $this->transformJob($job), $data);
    }

    public function getSourceName(): string
    {
        return self::SOURCE_NAME;
    }

    private function transformJob(array $job): array
    {
        return [
            'external_id' => $job['id'],
            'source' => self::SOURCE_NAME,
            'source_url' => $job['url'],
            'title' => $job['title'],
            'company' => $job['company'],
            'company_logo' => $job['logo'] ?? $this->getCompanyLogo($job['company']),
            'description' => TextCleanerService::cleanDescription($job['description']),
            'location' => $job['location'],
            'remote' => $job['is_remote'] ?? false,
            'job_type' => $this->determineJobType($job),
            'salary_range' => $job['salary'] ?? null,
            'apply_url' => $job['apply_link'],
            'tags' => $this->extractTags($job),
            'featured' => false,
            'published_at' => new \DateTime($job['published'] ?? 'now'),
        ];
    }

    private function getCompanyLogo(string $company): string
    {
        $domain = strtolower(str_replace(' ', '', $company)) . '.com';
        return "https://logo.clearbit.com/{$domain}";
    }

    // Implement determineJobType(), extractTags()...
}
```

2. **Register in ImportJobsFromApis** (`/app/Jobs/ImportJobsFromApis.php`):
```php
$services = [
    new AiJobsNetService(),
    new RemotiveService(),
    new TheMuseService(),
    new HimalayasService(),
    new NewApiService(), // Add here
];
```

3. **Test**:
```bash
php artisan jobs:import --sync
```

### Adding New Location Regions

Edit `/app/Services/LocationNormalizerService.php`:

```php
private static array $regionMappings = [
    'Your Region' => ['Country1', 'Country2', ...],
    // ...
];
```

Run normalization for existing jobs:
```bash
php artisan jobs:normalize-locations
```

### Adding Custom Filters

1. **Update Controller** (`JobController@index`):
```php
if ($request->filled('custom_filter')) {
    $query->where('field', $request->custom_filter);
}
```

2. **Update View** (`jobs/index.blade.php`):
```html
<select name="custom_filter">
    <option value="">All</option>
    <!-- Options -->
</select>
```

---

## Important Notes & Gotchas

### 1. Double HTML Entity Encoding
Some external APIs double-encode entities. Always use `TextCleanerService::cleanText()` which decodes twice:
```php
// Input: "&amp;#8211;"
// After 1st decode: "&#8211;"
// After 2nd decode: "–" (em dash)
```

### 2. Location String Parsing
Location strings vary wildly:
- "USA, Canada & Europe"
- "Remote (GMT+1)"
- "Worldwide"
- "San Francisco or Remote"

The `LocationNormalizerService` handles most cases but may need updates for new patterns.

### 3. Unique Job Identification
Jobs are uniquely identified by:
- Primary: `external_id` + `source`
- Fallback: `apply_url` (if no external_id)

This allows re-importing the same job without duplicates.

### 4. Company Logos
Uses Clearbit API (`https://logo.clearbit.com/{domain}`):
- Fallback when API doesn't provide logo
- Domain guessed from company name (e.g., "Anthropic" → "anthropic.com")
- May 404 for unknown companies (handle gracefully in frontend)

### 5. Queue Processing
The `jobs:import` command uses queues by default. Ensure queue worker is running:
```bash
php artisan queue:work
```

Or use `--sync` flag to run synchronously:
```bash
php artisan jobs:import --sync
```

### 6. Tag Extraction
Tags are extracted from:
- API-provided tags
- Job title/description keyword matching

Keyword list is hardcoded in each service. Consider centralizing if adding many services.

### 7. Job Age Indication
Jobs older than 30 days get `is_potentially_old` attribute. Consider:
- Auto-hiding after 60 days
- Archiving old jobs
- Re-checking if job still exists via source URL

---

## Testing Strategy

### Current State
- Basic Laravel test structure in `/tests`
- No feature tests yet (future enhancement)

### Recommended Test Coverage

**Unit Tests**:
- `LocationNormalizerService::normalize()`
- `TextCleanerService::cleanDescription()`
- `Job::generateUniqueSlug()`

**Feature Tests**:
- API service integrations (mocked HTTP)
- Job import flow
- Location normalization command
- Controller filtering logic

**Example Test** (future):
```php
public function test_location_normalizer_handles_regions()
{
    $result = LocationNormalizerService::normalize('USA and Europe');
    
    $this->assertCount(3, $result); // USA + Europe + all EU countries
    $this->assertEquals('United States', $result[0]['name']);
    $this->assertEquals('Europe', $result[1]['name']);
}
```

---

## Performance Considerations

### Database Optimization
- Indexes on frequently queried fields (`published_at`, `slug`, location foreign keys)
- JSON field (`tags`) queried via `whereJsonContains` (works on SQLite 3.38+)
- Pagination limits (50 jobs/page, 20 for company pages)

### Scaling Recommendations
1. **Cache Filter Options**:
   - Companies list, locations, job types (rarely change)
   - Cache for 1 hour, invalidate on import

2. **Full-Text Search**:
   - Consider Laravel Scout + Meilisearch/Algolia for better search
   - Current implementation uses `LIKE` queries (OK for small datasets)

3. **Queue Workers**:
   - Use Redis for queue in production (faster than database)
   - Run multiple queue workers for parallel processing

4. **Image Optimization**:
   - Cache Clearbit logo URLs (avoid repeated 404s)
   - Consider self-hosted logo storage

---

## Deployment Checklist

### Pre-Deployment
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate app key (`php artisan key:generate`)
- [ ] Configure database (MySQL/PostgreSQL recommended)
- [ ] Set `QUEUE_CONNECTION=redis` (if using Redis)
- [ ] Run migrations (`php artisan migrate --force`)
- [ ] Build assets (`npm run build`)

### Production Setup
- [ ] Set up queue worker (Supervisor recommended)
- [ ] Configure cron for scheduler (`* * * * * php artisan schedule:run`)
- [ ] Set up log rotation
- [ ] Configure CDN for static assets (optional)
- [ ] Set up monitoring (Laravel Horizon for queue visibility)

### Post-Deployment
- [ ] Run initial job import: `php artisan jobs:import --sync`
- [ ] Normalize locations: `php artisan jobs:normalize-locations`
- [ ] Generate slugs: `php artisan jobs:generate-slugs`
- [ ] Verify scheduler is running
- [ ] Check queue processing

---

## Maintenance Commands

### Regular Maintenance
```bash
# Import jobs (every 3 hours via scheduler)
php artisan jobs:import

# Clear old logs
php artisan log:clear

# Optimize framework
php artisan optimize

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### One-Time / Backfill
```bash
# Normalize all job locations
php artisan jobs:normalize-locations

# Generate missing slugs
php artisan jobs:generate-slugs

# Clean HTML entities in tags
php artisan jobs:clean-tags
```

### Database Maintenance
```bash
# Vacuum SQLite database (reclaim space)
sqlite3 database/database.sqlite "VACUUM;"

# Delete jobs older than 90 days
php artisan tinker
>>> Job::where('published_at', '<', now()->subDays(90))->delete();
```

---

## Troubleshooting

### Common Issues

**1. Jobs not importing**
- Check queue worker is running: `php artisan queue:work`
- Check API logs: `tail -f storage/logs/laravel.log`
- Test individual service in Tinker:
  ```php
  $service = new \App\Services\JobApis\RemotiveService();
  $jobs = $service->fetchJobs();
  count($jobs); // Should be > 0
  ```

**2. Location normalization fails**
- Check for special characters in location strings
- Add new patterns to `LocationNormalizerService::normalize()`
- Run with error logging: `php artisan jobs:normalize-locations`

**3. Slugs not generating**
- Ensure `boot()` method in Job model is called
- Run backfill: `php artisan jobs:generate-slugs`

**4. Tags display HTML entities**
- Run cleaning command: `php artisan jobs:clean-tags`
- Verify TextCleanerService is called in API services

**5. Company logos not loading**
- Clearbit API requires valid domain (may 404)
- Verify company name format (no special characters)
- Consider caching logo URLs to avoid repeated requests

---

## Future Enhancements

### Planned Features
- [ ] User authentication (save favorite jobs, apply tracking)
- [ ] Email alerts for new jobs matching criteria
- [ ] Advanced search with filters (salary range, experience level)
- [ ] Company profiles with description, reviews
- [ ] Admin dashboard for manual job curation
- [ ] API for external integrations
- [ ] RSS/JSON feeds for job listings
- [ ] SEO optimization (meta tags, sitemaps, structured data)

### Technical Improvements
- [ ] Redis caching for filter options
- [ ] Full-text search (Laravel Scout)
- [ ] Automated tests (unit + feature)
- [ ] CI/CD pipeline (GitHub Actions)
- [ ] Database query optimization (N+1 analysis)
- [ ] Rate limiting for job views (prevent scraping)
- [ ] Webhook support for instant job notifications

---

## Resources & References

### Laravel Documentation
- [Laravel 12.x Docs](https://laravel.com/docs/12.x)
- [Eloquent ORM](https://laravel.com/docs/12.x/eloquent)
- [Queues](https://laravel.com/docs/12.x/queues)
- [Task Scheduling](https://laravel.com/docs/12.x/scheduling)

### External APIs
- [Remotive API](https://remotive.com/api)
- [TheMuse API](https://www.themuse.com/developers/api)
- [Clearbit Logo API](https://clearbit.com/logo)

### Related Technologies
- [Tailwind CSS](https://tailwindcss.com)
- [Vite](https://vitejs.dev)
- [Laravel Herd](https://herd.laravel.com)

---

## Contact & Support

**Project**: PromptJobs.io  
**Repository**: [Add GitHub URL]  
**License**: MIT (assumed)  

For issues or questions, refer to:
1. Laravel documentation for framework questions
2. Application logs (`storage/logs/laravel.log`)
3. Database queries via Tinker (`php artisan tinker`)

---

**Last Updated**: 2025-11-07  
**Laravel Version**: 12.x  
**PHP Version**: 8.2+
