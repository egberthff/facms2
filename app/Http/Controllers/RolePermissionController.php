<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    // Render our matrix landing page
    public function index()
    {
        return view('admin.roles-permissions.index', [
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all()
        ]);
    }

    // Process massive multi-role checkbox state updates synchronously
    public function syncMatrix(Request $request)
    {
        $allRoles = Role::all();
        $submittedMatrix = $request->input('role_permissions', []);

        foreach ($allRoles as $role) {
            // Extract the permitted array strings assigned to this specific iteration item ID 
            $permissionsToSync = $submittedMatrix[$role->id] ?? [];
            
            // Spatie handles the detachment/reattachment automatically
            $role->syncPermissions($permissionsToSync);
        }

        return redirect()->back()->with('success', 'Security matrix structure synchronized perfectly.');
    }
}
