<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Display a listing of admin users
     */
   /**
 * Display a listing of admin users (simplified)
 */
public function index(Request $request)
{
    // Check permission
    if (!auth('admin')->user()->hasPermission('admin_users.read')) {
        abort(403, 'Unauthorized access');
    }

    $roles = Role::where('is_active', true)->get();
    return view('admin.usermanagement.users.index', compact('roles'));
}

/**
 * Get admin users data for DataTable (Ajax) - Simplified
 */
public function getData(Request $request)
{
    // Check permission
    if (!auth('admin')->user()->hasPermission('admin_users.read')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Start with basic query
    $query = AdminUser::with('roles')->where('id', '!=', auth('admin')->id());

    // Simple search
    if ($request->filled('search.value')) {
        $search = $request->input('search.value');
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%");
        });
    }

    // Role filter
    if ($request->filled('role_filter')) {
        $query->whereHas('roles', function($q) use ($request) {
            $q->where('name', $request->role_filter);
        });
    }

    // Status filter
    if ($request->filled('status_filter') && $request->status_filter !== '') {
        $query->where('is_active', (int) $request->status_filter);
    }

    // Get totals
    $totalRecords = AdminUser::where('id', '!=', auth('admin')->id())->count();
    $filteredRecords = $query->count();

    // Simple ordering
    $query->orderBy('created_at', 'desc');

    // Pagination
    if ($request->filled('start') && $request->filled('length')) {
        $query->skip($request->start)->take($request->length);
    }

    $users = $query->get();

    // Format data for DataTable
    $data = [];
    foreach ($users as $index => $user) {
        $data[] = [
            'DT_RowId' => 'user_' . $user->id,
            'index' => ($request->input('start', 0) + $index + 1),
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'phone' => $user->phone ?? '',
            'roles' => $user->roles->toArray(),
            'is_active' => (bool) $user->is_active,
            'created_at' => $user->created_at->toISOString(),
            'id' => $user->id,
            'can_edit' => auth('admin')->user()->hasPermission('admin_users.update'),
            'can_delete' => auth('admin')->user()->hasPermission('admin_users.delete'),
            'can_view' => auth('admin')->user()->hasPermission('admin_users.read'),
        ];
    }

    return response()->json([
        'draw' => (int) $request->input('draw', 1),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
    ]);
}


    /**
     * Show the form for creating a new admin user
     */
    public function create()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('admin_users.create')) {
            abort(403, 'Unauthorized access');
        }

        $roles = Role::where('is_active', true)->get();
        return view('admin.usermanagement.users.create', compact('roles'));
    }

    /**
     * Store a newly created admin user
     */
    public function store(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('admin_users.create')) {
            abort(403, 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admin_users',
            'username' => 'required|string|max:255|unique:admin_users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Create admin user
        $adminUser = AdminUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'created_by' => auth('admin')->id(),
            'email_verified_at' => now(),
        ]);

        // Assign roles
        $adminUser->roles()->sync($request->roles);

        return redirect()->route('admin.users.index')
                        ->with('success', 'Admin user created successfully.');
    }

    /**
     * Display the specified admin user
     */
    public function show(AdminUser $user)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('admin_users.read')) {
            abort(403, 'Unauthorized access');
        }

        $user->load(['roles', 'roles.permissions']);

        return view('admin.usermanagement.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified admin user
     */
    public function edit(AdminUser $user)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('admin_users.update')) {
            abort(403, 'Unauthorized access');
        }

        // Prevent editing own account through this interface
        if ($user->id === auth('admin')->id()) {
            return redirect()->route('admin.profile.edit')
                           ->with('info', 'Please use profile settings to edit your own account.');
        }

        $roles = Role::where('is_active', true)->get();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('admin.usermanagement.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified admin user
     */
    public function update(Request $request, AdminUser $user)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('admin_users.update')) {
            abort(403, 'Unauthorized access');
        }

        // Prevent editing own account
        if ($user->id === auth('admin')->id()) {
            return redirect()->back()->with('error', 'Cannot edit your own account here.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admin_users')->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('admin_users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Update admin user
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'phone' => $request->phone,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'updated_by' => auth('admin')->id(),
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Update roles
        $user->roles()->sync($request->roles);

        return redirect()->route('admin.users.index')
                        ->with('success', 'Admin user updated successfully.');
    }

    /**
     * Remove the specified admin user
     */
    public function destroy(AdminUser $user)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('admin_users.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        // Prevent deleting own account
        if ($user->id === auth('admin')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account.'
            ], 422);
        }

        // Prevent deleting if user has created content (optional check)
        // You can add more business logic here

        $user->roles()->detach(); // Remove role associations
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin user deleted successfully.'
        ]);
    }

    /**
     * Toggle admin user status
     */
    public function toggleStatus(AdminUser $user)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('admin_users.update')) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Prevent deactivating own account
        if ($user->id === auth('admin')->id()) {
            return response()->json(['error' => 'Cannot change your own status'], 403);
        }

        $user->update([
            'is_active' => !$user->is_active,
            'updated_by' => auth('admin')->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'status' => $user->is_active
        ]);
    }

    /**
     * Get summary statistics - Simplified
     */
    public function getStats()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('admin_users.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $currentUserId = auth('admin')->id();

        $stats = [
            'total_users' => AdminUser::where('id', '!=', $currentUserId)->count(),
            'active_users' => AdminUser::where('id', '!=', $currentUserId)->where('is_active', 1)->count(),
            'inactive_users' => AdminUser::where('id', '!=', $currentUserId)->where('is_active', 0)->count(),
            'total_roles' => Role::where('is_active', 1)->count()
        ];

        return response()->json($stats);
    }
}
