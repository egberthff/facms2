<nav class="sticky top-0 z-20 border-b border-gray-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">

    <div class="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">

        <div class="flex min-w-0 items-center gap-3">
            <button
                type="button"
                @click="sidebarOpen = !sidebarOpen"
                class="-ml-1 inline-flex items-center justify-center rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 lg:hidden"
                aria-controls="logo-sidebar"
                :aria-expanded="sidebarOpen.toString()"
                aria-label="Toggle navigation menu">
                <x-heroicon-o-bars-3 class="h-6 w-6" />
            </button>

            <h1 class="truncate text-lg font-semibold tracking-tight text-gray-900 sm:text-xl">

                {{ $title ?? 'Dashboard' }}

            </h1>

        </div>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">

            <x-notification-dropdown />
            <x-user-dropdown />

        </div>

    </div>

</nav>