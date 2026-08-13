<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionController extends Controller
{
    // Render our matrix landing page
    public function index()
    {
        return view('admin.roles-permissions.index', [
            'allUsers' => User::with('member.campus')->get(),
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all()
        ]);
    }

    // Process massive multi-role checkbox state updates synchronously - Roles based permission assignment
    public function syncMatrix(Request $request)
    {
        try {
            $allRoles = Role::all();
            $submittedMatrix = $request->input('role_permissions', []);
    
            foreach ($allRoles as $role) {
                // Extract the permitted array strings assigned to this specific iteration item ID 
                $permissionsToSync = $submittedMatrix[$role->id] ?? [];
                
                // Spatie handles the detachment/reattachment automatically
                $role->syncPermissions($permissionsToSync);
            }
    
            // Return JSON structure expected by your JavaScript Swal.fire block
            return response()->json([
                'status'  => 'success',
                'message' => 'Security matrix structure synchronized perfectly.'
            ], 200);
    
        } catch (\Exception $e) {
            // Return JSON error structure to safely fall into your AJAX error callback
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to synchronize security matrix.'
            ], 500);
        }
    }

    // Process user based checkbox state updates synchronously - User based fine grained permission assignment
    public function syncUserPermissions(Request $request)
    {
        // Validate that the targeted user exists
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'permissions' => 'array', 
        ]);

        try {
            // Find the user being modified
            $user = User::findOrFail($request->input('user_id'));

            // Grab the explicit permissions array (or empty array if none checked)
            $permissionsToSync = $request->input('permissions', []);

            // Spatie replaces all old direct user permissions with this new set
            $user->syncPermissions($permissionsToSync);

            return response()->json([
                'status'  => 'success',
                'message' => "Fine-grained permissions updated for user: {$user->name}."
            ], 200);

        } catch (\Exception $e) {
            \Log::error(print_r($e->getMessage(), true));
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to synchronize explicit user permissions.'
            ], 500);
        }
    }

    // 1. Fetch user permissions dynamically via AJAX
    public function getUserPermissions(User $user)
    {
        return response()->json([
            'status' => 'success',
            'direct_permissions' => $user->getDirectPermissionNames(), // Returns array of permission names
        ]);
    }
    
}
