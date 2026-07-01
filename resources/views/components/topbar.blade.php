<nav class="bg-white border-b border-gray-200">

    <div class="px-6 py-4 flex justify-between items-center">

        <div>

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