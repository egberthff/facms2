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
            <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Overview
            </div>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                        {{ request()->routeIs('dashboard')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">
                        <x-heroicon-o-home class="h-5 w-5 shrink-0"/>
                        <span class="truncate">
                            Dashboard
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <hr class="my-3 border-slate-700/70">
        <!-- ========================================= -->
        <!-- CLAIMS -->
        <!-- ========================================= -->
        <div class="mb-4">
            <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Claims
            </div>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('claim.create') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                        {{ request()->routeIs('claim.create')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">
                        <x-heroicon-o-plus-circle class="h-5 w-5 shrink-0"/>
                        <span class="truncate">
                            Submit Claim
                        </span>
                    </a>
                </li>
                <li>
                     <a href="{{ route('claim.my', Auth::user()) }}" 
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                        {{ request()->routeIs('claim.my')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">
                        <x-heroicon-o-document-text class="h-5 w-5 shrink-0"/>
                        <span class="truncate">
                            My Claims
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('balance.my-metrics') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                        {{ request()->routeIs('balance.*')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">
                        <x-heroicon-o-banknotes class="h-5 w-5 shrink-0"/>
                        <span class="truncate">
                            Balance
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <hr class="my-3 border-slate-700/70">
        <!-- ========================================= -->
        <!-- Contibutions -->
        <!-- ========================================= -->
                <div class="mb-4">
                    <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        Contributions
                    </div>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('member.my-contributions' , Auth::user()) }}"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                {{ request()->routeIs('member.my-contributions')
                                    ? 'bg-slate-700 text-white'
                                    : 'text-white hover:bg-slate-700' }}">
                                <x-heroicon-o-chart-bar-square class="h-5 w-5 shrink-0"/>
                                <span class="truncate">
                                   My Contributions
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
        <hr class="my-3 border-slate-700/70">
        <!-- ========================================= -->
        <!-- ACCOUNT -->
        <!-- ========================================= -->
        <div>
            <ul class="space-y-1">
                <li>
                    {{-- <a href="{{ route('profile.edit') }}" --}}
                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                        {{ request()->routeIs('profile.*')
                            ? 'bg-slate-700 text-white'
                            : 'text-white hover:bg-slate-700' }}">
                        <x-heroicon-o-user-circle class="h-5 w-5 shrink-0"/>
                        <span class="truncate">
                            My Profile
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>