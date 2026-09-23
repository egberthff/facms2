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

        // Filter roles
        $rolesQuery = Role::with('permissions');
        if ($user->hasRole('President') && !$isFederated) {
            $rolesQuery->whereIn('name', ['Member', 'Treasurer', 'President']);
        }

        // Filter permissions
        $permissionsQuery = Permission::query();
        if ($user->hasRole('President') && !$isFederated) {
            // Exclude the federated permission
            $permissionsQuery->whereNotIn('name', ['view all campus data']);
        }

        $roleMapping = [
            'Federated President' => 'federatedPresident',
            'President'           => 'president',
            'Federated Treasurer' => 'federatedTreasurer',
            'Treasurer'           => 'treasurer',
        ];
        
        // 1. Fetch all matching users in ONE database query
        $users = User::role(array_keys($roleMapping))
            ->with('member.campus')
            ->get();
        
        // 2. Initialize the array structure with empty arrays
        $userRoles = array_fill_keys(array_values($roleMapping), []);
        
        // 3. Group them dynamically
        foreach ($users as $user) {
            // A user can theoretically have multiple roles, so we loop through their assigned roles
            foreach ($user->roles as $role) {
                if (isset($roleMapping[$role->name])) {
                    $key = $roleMapping[$role->name];
                    
                    $userRoles[$key][] = [
                        'name'   => $user->name,
                        'campus' => $user->member?->campus?->acronym ?? '',
                    ];
                }
            }
        }

        // Execute and return to view 
        return view('admin.roles-permissions.index', [ 
            'allUsers'    => $usersQuery->get(), 
            'roles'       => $rolesQuery->get(), 
            'permissions' => $permissionsQuery->get(), 
            'isFederated' => $isFederated,
            'userRoles'   => $userRoles, 
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
            'action_type'   => 'required|in:role,permissions', 
        ]); 

        try {
            $user = User::findOrFail($request->input('user_id'));
            $actionType = $request->input('action_type');           

            $status = 'success';
            $message = '';          

            if ($actionType === 'permissions') {
                $permissionsToSync = $request->input('permissions', []);        
                $user->syncPermissions($permissionsToSync);     
                $message = "Fine-grained permissions updated for user: {$user->name}.";
            } 
            elseif ($actionType === 'role') {
                $roleName = $request->input('role');
                $authUser = auth()->user(); // The admin performing the action
                // $user->syncRoles('Federated President');
                // return true;
                // Role constants
                $fedPresident = 'Federated President';   
                $president = 'President';
                $fedTreasurer = 'Federated Treasurer';
                $treasurer = 'Treasurer';
                $member = 'Member';
            
                // --- 1. AUTHORIZATION & PERMISSION CHECKS ---
            
                // A. Check restrictions for the 'President' role
                if ($authUser->hasRole($president)) {
                    // Presidents cannot touch Federated President or Federated Treasurer
                    if ($user->hasRole($fedPresident) || $user->hasRole($fedTreasurer)) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'Unauthorized. Presidents cannot modify Federated roles.'
                        ], 403);
                    }
            
                    // Presidents can only assign President, Treasurer, or Member
                    $allowedRolesForPresident = [$president, $treasurer, $member];
                    if (!in_array($roleName, $allowedRolesForPresident)) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'Unauthorized. Campus Presidents cannot assign this role.'
                        ], 403);
                    }
                }
            
                // B. Check if someone else is trying to alter a Federated President
                if ($user->hasRole($fedPresident) && $authUser->id !== $user->id) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Unauthorized. Only the Federated President can modify their own role.'
                    ], 403);
                }
            
            
                // --- 2. "LAST PERSON STANDING" SAFETY CHECKS ---
            
                // Guard Federated President count (Blocks self-demotion if they are the last one)
                if ($user->hasRole($fedPresident)) {
                    $federatedPresidentCount = User::role($fedPresident)->count(); 
            
                    if ($federatedPresidentCount <= 1 && $roleName !== $fedPresident) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => "Action denied. You cannot demote yourself because there must be at least one user with the '{$fedPresident}' role."
                        ], 422);
                    }
                } 
                
                // Guard President count
                if ($user->hasRole($president)) {
                    $campusPresidentCount = User::role($president)->count();
            
                    if ($campusPresidentCount <= 1 && $roleName !== $president) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => "Action denied. There must be at least one user with the '{$president}' role."
                        ], 422);
                    }
                }
            
                // --- 3. EXECUTE UPDATE ---
                
                $user->syncRoles($roleName);
                $message = "Role updated for user: {$user->name}.";
                
                return response()->json([
                    'status' => 'success',
                    'message' => $message
                ], 200);
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
