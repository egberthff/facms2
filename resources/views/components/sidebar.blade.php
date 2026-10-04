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
            <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Overview
            </div>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white">
                        <x-heroicon-o-home class="h-5 w-5 shrink-0"/>
                        <span class="truncate">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <hr class="border-slate-700 my-4" />

        <!-- CLAIMS SECTION -->
        <div class="mb-4">
            <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Claims
            </div>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('claim.list') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white">
                        <x-heroicon-o-document-text class="h-5 w-5 shrink-0"/>
                        <span class="truncate">All Claims</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('disbursement.index')}}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white">
                        <x-heroicon-o-credit-card class="h-5 w-5 shrink-0"/>
                        <span class="truncate">Disbursement</span>
                    </a>
                </li>
            </ul>
        </div>

        <hr class="border-slate-700 my-4" />

        <!-- CONTRIBUTION SECTION -->
        <div class="mb-4">
            <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Contributions
            </div>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('contributions.monitoring') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white">
                        <x-heroicon-o-chart-bar-square class="h-5 w-5 shrink-0"/>
                        <span class="truncate">All Contributions</span>
                    </a>
                </li>
            </ul>
        </div>

        <hr class="border-slate-700 my-4" />

        <!-- MANAGEMENT SECTION -->
        <div class="mb-4">
            <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Management
            </div>
            <ul class="space-y-1">
                @role('President|Treasurer|Federated President|Federated Treasurer')
                <li>
                    <a href="{{ route('members.index') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white">
                        <x-heroicon-o-users class="h-5 w-5 shrink-0"/>
                        <span class="truncate">Members</span>
                    </a>
                </li>
                @endrole
                @role('President|Federated President' )
                <li>
                    <a href="{{ route('admin.permissions.index') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white">
                        <x-heroicon-o-shield-exclamation class="h-5 w-5 shrink-0"/>
                        <span class="truncate">Roles & Permissions</span>
                    </a>
                </li>
                @endrole
                <li>
                    <a href="{{ route('treasury.index') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white">
                        <x-heroicon-o-building-library class="h-5 w-5 shrink-0"/>
                        <span class="truncate">Banking</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.index') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white">
                        <x-heroicon-o-chart-bar class="h-5 w-5 shrink-0"/>
                        <span class="truncate">Reports</span>
                    </a>
                </li>
            </ul>
        </div>

        <hr class="border-slate-700 my-4" />

        <!-- SETTINGS SECTION -->
        <div>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('settings.index') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                        {{ request()->routeIs('settings.*')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">

                        <x-heroicon-o-cog-6-tooth class="h-5 w-5 shrink-0"/>

                        <span class="truncate">

                            Settings

                        </span>

                    </a>
                </li>
            </ul>
        </div>

    </div>
</aside>
