<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Header -->
        <header class="mb-10 flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-200 pb-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">Access Control</h1>
                <p class="mt-2 text-sm text-gray-500">Configure global application roles and fine-tune individual module permissions.</p>
            </div>
            <!-- Action Buttons -->
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">+ New Permission</button>
                <button class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">+ New Role</button>
            </div>
        </header>

        <!-- Status Alerts -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-md bg-green-50 border border-green-200 flex items-center text-sm text-green-700">
                <svg class="h-5 w-5 text-green-400 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Quick Stats Widgets -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-10">
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6 border border-gray-100">
                <dt class="truncate text-sm font-medium text-gray-500">Total System Roles</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-indigo-600">{{ $roles->count() }}</dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6 border border-gray-100">
                <dt class="truncate text-sm font-medium text-gray-500">System Permissions</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-emerald-600">{{ $permissions->count() }}</dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6 border border-gray-100">
                <dt class="truncate text-sm font-medium text-gray-500">Active Core Guards</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">web / api</dd>
            </div>
        </div>

        <!-- Master Matrix Form -->
        <form action="{{ route('admin.permissions.sync') }}" method="POST" class="bg-white shadow rounded-xl border border-gray-200 overflow-hidden">
            @csrf
            @method('PUT')

            <!-- Block Context Title -->
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold leading-6 text-gray-900">Role & Permission Matrix</h2>
                    <p class="mt-1 text-xs text-gray-500">Map specific permissions directly to application tiers using the checkbox grid below.</p>
                </div>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2">
                    Save Changes Matrix
                </button>
            </div>

            <!-- Scrollable Responsive Matrix Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-sm font-semibold text-gray-900 w-1/4">System Module Permission</th>
                            @foreach($roles as $role)
                                <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 capitalize">
                                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-600/20">
                                        {{ $role->name }}
                                    </span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($permissions as $permission)
                            <tr class="hover:bg-slate-50 transition duration-150">
                                <!-- Permission Row Label -->
                                <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-gray-900">
                                    <div class="font-semibold text-gray-800">{{ $permission->name }}</div>
                                    <div class="text-xs text-gray-400 font-mono">guard: {{ $permission->guard_name }}</div>
                                </td>
                                
                                <!-- Active Association Checkboxes per Role -->
                                @foreach($roles as $role)
                                    <td class="whitespace-nowrap px-3 py-4 text-center">
                                        <label class="inline-flex items-center justify-center p-2 cursor-pointer group">
                                            <!-- Field maps structural array: role_permissions[role_id][] = permission_name -->
                                            <input type="checkbox" 
                                                   name="role_permissions[{{ $role->id }}][]" 
                                                   value="{{ $permission->name }}"
                                                   {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 transition group-hover:scale-110">
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $roles->count() + 1 }}" class="text-center py-12 text-sm text-gray-500">
                                    No permissions initialized yet. Run your Spatie database seeders.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Action Panel -->
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <span class="text-xs text-gray-400">Careful: Modifying live parameters alters immediate frontend controller checks.</span>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    Save Changes Matrix
                </button>
            </div>
        </form>
    </div>

</x-app-layout>
