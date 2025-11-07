@extends('layouts.app')

@section('title', $company . ' AI Jobs - PromptJobs.io')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    <!-- Breadcrumb -->
    <div class="mb-4 md:mb-6 overflow-x-auto">
        <nav class="flex items-center space-x-2 text-xs md:text-sm text-gray-600 whitespace-nowrap pb-2 md:pb-0">
            <a href="{{ route('companies.index') }}" class="hover:text-blue-600 flex-shrink-0">Companies</a>
            <span class="flex-shrink-0">/</span>
            <span class="text-gray-900 font-medium truncate max-w-[200px] md:max-w-none">{{ $company }}</span>
        </nav>
    </div>

    <!-- Company Header -->
    <div class="mb-6 md:mb-8 bg-white border border-gray-200 rounded-lg p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3 md:space-x-4 flex-1 min-w-0">
                @php
                    $companyLogo = $jobs->first()->company_logo ?? null;
                @endphp
                @if($companyLogo)
                <img src="{{ $companyLogo }}" alt="{{ $company }}" class="w-14 h-14 md:w-20 md:h-20 rounded flex-shrink-0" onerror="this.style.display='none'">
                @endif
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900 mb-1 truncate">{{ $company }}</h1>
                    <p class="text-sm md:text-base text-gray-600">💼 {{ $jobs->total() }} open positions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- All Jobs -->
    <div class="mb-4">
        <h2 class="text-lg md:text-xl font-bold text-gray-900">Open Positions</h2>
    </div>

    @if($jobs->count() > 0)
        <div class="space-y-3">
            @foreach($jobs as $job)
            <div class="p-4 md:p-5 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:shadow-md transition">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <!-- Job info (clickable) -->
                    <a href="{{ route('jobs.show', [$job->company_slug, $job->slug]) }}" class="flex items-start space-x-3 flex-1 min-w-0">
                        @if($job->company_logo)
                        <img src="{{ $job->company_logo }}" alt="{{ $job->company }}" class="w-10 h-10 md:w-12 md:h-12 rounded flex-shrink-0" onerror="this.style.display='none'">
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="font-bold text-gray-900 text-base md:text-lg hover:text-blue-600 line-clamp-2 md:line-clamp-1">{{ $job->title }}</h3>
                                @if($job->featured)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-medium flex-shrink-0">⭐ Featured</span>
                                @endif
                                @if($job->is_potentially_old)
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs font-medium flex-shrink-0 cursor-help" title="Posted {{ $job->published_at->diffForHumans() }} - This position may already be filled">
                                    ⚠️ May be filled
                                </span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2 md:gap-3 text-xs md:text-sm text-gray-600 mb-3">
                                @if($job->location)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="truncate max-w-[150px] md:max-w-none">{{ $job->location }}</span>
                                </span>
                                @endif
                                @if($job->remote)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium whitespace-nowrap">🏠 Remote</span>
                                @endif
                                @if($job->salary_range)
                                <span class="font-medium text-gray-900 text-xs md:text-sm truncate max-w-[200px]">💰 {{ $job->salary_range }}</span>
                                @endif
                                <span class="text-gray-400 text-xs md:text-sm whitespace-nowrap">{{ $job->published_at->diffForHumans() }}</span>
                            </div>

                            @if((count($job->programming_languages) > 0) || (count($job->generic_tags) > 0))
                            <div class="flex flex-wrap gap-1.5 md:gap-2">
                                @foreach(array_slice($job->generic_tags, 0, 3) as $tag)
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">{{ $tag }}</span>
                                @endforeach
                                @foreach(array_slice($job->programming_languages, 0, 3) as $lang)
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-mono">{{ $lang }}</span>
                                @endforeach
                                @if(count($job->generic_tags) + count($job->programming_languages) > 6)
                                <span class="px-2 py-1 text-gray-500 text-xs">+{{ count($job->generic_tags) + count($job->programming_languages) - 6 }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </a>

                    <!-- Apply button -->
                    <div class="flex items-center md:flex-shrink-0">
                        <span
                            data-href="{{ route('jobs.apply', $job) }}"
                            onclick="window.open(this.getAttribute('data-href'), '_blank'); event.stopPropagation();"
                            class="w-full md:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm text-center whitespace-nowrap transition-colors min-h-[44px] flex items-center justify-center cursor-pointer"
                        >
                            ✉️ Apply Now
                        </span>
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
            <p class="text-gray-600">No jobs found for {{ $company }}.</p>
        </div>
    @endif

</div>
@endsection
