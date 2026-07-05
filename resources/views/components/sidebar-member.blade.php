<aside
    id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform bg-slate-800 border-r border-slate-700">

    <div class="h-full px-3 py-4 overflow-y-auto">

        <!-- ========================================= -->
        <!-- LOGO -->
        <!-- ========================================= -->

        <a href="{{ route('dashboard') }}"
            class="flex items-center mb-8">

            <div class="w-13 h-13 rounded-lg bg-white flex items-center justify-center p-1">

                <img
                    src="{{ asset('storage/images/logo/bisu_logo.png') }}"
                    alt="Logo"
                    class="w-full h-full object-cover rounded-lg">

            </div>

            <div class="ml-5">

                <div class="text-white font-bold">

                    FacAssoc

                </div>

                <div class="text-xs text-slate-300 uppercase">

                    Member Portal

                </div>

            </div>

        </a>

        <!-- ========================================= -->
        <!-- OVERVIEW -->
        <!-- ========================================= -->

        <div class="mb-4">

            <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">

                Overview

            </div>

            <ul class="space-y-2">

                <li>

                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg transition
                        {{ request()->routeIs('dashboard')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">

                        <x-heroicon-o-home class="w-5 h-5"/>

                        <span class="ml-3">

                            Dashboard

                        </span>

                    </a>

                </li>

            </ul>

        </div>

        <hr class="border-slate-700 my-4">

        <!-- ========================================= -->
        <!-- CLAIMS -->
        <!-- ========================================= -->

        <div class="mb-4">

            <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">

                Claims

            </div>

            <ul class="space-y-2">

                <li>

                    {{-- <a href="{{ route('claims.create') }}" --}}
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg transition
                        {{ request()->routeIs('claims.create')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">

                        <x-heroicon-o-plus-circle class="w-5 h-5"/>

                        <span class="ml-3">

                            Submit Claim

                        </span>

                    </a>

                </li>

                <li>

                    {{-- <a href="{{ route('claims.my') }}" --}}
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg transition
                        {{ request()->routeIs('claims.my')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">

                        <x-heroicon-o-document-text class="w-5 h-5"/>

                        <span class="ml-3">

                            My Claims

                        </span>

                    </a>

                </li>

                <li>

                    {{-- <a href="{{ route('balance.index') }}" --}}
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg transition
                        {{ request()->routeIs('balance.*')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">

                        <x-heroicon-o-banknotes class="w-5 h-5"/>

                        <span class="ml-3">

                            Balance

                        </span>

                    </a>

                </li>

            </ul>

        </div>

        <hr class="border-slate-700 my-4">

        <!-- ========================================= -->
        <!-- REPORTS -->
        <!-- ========================================= -->

        <div class="mb-4">

            <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">

                Reports

            </div>

            <ul class="space-y-2">

                <li>

                    {{-- <a href="{{ route('reports.my') }}" --}}
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg transition
                        {{ request()->routeIs('reports.my')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">

                        <x-heroicon-o-chart-bar-square class="w-5 h-5"/>

                        <span class="ml-3">

                            My Reports

                        </span>

                    </a>

                </li>

            </ul>

        </div>

        <hr class="border-slate-700 my-4">

        <!-- ========================================= -->
        <!-- ACCOUNT -->
        <!-- ========================================= -->

        <div>

            <ul class="space-y-2">

                <li>

                    {{-- <a href="{{ route('profile.edit') }}" --}}
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg transition
                        {{ request()->routeIs('profile.*')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">

                        <x-heroicon-o-user-circle class="w-5 h-5"/>

                        <span class="ml-3">

                            My Profile

                        </span>

                    </a>

                </li>

                <li>

                    {{-- <a href="{{ route('settings.index') }}" --}}
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg transition
                        {{ request()->routeIs('settings.*')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">

                        <x-heroicon-o-cog-6-tooth class="w-5 h-5"/>

                        <span class="ml-3">

                            Settings

                        </span>

                    </a>

                </li>

            </ul>

        </div>

    </div>

</aside>