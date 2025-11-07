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
                <div>
                    <button onclick="showPublishJobModal()" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 font-semibold transition-all shadow-md hover:shadow-lg">
                        ✨ Publish Job - $99
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Publish Job Modal -->
    <div id="publishJobModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="hidePublishJobModal()">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-8 transform transition-all" onclick="event.stopPropagation()">
            <div class="text-center">
                <div class="mb-6">
                    <span class="text-6xl">🚀</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Coming Soon!</h2>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    We're working on this feature. Soon you'll be able to publish your AI job listing for <strong class="text-purple-600">$99</strong> and get:
                </p>
                <div class="bg-blue-50 rounded-lg p-4 mb-6 text-left">
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start">
                            <span class="text-green-600 mr-2">✓</span>
                            <span><strong>Featured placement</strong> on homepage</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-600 mr-2">✓</span>
                            <span><strong>10 days</strong> of premium visibility</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-600 mr-2">✓</span>
                            <span>Reach top <strong>AI talent</strong></span>
                        </li>
                    </ul>
                </div>
                <button onclick="hidePublishJobModal()" class="w-full px-6 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 font-medium transition-colors">
                    Got it!
                </button>
            </div>
        </div>
    </div>

    <script>
        function showPublishJobModal() {
            document.getElementById('publishJobModal').classList.remove('hidden');
        }

        function hidePublishJobModal() {
            document.getElementById('publishJobModal').classList.add('hidden');
        }
    </script>

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
