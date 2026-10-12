<nav class="bg-white border-b border-gray-200">

    <div class="px-6 py-4 flex justify-between items-center">

        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="sidebarOpen = !sidebarOpen"
                class="inline-flex items-center justify-center rounded-lg p-2 text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 lg:hidden"
                aria-controls="logo-sidebar"
                :aria-expanded="sidebarOpen.toString()"
                aria-label="Toggle navigation menu">
                <x-heroicon-o-bars-3 class="h-6 w-6" />
            </button>

            <h1 class="text-xl font-semibold">

                {{ $title ?? 'Dashboard' }}

            </h1>

        </div>

        <div class="flex items-center gap-4">

            <x-notification-dropdown />
            <x-user-dropdown />

        </div>

    </div>

</nav>