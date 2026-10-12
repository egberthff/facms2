<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- @include('head') --}}
    
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            [x-cloak] { display: none !important; }
        </style>

        <title>{{ config('app.name', 'FACMS') }}</title>

        <!-- Fonts -->
        {{-- <link rel="preconnect" href="https://bunny.net"> --}}
         <!-- Preconnect to speed up the DNS handshake -->
        {{-- <link href="https://bunny.net" rel="stylesheet" /> --}}
        {{-- <link href="https://bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" /> --}}
        {{-- <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" /> --}}

        {{-- <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script> --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

<div class="min-h-screen flex" x-data="{ sidebarOpen: false }">

    {{-- Sidebar --}}
    {{-- <x-layout.sidebar /> --}}
    {{-- @role('President|Federated President|Treasurer|Federated Treasurer')
    <x-sidebar/>
    @endrole
    @role("Member")
    <x-sidebar-member/>
    @endrole --}}
    <x-sidebar-hybrid/>
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-slate-900/60 lg:hidden"
        aria-hidden="true"></div>
    {{-- Main Content --}}
    <div class="flex-1 min-w-0 flex flex-col lg:ml-64">

        {{-- Top Navigation --}}
        {{-- <x-layout.topbar /> --}}
        <x-topbar/>

        {{-- Main Content --}}
        <main class="flex-1 w-full max-w-screen-2xl mx-auto p-3 sm:p-4 lg:p-6">

            @if (auth()->user()?->hasInactiveMembership())
                <div class="mb-3 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-md text-sm text-amber-800" role="status">
                    <span class="font-semibold">Membership pending activation.</span>
                    Your account is active, but your membership is not. Until a campus officer activates it, you can only use the Dashboard and My Profile.
                </div>
            @endif

            {{ $slot }}

        </main>

    </div>
</div>

<x-alert />

@stack('modals')
@stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
</body>

</html>