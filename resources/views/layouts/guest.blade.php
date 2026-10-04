<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FacAssoc Portal') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://bunny.net">
        <link href="https://bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900 bg-slate-100">
        <div class="min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row md:min-h-[550px] border border-slate-200">
                
                <!-- Left Sidebar Brand Panel (Matches your app's sidebar look) -->
                <div class="w-full md:w-5/12 bg-linear-to-br from-indigo-900 via-violet-950 to-slate-900 p-6 sm:p-8 flex flex-col justify-between text-white relative overflow-hidden">
                    <!-- Subtle decorative background patterns -->
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-yellow-500 rounded-full opacity-10 blur-2xl"></div>
                    <div class="absolute -left-10 -top-10 w-40 h-40 bg-violet-500 rounded-full opacity-20 blur-2xl"></div>
                    
                    <!-- Header area inside pane -->
                    <div class="flex items-center space-x-3 z-10">
                        <span class="text-xs font-bold uppercase tracking-widest bg-white/10 px-3 py-1 rounded-full text-yellow-400 backdrop-blur-sm border border-white/5">
                            FACMS v2.0
                        </span>
                    </div>
            
                    
                    <div class="my-auto py-6 md:py-12 flex flex-col items-center text-center z-10">
                        <!-- BISU Logo Container -->
                        <div class="h-20 w-20 md:h-28 md:w-28 rounded-full bg-white p-2 shadow-lg border-2 border-violet-400/30 flex items-center justify-center">
                            <img src="{{ asset('storage/images/logo/bisu_logo.png') }}" alt="Bohol Island State University Logo" class="object-contain max-h-full">
                        </div>
                        
                        <h1 class="mt-5 text-lg md:text-xl font-bold tracking-tight text-gray-100 uppercase">
                            Bohol Island State University
                        </h1>
                        <p class="text-xs text-violet-300 tracking-wider font-semibold mt-1 max-w-xs">
                            Balilihan Campus · Magsija Balilihan Bohol
                        </p>
                        
                        <div class="w-12 h-1 bg-yellow-400 rounded mt-6 mb-4"></div>
                        
                        <h2 class="text-base md:text-lg font-medium text-gray-200 leading-snug px-4">
                            Faculty Association Claims Monitoring System
                        </h2>
                    </div>
            
                    <!-- Footer inside branding pane -->
                    <div class="text-center md:text-left z-10">
                        <p class="text-xs text-violet-300/70">
                            &copy; {{ date('Y') }} BISU Faculty Association. All rights reserved.
                        </p>
                    </div>
                </div>

                <!-- Right Form Slot Panel -->
                <div class="w-full md:w-7/12 p-6 sm:p-10 flex flex-col justify-center bg-white">
                    {{ $slot }}
                </div>

            </div>
        </div>
    </body>
</html>
