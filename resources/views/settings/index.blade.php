<x-app-layout>
    <div class="p-6 bg-gray-50 min-h-screen text-gray-800 font-sans">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 tracking-tight">System Settings</h2>
                    <p class="text-sm text-gray-500 mt-1">Review system settings and switch campus scope for privileged users.</p>
                </div>
                <form method="GET" action="{{ route('settings.index') }}" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                    @if($isPresident)
                        <select name="campus_filter" onchange="this.form.submit()" class="text-sm rounded-lg bg-white dark:bg-zinc-900 border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 focus:ring-blue-500 focus:border-blue-500 min-w-52">
                            <option value="">All Campuses (Global View)</option>
                            @foreach($allCampuses as $campus)
                                <option value="{{ $campus->id }}" {{ ($selectedCampusId ?? '') == $campus->id ? 'selected' : '' }}>{{ $campus->acronym }}</option>
                            @endforeach
                        </select>
                    @endif
                </form>
            </div>

            @if($isPresident)
                <div class="p-6 bg-gray-50 dark:bg-zinc-800/40 rounded-b-xl border-t border-gray-200 dark:border-zinc-800/80">
                    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-none">
                        @foreach($allCampuses as $campus)
                            <div class="shrink-0 bg-white dark:bg-zinc-900 px-3 py-1.5 rounded-lg border {{ ($selectedCampusId ?? '') == $campus->id ? 'border-blue-500 bg-blue-50/10' : 'border-gray-200 dark:border-zinc-800' }} text-xs">
                                <span class="font-bold text-gray-500 dark:text-gray-400">{{ $campus->acronym }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-semibold text-gray-900">Settings by Campus</h3>
            </div>

            <div class="p-6 space-y-4">
                @if(session('success'))
                    <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3">{{ session('success') }}</div>
                @endif

                @if($settings->isEmpty())
                    <div class="rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3">No settings available for the selected campus.</div>
                @else
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($settings as $setting)
                            <div class="rounded-2xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-5">
                                <div class="flex items-center justify-between gap-4 mb-4">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ str()->title(str_replace('_', ' ', $setting->setting_key)) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $setting->description }}</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-zinc-900 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:text-gray-300">{{ optional($setting->campus)->acronym ?? 'Campus' }}</span>
                                </div>
                                <div class="space-y-3">
                                    <form method="POST" action="{{ route('settings.update', $setting) }}" class="space-y-3">
                                        @csrf
                                        @method('PUT')

                                        <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">

                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Value</label>
                                        <input
                                            name="setting_value"
                                            value="{{ old('setting_value', $setting->setting_value) }}"
                                            class="block w-full rounded-lg border border-gray-300 bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                                        />

                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Update</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
