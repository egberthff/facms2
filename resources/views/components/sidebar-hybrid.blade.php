<aside
    id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen -translate-x-full lg:translate-x-0 transition-transform duration-300 bg-slate-800 border-r border-slate-700"
    :class="{ 'translate-x-0': sidebarOpen }"
    @click="if (window.innerWidth < 1024) sidebarOpen = false">
    <div class="h-full px-3 py-4 overflow-y-auto">
        <!-- ========================================= -->
        <!-- LOGO -->
        <!-- ========================================= -->
        <a href="{{ route('dashboard') }}" class="mb-6 flex items-center gap-3 px-2">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white p-1 shadow-sm">
                <img
                    src="{{ asset('storage/images/logo/bisu_logo.png') }}"
                    alt="Logo"
                    class="w-full h-full object-cover rounded-lg">
            </div>
            <div class="min-w-0">
                <div class="font-semibold tracking-tight text-white">FacAssoc</div>
                <div class="text-[11px] uppercase tracking-wider text-slate-400">Portal Dashboard</div>
            </div>
        </a>

        <!-- ========================================= -->
        <!-- OVERVIEW / GENERAL DASHBOARD -->
        <!-- ========================================= -->
        <div class="mb-4">
            <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Overview
            </div>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                        {{ request()->routeIs('dashboard') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                        <x-heroicon-o-home class="h-5 w-5 shrink-0"/>
                        <span class="truncate">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ========================================= -->
        <!-- MEMBER ZONE (Claims & Balance) -->
        <!-- ========================================= -->
        @canany(['create claims', 'view own claims', 'view member metrics'])
            <hr class="my-3 border-slate-700/70">
            <div class="mb-4">
                <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    Member Actions
                </div>
                <ul class="space-y-1">
                    @can('create claims')
                        <li>
                            <a href="{{ route('claim.create') }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('claim.create') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-plus-circle class="h-5 w-5 shrink-0"/>
                                <span class="truncate">Submit Claim</span>
                            </a>
                        </li>
                    @endcan
                    {{-- @can('view own claims')
                        <li>
                             <a href="{{ route('claim.my', Auth::id()) }}" 
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('claim.my') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-document-text class="h-5 w-5 shrink-0"/>
                                <span class="truncate">My Claims</span>
                             </a>
                        </li>
                    @endcan --}}
                    @can('view own claims')
                        <li>
                             <!-- Pass the User object or Auth::id() cleanly -->
                             <a href="{{ route('claim.my', Auth::id()) }}" 
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('claim.my') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-document-text class="h-5 w-5 shrink-0"/>
                                <span class="truncate">My Claims</span>
                             </a>
                        </li>
                    @endcan
                    @can('view member metrics')
                        <li>
                            <a href="{{ route('balance.my-metrics') }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('balance.*') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-banknotes class="h-5 w-5 shrink-0"/>
                                <span class="truncate">Balance</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        @endcanany

        <!-- ========================================= -->
        <!-- MEMBER ZONE (Contributions) -->
        <!-- ========================================= -->
        {{-- @can('view own contribution')
            <hr class="my-3 border-slate-700/70">
            <div class="mb-4">
                <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    Contributions
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('member.my-contributions', Auth::id()) }}"
                            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                            {{ request()->routeIs('member.my-contributions') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <x-heroicon-o-chart-bar-square class="h-5 w-5 shrink-0"/>
                            <span class="truncate">My Contributions</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endcan --}}
        @can('view own contribution')
            <ul class="mt-1 space-y-1">
            <li>
                <a href="{{ route('member.my-contributions', Auth::id()) }}"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                    {{ request()->routeIs('member.my-contributions') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                    <x-heroicon-o-chart-bar-square class="h-5 w-5 shrink-0"/>
                    <span class="truncate">My Contributions</span>
                </a>
            </li>
            </ul>
        @endcan

        <!-- ========================================= -->
        <!-- ADMIN MANAGEMENT ZONE -->
        <!-- ========================================= -->
        @canany(['view all claims', 'view disbursement', 'release payments', 'view all contribution', 'manage members', 'view campus treasury', 'view reports', 'manage users'])
            <hr class="my-3 border-slate-700/70">
            <div class="mb-4">
                <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-amber-500">
                    Administrative Management
                </div>
                <ul class="space-y-1">
                    @can('view all claims')
                        <li>
                            <a href="{{ route('claim.list') }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('claim.list') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-clipboard-document-list class="h-5 w-5 shrink-0"/>
                                <span class="truncate">All Claims</span>
                            </a>
                        </li>
                    @endcan

                    @canany(['view disbursement', 'release payments'])
                        <li>
                            <a href="{{ route('disbursement.index') }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('disbursement.*') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-credit-card class="h-5 w-5 shrink-0"/>
                                <span class="truncate">Disbursement</span>
                            </a>
                        </li>
                    @endcanany

                    @can('view all contribution')
                        <li>
                            <a href="{{ route('contributions.monitoring') }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('contributions.monitoring') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-circle-stack class="h-5 w-5 shrink-0"/>
                                <span class="truncate">All Contributions</span>
                            </a>
                        </li>
                    @endcan

                    @can('manage members')
                        <li>
                            <a href="{{ route('members.index') }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('members.*') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-users class="h-5 w-5 shrink-0"/>
                                <span class="truncate">Members</span>
                            </a>
                        </li>
                    @endcan

                    @can('view campus treasury')
                        <li>
                            <a href="{{ route('treasury.index') }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('treasury.*') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-building-library class="h-5 w-5 shrink-0"/>
                                <span class="truncate">Banking / Treasury</span>
                            </a>
                        </li>
                    @endcan

                    @can('view reports')
                        <li>
                            <a href="{{ route('reports.index') }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('reports.*') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-document-chart-bar class="h-5 w-5 shrink-0"/>
                                <span class="truncate">Reports</span>
                            </a>
                        </li>
                    @endcan

                    @can('manage users')
                        <li>
                            <a href="{{ route('admin.permissions.index') }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('admin.permissions.*') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-shield-check class="h-5 w-5 shrink-0 text-blue-400"/>
                                <span class="truncate text-blue-300">User Roles</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('settings.index') }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('settings.*') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                                <x-heroicon-o-cog-6-tooth class="h-5 w-5 shrink-0"/>
                                <span class="truncate">Settings</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        @endcanany
        {{-- <li>
            <a href="{{ route('faculty-deductions-summary') }}"
                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                {{ request()->routeIs('reports.*') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                <x-heroicon-o-document-chart-bar class="h-5 w-5 shrink-0"/>
                <span class="truncate">Faculty Deductions Summary</span>
            </a>
        </li> --}}

        <!-- ========================================= -->
        <!-- ACCOUNT / GLOBAL PROFILE -->
        <!-- ========================================= -->
        <hr class="my-3 border-slate-700/70">
        <div>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('members.profile') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                        {{ request()->routeIs('profile.*') ? 'bg-slate-700 text-white shadow-[inset_3px_0_0_var(--color-blue-400)]' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                        <x-heroicon-o-user-circle class="h-5 w-5 shrink-0"/>
                        <span class="truncate">My Profile</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>