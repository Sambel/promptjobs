@extends('layouts.app')

@section('title', 'Official AI Job Board - PromptJobs.io')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    <!-- Search Bar -->
    <div class="mb-6">
        <form method="GET" action="{{ route('jobs.index') }}" id="filterForm">
            <div class="flex flex-col md:flex-row gap-3 mb-4">
                <input
                    type="text"
                    name="search"
                    placeholder="🔍 Search jobs, companies, or skills..."
                    value="{{ request('search') }}"
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                <button
                    type="submit"
                    class="px-6 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 font-medium border-2 border-gray-900 hover:border-gray-800"
                >
                    🚀 Search
                </button>
            </div>

            <!-- Advanced Filters -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-900">🎯 Filters</h3>
                    @if(request()->hasAny(['company', 'zone', 'country', 'remote_type', 'job_type']))
                    <a href="{{ route('jobs.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        ✨ Clear all filters
                    </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Company Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🏢 Company</label>
                        <select
                            name="company"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                            <option value="{{ $company }}" {{ request('company') === $company ? 'selected' : '' }}>
                                {{ $company }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Zone Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🌍 Zone</label>
                        <select
                            name="zone"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Zones</option>
                            @foreach($zones as $zone)
                            <option value="{{ $zone }}" {{ request('zone') === $zone ? 'selected' : '' }}>
                                {{ $zone }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Country Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📍 Country</label>
                        <select
                            name="country"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Countries</option>
                            @foreach($countries as $country)
                            <option value="{{ $country }}" {{ request('country') === $country ? 'selected' : '' }}>
                                {{ \App\Helpers\CountryFlagHelper::getFlag($country) }} {{ $country }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Remote Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🏠 Work Mode</label>
                        <select
                            name="remote_type"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All</option>
                            <option value="remote" {{ request('remote_type') === 'remote' ? 'selected' : '' }}>🏠 Remote</option>
                            <option value="on-site" {{ request('remote_type') === 'on-site' ? 'selected' : '' }}>🏢 On-site</option>
                        </select>
                    </div>

                    <!-- Job Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">⏰ Job Type</label>
                        <select
                            name="job_type"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Types</option>
                            @foreach($jobTypes as $type)
                            <option value="{{ $type }}" {{ request('job_type') === $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('-', ' ', $type)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Filters Display -->
    @if(request()->hasAny(['search', 'company', 'zone', 'country', 'remote_type', 'job_type']))
    <div class="mb-6">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm font-medium text-gray-700">Active filters:</span>

            @if(request('search'))
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                Search: "{{ request('search') }}"
                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-blue-900">×</a>
            </span>
            @endif

            @if(request('company'))
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                Company: {{ request('company') }}
                <a href="{{ request()->fullUrlWithQuery(['company' => null]) }}" class="hover:text-blue-900">×</a>
            </span>
            @endif

            @if(request('zone'))
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                Zone: {{ request('zone') }}
                <a href="{{ request()->fullUrlWithQuery(['zone' => null]) }}" class="hover:text-blue-900">×</a>
            </span>
            @endif

            @if(request('country'))
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                Country: {{ request('country') }}
                <a href="{{ request()->fullUrlWithQuery(['country' => null]) }}" class="hover:text-blue-900">×</a>
            </span>
            @endif

            @if(request('remote_type'))
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                {{ ucfirst(request('remote_type')) }}
                <a href="{{ request()->fullUrlWithQuery(['remote_type' => null]) }}" class="hover:text-blue-900">×</a>
            </span>
            @endif

            @if(request('job_type'))
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                {{ ucfirst(str_replace('-', ' ', request('job_type'))) }}
                <a href="{{ request()->fullUrlWithQuery(['job_type' => null]) }}" class="hover:text-blue-900">×</a>
            </span>
            @endif
        </div>
    </div>
    @endif

    <!-- Featured Jobs -->
    @if($featuredJobs->count() > 0 && !request('search') && !request('tag'))
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">⭐ Featured Jobs</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($featuredJobs as $job)
            <a href="{{ route('jobs.show', [$job->company_slug, $job->slug]) }}" class="block p-6 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg hover:shadow-lg transition">
                <div class="flex items-start space-x-3 mb-3">
                    @if($job->company_logo)
                    <img src="{{ $job->company_logo }}" alt="{{ $job->company }}" class="w-12 h-12 rounded" onerror="this.style.display='none'">
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 truncate">{{ $job->title }}</h3>
                        <p class="text-sm text-gray-600">{{ $job->company }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 mb-2">
                    @if($job->location)
                    <span>{{ $job->location }}</span>
                    @endif
                    @if($job->remote)
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">🏠 Remote</span>
                    @endif
                    @if($job->is_potentially_old)
                    <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs font-medium cursor-help" title="Posted {{ $job->published_at->diffForHumans() }} - This position may already be filled">
                        ⚠️ May be filled
                    </span>
                    @endif
                </div>
                @if($job->salary_range)
                <p class="text-sm font-medium text-gray-900">{{ $job->salary_range }}</p>
                @endif
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- All Jobs -->
    <div class="mb-4">
        <div class="flex items-center justify-between mb-1">
            <h1 class="text-2xl font-bold text-gray-900">
                @if(request('search'))
                    🔎 Search results for "{{ request('search') }}"
                @else
                    🤖 All AI Jobs
                @endif
            </h1>
            <span class="text-gray-600">💼 {{ $jobs->total() }} jobs</span>
        </div>
        @if(!request('search'))
        <p class="text-sm text-gray-500">
            Last updated {{ \App\Models\Job::published()->latest('updated_at')->first()?->updated_at?->diffForHumans() ?? 'recently' }}
        </p>
        @endif
    </div>

    @if($jobs->count() > 0)
        <div class="space-y-3">
            @foreach($jobs as $job)
            <div class="p-5 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-6">
                    <!-- Left side: Job info (clickable) -->
                    <a href="{{ route('jobs.show', [$job->company_slug, $job->slug]) }}" class="flex items-start space-x-3 flex-1 min-w-0">
                        @if($job->company_logo)
                        <img src="{{ $job->company_logo }}" alt="{{ $job->company }}" class="w-12 h-12 rounded flex-shrink-0" onerror="this.style.display='none'">
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-gray-900 text-lg hover:text-blue-600">{{ $job->title }}</h3>
                                @if($job->featured)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-medium flex-shrink-0">⭐ Featured</span>
                                @endif
                                @if($job->is_potentially_old)
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs font-medium flex-shrink-0 cursor-help" title="Posted {{ $job->published_at->diffForHumans() }} - This position may already be filled">
                                    ⚠️ May be filled
                                </span>
                                @endif
                            </div>
                            <p class="text-gray-600 mb-2">{{ $job->company }}</p>

                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 mb-3">
                                @if($job->location)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $job->location }}
                                </span>
                                @endif
                                @if($job->remote)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">🏠 Remote</span>
                                @endif
                                @if($job->salary_range)
                                <span class="font-medium text-gray-900">💰 {{ $job->salary_range }}</span>
                                @endif
                                <span class="text-gray-400">{{ $job->published_at->diffForHumans() }}</span>
                            </div>

                            @if((count($job->programming_languages) > 0) || (count($job->generic_tags) > 0))
                            <div class="flex flex-wrap gap-2">
                                @foreach($job->generic_tags as $tag)
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">{{ $tag }}</span>
                                @endforeach
                                @foreach($job->programming_languages as $lang)
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-mono">{{ $lang }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </a>

                    <!-- Right side: Apply button -->
                    <div class="flex items-center flex-shrink-0">
                        <a
                            href="{{ $job->apply_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            onclick="event.stopPropagation()"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm whitespace-nowrap transition-colors"
                        >
                            ✉️ Apply Now
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $jobs->links() }}
        </div>
    @else
        <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
            <p class="text-gray-600">No jobs found. Try adjusting your search criteria.</p>
        </div>
    @endif
</div>
@endsection
