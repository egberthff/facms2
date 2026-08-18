<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionController extends Controller
{
    // Render our matrix landing page
    // public function index()
    // {
    //     return view('admin.roles-permissions.index', [
    //         'allUsers' => User::with('member.campus')->get(),
    //         'roles' => Role::with('permissions')->get(),
    //         'permissions' => Permission::all(),
        
    //     ]);
    // }

    public function index()
    {
        $user = Auth::user();
    
        $usersQuery = User::with('member.campus');
        
        $isFederated = $user->hasRole('Federated President');
        $hasFullAccess = $isFederated || $user->can('view all campus data');
    
        if (!$hasFullAccess) {
            $userCampusId = $user->member->campus_id ?? null;
            
            $usersQuery->whereHas('member', function ($query) use ($userCampusId) {
                $query->where('campus_id', $userCampusId);
            });
        }
    
        // 4. Execute and return to view
        return view('admin.roles-permissions.index', [
            'allUsers'    => $usersQuery->get(),
            'roles'       => Role::with('permissions')->get(),
            'permissions' => Permission::all(),
            'isFederated' => $isFederated
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
    public function syncUserAccess(Request $request)
    {
        // Validate that the targeted user exists
        $request->validate([
            'user_id'       => 'required|exists:users,user_id',
            'permissions'   => 'array|nullable|required_if:action_type,permissions',
            'role'          => 'string|nullable|required_if:action_type,role',
            'action_type'   => 'required|in:role,permissions', // Added 'in' to restrict valid values
        ]);

       try {
            // Find the user being modified
            $user = User::findOrFail($request->input('user_id'));
            $actionType = $request->input('action_type');       

            // Initialize response variables
            $status = 'success';
            $message = '';      

            if ($actionType === 'permissions') {
                // Ensure permissions is an array (validation handles it, but safety check is good)
                $permissionsToSync = $request->input('permissions', []);        

                // Spatie replaces all old direct user permissions with this new set
                $user->syncPermissions($permissionsToSync);     

                $message = "Fine-grained permissions updated for user: {$user->name}.";
            } 
            elseif ($actionType === 'role') {

                $roleName = $request->input('role');
                $user->syncRoles($roleName);

                $message = "Role updated for user: {$user->name}.";
            } 
            else {
                throw new \InvalidArgumentException("Invalid action_type provided.");
            }       

            return response()->json([
                'status'  => $status,
                'message' => $message
            ], 200);        

        } catch (\Exception $e) {
            \Log::error('Error updating user permissions or role: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => "Failed to update user {$actionType}."
            ], 500);
        }
    }

    // 1. Fetch user permissions dynamically via AJAX
    public function getUserAccess(User $user)
    {
        return response()->json([
            'status' => 'success',
            'direct_permissions' => $user->getDirectPermissionNames(), // Returns array of permission names
            'roles' => $user->roles->pluck('name'), // Returns array of role names
        ]);
    }
    
}
