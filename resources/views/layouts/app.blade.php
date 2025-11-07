<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="zLESLfpqaZAQWor2Y62p6DFt6zma2K4mJ5kWjgvMGus" />
    <title>@yield('title', 'PromptJobs.io - Find your next AI job')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('jobs.index') }}" class="flex items-center space-x-2" title="Ai jobs">
                        <span class="text-2xl font-bold text-gray-900">🚀 PromptJobs.io</span>
                    </a>
                    <nav class="hidden md:flex items-center space-x-6">
                        <a href="{{ route('companies.index') }}" class="text-gray-700 hover:text-blue-600 font-medium {{ request()->routeIs('companies.*') ? 'text-blue-600' : '' }}">
                            🏢 Companies
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <p class="text-center text-gray-600 text-sm">
                &copy; {{ date('Y') }} PromptJobs.io - 🤖 Find your next AI job
            </p>
        </div>
    </footer>
</body>
</html>
