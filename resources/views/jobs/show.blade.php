@extends('layouts.app')

@section('title', $job->title . ' at ' . $job->company . ' - PromptJobs.io')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <div class="mb-4 md:mb-6 overflow-x-auto">
        <nav class="flex items-center text-xs md:text-sm text-gray-600 whitespace-nowrap pb-2 md:pb-0">
            <a href="{{ route('companies.index') }}" class="hover:text-blue-600 flex-shrink-0">🏢 Companies</a>
            <span class="mx-1 md:mx-2 flex-shrink-0">/</span>
            <a href="{{ route('companies.jobs', $job->company_slug) }}" class="hover:text-blue-600 truncate max-w-[150px] md:max-w-none">{{ $job->company }}</a>
            <span class="mx-1 md:mx-2 flex-shrink-0">/</span>
            <span class="text-gray-900 font-medium truncate max-w-[150px] md:max-w-none">{{ $job->title }}</span>
        </nav>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
        <!-- Main Content -->
        <div class="md:col-span-2">

    <!-- Job Header -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6 lg:p-8 mb-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 md:gap-6 mb-6">
            <div class="flex items-start space-x-3 md:space-x-4 flex-1">
                @if($job->company_logo)
                <img src="{{ $job->company_logo }}" alt="{{ $job->company }}" class="w-14 h-14 md:w-16 md:h-16 lg:w-20 lg:h-20 rounded flex-shrink-0" onerror="this.style.display='none'">
                @endif
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900 mb-2 line-clamp-3 md:line-clamp-2">{{ $job->title }}</h1>
                    <p class="text-base md:text-lg lg:text-xl text-gray-600 mb-3 md:mb-4 truncate">{{ $job->company }}</p>

                    <div class="flex flex-wrap gap-3 mb-4">
                        @if($job->location)
                        <span class="flex items-center text-gray-600">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $job->location }}
                        </span>
                        @endif

                        @if($job->remote)
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">🏠 Remote</span>
                        @endif

                        @if($job->featured)
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">⭐ Featured</span>
                        @endif

                        @if($job->is_potentially_old)
                        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium cursor-help" title="Posted {{ $job->published_at->diffForHumans() }} - This position may already be filled">
                            ⚠️ May be filled
                        </span>
                        @endif

                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium capitalize">{{ str_replace('-', ' ', $job->job_type) }}</span>
                    </div>

                    @if($job->salary_range)
                    <p class="text-lg font-semibold text-gray-900">💰 {{ $job->salary_range }}</p>
                    @endif
                </div>
            </div>

            <!-- Apply Button -->
            <div class="flex items-start md:flex-shrink-0">
                <a
                    href="{{ $job->apply_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full md:w-auto px-6 md:px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-base md:text-lg whitespace-nowrap transition-colors text-center min-h-[48px] flex items-center justify-center"
                >
                    ✉️ Apply Now
                </a>
            </div>
        </div>

        <!-- Tags -->
        @if((count($job->programming_languages) > 0) || (count($job->generic_tags) > 0))
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">🎯 Required Skills</h3>

            @if(count($job->generic_tags) > 0)
            <div class="mb-4">
                <p class="text-xs text-gray-500 mb-2">📌 Categories</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($job->generic_tags as $tag)
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm font-medium">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if(count($job->programming_languages) > 0)
            <div>
                <p class="text-xs text-gray-500 mb-2">💻 Technologies</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($job->programming_languages as $lang)
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-mono">{{ $lang }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Job Description -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6 lg:p-8">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">📄 Job Description</h2>
        <div class="prose prose-blue max-w-none">
            <p class="text-sm md:text-base text-gray-700 leading-relaxed whitespace-pre-line">{{ $job->description }}</p>
        </div>

        <!-- Apply Button at Bottom -->
        <div class="mt-6 md:mt-8 pt-6 border-t border-gray-200">
            <a
                href="{{ $job->apply_url }}"
                target="_blank"
                rel="noopener noreferrer"
                class="w-full md:w-auto inline-block px-6 md:px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-base md:text-lg text-center min-h-[48px]"
            >
                ✉️ Apply for this position
            </a>
            <p class="text-xs md:text-sm text-gray-500 mt-3">
                🕒 Posted {{ $job->published_at->diffForHumans() }}
            </p>
        </div>
    </div>

        </div>

        <!-- Sidebar: Similar Jobs -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6 md:sticky md:top-8">
                <h3 class="text-base md:text-lg font-bold text-gray-900 mb-4">
                    🔍 Similar @if($similarJobsCategory)<span class="truncate inline-block max-w-[150px] align-bottom">{{ $similarJobsCategory }}</span>@endif Jobs
                </h3>

                @if($similarJobs->count() > 0)
                    <div class="space-y-4">
                        @foreach($similarJobs as $similarJob)
                        <a href="{{ route('jobs.show', [$similarJob->company_slug, $similarJob->slug]) }}" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-400 hover:shadow-md transition">
                            <div class="flex items-start space-x-3 mb-2">
                                @if($similarJob->company_logo)
                                <img src="{{ $similarJob->company_logo }}" alt="{{ $similarJob->company }}" class="w-10 h-10 rounded flex-shrink-0" onerror="this.style.display='none'">
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-gray-900 text-sm hover:text-blue-600 line-clamp-2">{{ $similarJob->title }}</h4>
                                    <p class="text-sm text-gray-600 truncate">{{ $similarJob->company }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 text-xs">
                                @if($similarJob->location)
                                <span class="text-gray-500">📍 {{ Str::limit($similarJob->location, 20) }}</span>
                                @endif
                                @if($similarJob->remote)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded">🏠 Remote</span>
                                @endif
                            </div>

                            @if($similarJob->generic_tags && count($similarJob->generic_tags) > 0)
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach(array_slice($similarJob->generic_tags, 0, 2) as $tag)
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">{{ $tag }}</span>
                                @endforeach
                            </div>
                            @endif

                            <p class="text-xs text-gray-400 mt-2">{{ $similarJob->published_at->diffForHumans() }}</p>
                        </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No similar jobs found.</p>
                @endif

                <a href="{{ route('jobs.index') }}" class="block mt-6 text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                    View all jobs →
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
