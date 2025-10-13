<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100">
        <div class="min-h-screen">
            <!-- Header -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ config('app.name', 'Quiz App') }}
                    </h1>
                    @if (Route::has('login'))
                        <nav class="flex space-x-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </header>

            <!-- Main Content -->
            <main>
                <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
                    <!-- Hero Section -->
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-gray-900 mb-4">
                            Welcome to Quiz App
                        </h2>
                        <p class="text-xl text-gray-600 mb-8">
                            Test your knowledge with our interactive quizzes
                        </p>
                        
                        @guest
                            <!-- Call to Action Buttons -->
                            <div class="flex justify-center gap-4 mb-8">
                                <a href="{{ route('register') }}" class="px-8 py-3 bg-blue-600 text-white text-lg font-semibold rounded-lg hover:bg-blue-700 transition">
                                    Get Started - Register Now
                                </a>
                                <a href="{{ route('login') }}" class="px-8 py-3 bg-gray-200 text-gray-800 text-lg font-semibold rounded-lg hover:bg-gray-300 transition">
                                    Login
                                </a>
                            </div>
                        @endguest
                    </div>

                    <!-- Features -->
                  
                    @guest
                        <!-- Registration Call-to-Action Section -->
                        <div class="bg-blue-600 text-white rounded-lg p-8 text-center">
                            <h3 class="text-2xl font-bold mb-4">Ready to Test Your Knowledge?</h3>
                            <p class="text-lg mb-6">Join thousands of learners improving their skills every day</p>
                            <a href="{{ route('register') }}" class="inline-block px-8 py-3 bg-white text-blue-600 text-lg font-semibold rounded-lg hover:bg-gray-100 transition">
                                Register for Free
                            </a>
                        </div>
                    @endguest
                </div>
            </main>

       
        </div>
    </body>
</html>
