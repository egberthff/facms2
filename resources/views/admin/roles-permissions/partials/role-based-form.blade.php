<form id='roles-permissions-form' action="{{ route('admin.permissions.sync') }}" method="POST" onsubmit="handleRolePermissionUpdate(event)" class="bg-white shadow rounded-xl border border-gray-200 overflow-hidden">
    @csrf
    @method('PUT')

    <!-- Block Context Title -->
    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold leading-6 text-gray-900">Role & Permission Matrix</h2>
            <p class="mt-1 text-xs text-gray-500">Map specific permissions directly to application tiers using the checkbox grid below.</p>
        </div>
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500 focus-visible:outline focus-visible:outline-2">
            Save Changes
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
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500">
            Save Changes
        </button>
    </div>
</form>