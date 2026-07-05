<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- @include('head') --}}
    
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FACMS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://bunny.net">
         <!-- Preconnect to speed up the DNS handshake -->
        <link href="https://bunny.net" rel="stylesheet" />
        {{-- <link href="https://bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" /> --}}
        <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" />

        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

<div class="min-h-screen flex">

    {{-- Sidebar --}}
    {{-- <x-layout.sidebar /> --}}
    @role('President|Treasurer')
    <x-sidebar/>
    @endrole
    @role("Member")
    <x-sidebar-member/>
    @endrole
    {{-- Main Content --}}
    <div class="flex-1 flex flex-col lg:ml-64">

        {{-- Top Navigation --}}
        {{-- <x-layout.topbar /> --}}
        <x-topbar/>

        {{-- Main Content --}}
        <main class="flex-1 p-6">

            {{ $slot }}

        </main>

    </div>
    @include('sweetalert::alert')
</div>

<x-alert />

@stack('modals')
@stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
</body>

</html>