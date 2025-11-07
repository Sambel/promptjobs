@extends('layouts.app')

@section('title', 'AI Companies Currently Hiring - PromptJobs.io')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">🏢 AI Companies Currently Hiring</h1>
        <p class="text-gray-600">Discover companies actively hiring for AI & Machine Learning roles</p>
    </div>

    <!-- Companies Stats -->
    <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center gap-2 text-blue-800">
            <span class="text-2xl font-bold">{{ $companies->count() }}</span>
            <span class="text-sm">companies with active job openings</span>
        </div>
    </div>

    <!-- Companies Grid -->
    @if($companies->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($companies as $company)
        <a href="{{ route('companies.jobs', Str::slug($company->company)) }}" class="block p-6 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:shadow-lg transition">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                    @if($company->company_logo)
                    <img src="{{ $company->company_logo }}" alt="{{ $company->company }}" class="w-16 h-16 rounded flex-shrink-0" onerror="this.style.display='none'">
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 text-lg truncate hover:text-blue-600">{{ $company->company }}</h3>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm">
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-medium">
                        💼 {{ $company->jobs_count }} {{ $company->jobs_count === 1 ? 'job' : 'jobs' }}
                    </span>
                </div>
                <span class="text-blue-600 text-sm font-medium">View jobs →</span>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
        <p class="text-gray-600">No companies found with active job openings.</p>
    </div>
    @endif

</div>
@endsection
