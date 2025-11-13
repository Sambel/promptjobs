<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Location;
use App\Services\JobDomainService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::published()->latest('published_at');

        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by company
        if ($request->filled('company')) {
            $query->where('company', $request->company);
        }

        // Filter by zone (region)
        if ($request->filled('zone')) {
            $zone = $request->zone;
            $query->whereHas('locations', function($q) use ($zone) {
                $q->where('name', $zone)
                  ->orWhere('region_parent', $zone);
            });
        }

        // Filter by country
        if ($request->filled('country')) {
            $country = $request->country;
            $query->whereHas('locations', function($q) use ($country) {
                $q->where('name', $country)->where('type', 'country');
            });
        }

        // Filter by remote status
        if ($request->filled('remote_type')) {
            if ($request->remote_type === 'remote') {
                $query->where('remote', true);
            } elseif ($request->remote_type === 'on-site') {
                $query->where('remote', false);
            }
            // 'all' doesn't filter anything
        }

        // Filter by job type
        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        // Filter by specialization (categories)
        if ($request->filled('specialization')) {
            $query->whereJsonContains('categories', $request->specialization);
        }

        // Filter by tags
        if ($request->filled('tag')) {
            $tag = $request->tag;
            $query->whereJsonContains('tags', $tag);
        }

        $featuredJobs = Job::published()->featured()->latest('published_at')->limit(3)->get();
        $jobs = $query->paginate(50)->withQueryString();

        // Get unique values for filters
        $companies = Job::published()
            ->select('company')
            ->distinct()
            ->orderBy('company')
            ->pluck('company');

        // Get zones (regions + worldwide + timezone)
        $zones = Location::whereIn('type', ['region', 'worldwide', 'timezone'])
            ->orderBy('name')
            ->pluck('name');

        // Get countries
        $countries = Location::countries()
            ->orderBy('name')
            ->pluck('name');

        $jobTypes = Job::published()
            ->select('job_type')
            ->distinct()
            ->pluck('job_type');

        // Get available specializations (from PromptEngineeringFilterService)
        $specializations = [
            'prompt_engineering' => 'Prompt Engineering',
            'llm_engineering' => 'LLM Engineering',
            'genai' => 'GenAI',
            'ml_engineer' => 'ML Engineer',
        ];

        return view('jobs.index', compact('jobs', 'featuredJobs', 'companies', 'zones', 'countries', 'jobTypes', 'specializations'));
    }

    public function show(string $company, string $slug)
    {
        // Find job by slug AND company (slugified)
        // We need to get all jobs with this slug and filter by company
        $job = Job::published()
            ->where('slug', $slug)
            ->get()
            ->first(function ($job) use ($company) {
                return \Illuminate\Support\Str::slug($job->company) === $company;
            });

        // If no job found, redirect to companies index (301 for SEO)
        if (!$job) {
            return redirect()->route('companies.index', [], 301);
        }

        // Get similar jobs based on tags (categories)
        $similarJobs = collect();
        $similarJobsCategory = null;

        if ($job->tags && count($job->tags) > 0) {
            // Use the first tag as the category for personalization
            $firstTag = $job->tags[0];
            $similarJobsCategory = $firstTag;

            // Find jobs that share at least one tag with the current job
            $similarJobs = Job::published()
                ->where('id', '!=', $job->id)
                ->where(function($query) use ($job) {
                    foreach ($job->tags as $tag) {
                        $query->orWhereJsonContains('tags', $tag);
                    }
                })
                ->latest('published_at')
                ->limit(5)
                ->get();
        }

        // If no similar jobs found by tags, get recent jobs from the same company
        if ($similarJobs->isEmpty()) {
            $similarJobs = Job::published()
                ->where('id', '!=', $job->id)
                ->where('company', $job->company)
                ->latest('published_at')
                ->limit(5)
                ->get();
        }

        // If still empty, get just recent jobs
        if ($similarJobs->isEmpty()) {
            $similarJobs = Job::published()
                ->where('id', '!=', $job->id)
                ->latest('published_at')
                ->limit(5)
                ->get();
        }

        return view('jobs.show', compact('job', 'similarJobs', 'similarJobsCategory'));
    }

    public function companies()
    {
        $companies = Job::published()
            ->select('company')
            ->selectRaw('MAX(company_logo) as company_logo')
            ->selectRaw('COUNT(*) as jobs_count')
            ->groupBy('company')
            ->orderBy('jobs_count', 'desc')
            ->get();

        return view('companies.index', compact('companies'));
    }

    public function companyJobs(string $companySlug)
    {
        // Find jobs where the slugified company name matches the provided slug
        $jobs = Job::published()
            ->get()
            ->filter(function($job) use ($companySlug) {
                return \Illuminate\Support\Str::slug($job->company) === $companySlug;
            });

        // If no jobs found for this company, redirect to companies index (301 for SEO)
        if ($jobs->isEmpty()) {
            return redirect()->route('companies.index', [], 301);
        }

        // Get the actual company name from the first job
        $company = $jobs->first()->company;

        // Paginate manually
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $items = $jobs->forPage($currentPage, $perPage);

        $jobs = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $jobs->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('companies.jobs', compact('jobs', 'company'));
    }

    public function redirectToApply(Job $job)
    {
        // Redirect to the external apply URL
        return redirect()->away($job->apply_url);
    }
}
