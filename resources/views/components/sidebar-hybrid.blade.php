<aside
    id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen -translate-x-full lg:translate-x-0 transition-transform duration-300 bg-slate-800 border-r border-slate-700"
    :class="{ 'translate-x-0': sidebarOpen }"
    @click="if (window.innerWidth < 1024) sidebarOpen = false">
    <div class="h-full px-3 py-4 overflow-y-auto">
        <!-- ========================================= -->
        <!-- LOGO -->
        <!-- ========================================= -->
        <a href="{{ route('dashboard') }}" class="flex items-center mb-8">
            <div class="w-13 h-13 rounded-lg bg-white flex items-center justify-center p-1">
                <img
                    src="{{ asset('storage/images/logo/bisu_logo.png') }}"
                    alt="Logo"
                    class="w-full h-full object-cover rounded-lg">
            </div>
            <div class="ml-5">
                <div class="text-white font-bold">FacAssoc</div>
                <div class="text-xs text-slate-300 uppercase">Portal Dashboard</div>
            </div>
        </a>

        <!-- ========================================= -->
        <!-- OVERVIEW / GENERAL DASHBOARD -->
        <!-- ========================================= -->
        <div class="mb-4">
            <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                Overview
            </div>
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg transition
                        {{ request()->routeIs('dashboard') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                        <x-heroicon-o-home class="w-5 h-5"/>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ========================================= -->
        <!-- MEMBER ZONE (Claims & Balance) -->
        <!-- ========================================= -->
        @canany(['create claims', 'view own claims', 'view member metrics'])
            <hr class="border-slate-700 my-4">
            <div class="mb-4">
                <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Member Actions
                </div>
                <ul class="space-y-2">
                    @can('create claims')
                        <li>
                            <a href="{{ route('claim.create') }}"
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('claim.create') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-plus-circle class="w-5 h-5"/>
                                <span class="ml-3">Submit Claim</span>
                            </a>
                        </li>
                    @endcan
                    {{-- @can('view own claims')
                        <li>
                             <a href="{{ route('claim.my', Auth::id()) }}" 
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('claim.my') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-document-text class="w-5 h-5"/>
                                <span class="ml-3">My Claims</span>
                             </a>
                        </li>
                    @endcan --}}
                    @can('view own claims')
                        <li>
                             <!-- Pass the User object or Auth::id() cleanly -->
                             <a href="{{ route('claim.my', Auth::id()) }}" 
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('claim.my') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-document-text class="w-5 h-5"/>
                                <span class="ml-3">My Claims</span>
                             </a>
                        </li>
                    @endcan
                    @can('view member metrics')
                        <li>
                            <a href="{{ route('balance.my-metrics') }}"
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('balance.*') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-banknotes class="w-5 h-5"/>
                                <span class="ml-3">Balance</span>
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
            <hr class="border-slate-700 my-4">
            <div class="mb-4">
                <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Contributions
                </div>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('member.my-contributions', Auth::id()) }}"
                            class="flex items-center p-3 rounded-lg transition
                            {{ request()->routeIs('member.my-contributions') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                            <x-heroicon-o-chart-bar-square class="w-5 h-5"/>
                            <span class="ml-3">My Contributions</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endcan --}}
        @can('view own contribution')
            <li>
                <a href="{{ route('member.my-contributions', Auth::id()) }}"
                    class="flex items-center p-3 rounded-lg transition
                    {{ request()->routeIs('member.my-contributions') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                    <x-heroicon-o-chart-bar-square class="w-5 h-5"/>
                    <span class="ml-3">My Contributions</span>
                </a>
            </li>
        @endcan

        <!-- ========================================= -->
        <!-- ADMIN MANAGEMENT ZONE -->
        <!-- ========================================= -->
        @canany(['view all claims', 'view disbursement', 'release payments', 'view all contribution', 'manage members', 'view campus treasury', 'view reports', 'manage users'])
            <hr class="border-slate-700 my-4">
            <div class="mb-4">
                <div class="px-3 mb-2 text-xs font-semibold text-amber-500 uppercase tracking-wider">
                    Administrative Management
                </div>
                <ul class="space-y-2">
                    @can('view all claims')
                        <li>
                            <a href="{{ route('claim.list') }}"
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('claim.list') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-clipboard-document-list class="w-5 h-5"/>
                                <span class="ml-3">All Claims</span>
                            </a>
                        </li>
                    @endcan

                    @canany(['view disbursement', 'release payments'])
                        <li>
                            <a href="{{ route('disbursement.index') }}"
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('disbursement.*') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-credit-card class="w-5 h-5"/>
                                <span class="ml-3">Disbursement</span>
                            </a>
                        </li>
                    @endcanany

                    @can('view all contribution')
                        <li>
                            <a href="{{ route('contributions.monitoring') }}"
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('contributions.monitoring') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-circle-stack class="w-5 h-5"/>
                                <span class="ml-3">All Contributions</span>
                            </a>
                        </li>
                    @endcan

                    @can('manage members')
                        <li>
                            <a href="{{ route('members.index') }}"
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('members.*') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-users class="w-5 h-5"/>
                                <span class="ml-3">Members</span>
                            </a>
                        </li>
                    @endcan

                    @can('view campus treasury')
                        <li>
                            <a href="{{ route('treasury.index') }}"
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('treasury.*') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-building-library class="w-5 h-5"/>
                                <span class="ml-3">Banking / Treasury</span>
                            </a>
                        </li>
                    @endcan

                    @can('view reports')
                        <li>
                            <a href="{{ route('reports.index') }}"
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('reports.*') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-document-chart-bar class="w-5 h-5"/>
                                <span class="ml-3">Reports</span>
                            </a>
                        </li>
                    @endcan

                    @can('manage users')
                        <li>
                            <a href="{{ route('admin.permissions.index') }}"
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('admin.permissions.*') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-shield-check class="w-5 h-5 text-blue-400"/>
                                <span class="ml-3 text-blue-300 font-medium">User Roles</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('settings.index') }}"
                                class="flex items-center p-3 rounded-lg transition
                                {{ request()->routeIs('settings.*') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-cog-6-tooth class="w-5 h-5"/>
                                <span class="ml-3">Settings</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        @endcanany
        <li>
            <a href="{{ route('faculty-deductions-summary') }}"
                class="flex items-center p-3 rounded-lg transition
                {{ request()->routeIs('reports.*') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                <x-heroicon-o-document-chart-bar class="w-5 h-5"/>
                <span class="ml-3">Faculty Deductions Summary</span>
            </a>
        </li>

        <!-- ========================================= -->
        <!-- ACCOUNT / GLOBAL PROFILE -->
        <!-- ========================================= -->
        <hr class="border-slate-700 my-4">
        <div>
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg transition
                        {{ request()->routeIs('profile.*') ? 'bg-slate-700 text-white' : 'text-white hover:bg-slate-700' }}">
                        <x-heroicon-o-user-circle class="w-5 h-5"/>
                        <span class="ml-3">My Profile</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>