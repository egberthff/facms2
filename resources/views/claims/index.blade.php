<x-app-layout>

    <div class="p-4 bg-white border border-blue-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">

        <div class="flex justify-between items-center mb-4 w-full p-2">

            <!-- Left End: Filter Dropdown -->

            <div class="flex items-center">

                <label for="member-status-filter" class="sr-only">Filter by Status</label>

                <select id="member-status-filter" class="bg-slate-700 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">

                    <option value="">All Members</option>

                    <option value="Active" selected>Active Members</option>

                    <option value="Inactive">Inactive Members</option>

                </select>

            </div>  

            <!-- Right End: Add Action -->

            <div class="flex">
</x-app-layout>