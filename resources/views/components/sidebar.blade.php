<aside
    id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform bg-slate-800 border-r border-slate-700">

    <div class="h-full px-3 py-4 overflow-y-auto">

        <a href="{{ route('dashboard') }}"
            class="flex items-center mb-8">

            <div class="w-13 h-13 rounded-lg bg-white flex items-center justify-center p-1">

                {{-- <x-heroicon-o-heart class="w-6 h-6 text-white"/> --}}
                <img src="{{ asset('storage/images/logo/bisu_logo.png') }}" alt="Logo" class="w-full h-full object-cover rounded-lg">

            </div>

            <div class="ml-5">

                <div class="text-white font-bold">

                    <span>FacAssoc</span>

                </div>

                <div class="text-xs text-slate-300">

                    <span>ADMIN PANEL</span>

                </div>

            </div>

        </a>

        <!-- OVERVIEW SECTION -->
        <div class="mb-4">
            <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                Overview
            </div>
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg text-white hover:bg-slate-700">
                        <x-heroicon-o-home class="w-5 h-5"/>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <hr class="border-slate-700 my-4" />

        <!-- CLAIMS SECTION -->
        <div class="mb-4">
            <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                Claims
            </div>
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('claim.list') }}"
                        class="flex items-center p-3 rounded-lg text-white hover:bg-slate-700">
                        <x-heroicon-o-document-text class="w-5 h-5"/>
                        <span class="ml-3">All Claims</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('disbursement.index')}}"
                        class="flex items-center p-3 rounded-lg text-white hover:bg-slate-700">
                        <x-heroicon-o-credit-card class="w-5 h-5"/>
                        <span class="ml-3">Disbursement</span>
                    </a>
                </li>
            </ul>
        </div>

        <hr class="border-slate-700 my-4" />

        <!-- CONTRIBUTION SECTION -->
        <div class="mb-4">
            <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                Contributions
            </div>
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('contributions.monitoring') }}"
                        class="flex items-center p-3 rounded-lg text-white hover:bg-slate-700">
                        <x-heroicon-o-chart-bar-square class="w-5 h-5"/>
                        <span class="ml-3">All Contributions</span>
                    </a>
                </li>
            </ul>
        </div>

        <hr class="border-slate-700 my-4" />

        <!-- MANAGEMENT SECTION -->
        <div class="mb-4">
            <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                Management
            </div>
            <ul class="space-y-2">
                @role('President|Treasurer')
                <li>
                    <a href="{{ route('members.index') }}"
                        class="flex items-center p-3 rounded-lg text-white hover:bg-slate-700">
                        <x-heroicon-o-users class="w-5 h-5"/>
                        <span class="ml-3">Members</span>
                    </a>
                </li>
                @endrole
                @role('President')
                <li>
                    {{-- <a href="{{ route('users.index') }}" --}}
                    <a href="{{ route('treasury.index') }}"
                        class="flex items-center p-3 rounded-lg text-white hover:bg-slate-700">
                        <x-heroicon-o-shield-exclamation class="w-5 h-5"/>
                        <span class="ml-3">Roles & Permissions</span>
                    </a>
                </li>
                @endrole
                <li>
                    <a href="{{ route('treasury.index') }}"
                        class="flex items-center p-3 rounded-lg text-white hover:bg-slate-700">
                        <x-heroicon-o-building-library class="w-5 h-5"/>
                        <span class="ml-3">Banking</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.index') }}"
                        class="flex items-center p-3 rounded-lg text-white hover:bg-slate-700">
                        <x-heroicon-o-chart-bar class="w-5 h-5"/>
                        <span class="ml-3">Reports</span>
                    </a>
                </li>
            </ul>
        </div>

        <hr class="border-slate-700 my-4" />

        <!-- SETTINGS SECTION -->
        <div>
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('settings.index') }}"
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
