<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('roles.read')) {
            abort(403, 'Unauthorized access');
        }

        $query = Role::withCount(['adminUsers', 'permissions']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $roles = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.usermanagement.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('roles.create')) {
            abort(403, 'Unauthorized access');
        }

        $permissions = Permission::orderBy('module')->orderBy('action')->get();
        $groupedPermissions = $permissions->groupBy('module');

        return view('admin.usermanagement.roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('roles.create')) {
            abort(403, 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
            'is_active' => 'boolean',
        ], [
            'name.regex' => 'Role name must contain only lowercase letters and underscores.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Create role
        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'created_by' => auth('admin')->id(),
        ]);

        // Assign permissions
        $role->permissions()->sync($request->permissions);

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('roles.read')) {
            abort(403, 'Unauthorized access');
        }

        $role->load(['permissions', 'adminUsers']);
        $groupedPermissions = $role->permissions->groupBy('module');

        return view('admin.usermanagement.roles.show', compact('role', 'groupedPermissions'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('roles.update')) {
            abort(403, 'Unauthorized access');
        }

        // Prevent editing super_admin role by non-super-admin
        if ($role->name === 'super_admin' && !auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can edit this role.');
        }

        $permissions = Permission::orderBy('module')->orderBy('action')->get();
        $groupedPermissions = $permissions->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.usermanagement.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('roles.update')) {
            abort(403, 'Unauthorized access');
        }

        // Prevent editing super_admin role by non-super-admin
        if ($role->name === 'super_admin' && !auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can edit this role.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id), 'regex:/^[a-z_]+$/'],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
            'is_active' => 'boolean',
        ], [
            'name.regex' => 'Role name must contain only lowercase letters and underscores.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Update role
        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'updated_by' => auth('admin')->id(),
        ]);

        // Update permissions
        $role->permissions()->sync($request->permissions);

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('roles.delete')) {
            abort(403, 'Unauthorized access');
        }

        // Prevent deleting system roles
        $systemRoles = ['super_admin', 'manager', 'staff'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->back()->with('error', 'Cannot delete system roles.');
        }

        // Check if role is assigned to any users
        if ($role->adminUsers()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete role. It is assigned to active users.');
        }

        $role->permissions()->detach(); // Remove permission associations
        $role->delete();

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role deleted successfully.');
    }

    /**
     * Toggle role status
     */
    public function toggleStatus(Role $role)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('roles.update')) {
            abort(403, 'Unauthorized access');
        }

        // Prevent deactivating system roles
        $systemRoles = ['super_admin'];
        if (in_array($role->name, $systemRoles)) {
            return response()->json(['error' => 'Cannot deactivate system roles'], 403);
        }

        $role->update([
            'is_active' => !$role->is_active,
            'updated_by' => auth('admin')->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role status updated successfully',
            'status' => $role->is_active
        ]);
    }
}
