<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions
     */
    public function index(Request $request)
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can manage permissions');
        }

        // Get unique modules and actions for filters
        $modules = Permission::distinct('module')->pluck('module')->sort();
        $actions = Permission::distinct('action')->pluck('action')->sort();

        return view('admin.usermanagement.permissions.index', compact('modules', 'actions'));
    }

    /**
     * Get permissions data for DataTable (Ajax)
     */
    public function getData(Request $request)
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Permission::withCount('roles');

        // Global search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by module
        if ($request->has('module_filter') && !empty($request->module_filter)) {
            $query->where('module', $request->module_filter);
        }

        // Filter by action
        if ($request->has('action_filter') && !empty($request->action_filter)) {
            $query->where('action', $request->action_filter);
        }

        // Get total count before filtering
        $totalRecords = Permission::count();
        $filteredRecords = $query->count();

        // Handle ordering
        if ($request->has('order')) {
            $orderColumn = $request->columns[$request->order[0]['column']]['data'];
            $orderDirection = $request->order[0]['dir'];

            switch ($orderColumn) {
                case 'display_name':
                    $query->orderBy('display_name', $orderDirection);
                    break;
                case 'module':
                    $query->orderBy('module', $orderDirection);
                    break;
                case 'action':
                    $query->orderBy('action', $orderDirection);
                    break;
                case 'roles_count':
                    $query->orderBy('roles_count', $orderDirection);
                    break;
                case 'created_at':
                    $query->orderBy('created_at', $orderDirection);
                    break;
                default:
                    $query->orderBy('module')->orderBy('action');
            }
        } else {
            $query->orderBy('module')->orderBy('action');
        }

        // Handle pagination
        if ($request->has('start') && $request->has('length')) {
            $query->offset($request->start)->limit($request->length);
        }

        $permissions = $query->get();

        // Format data for DataTable
        $data = $permissions->map(function($permission, $index) use ($request) {
            return [
                'DT_RowId' => 'permission_' . $permission->id,
                'index' => ($request->start ?? 0) + $index + 1,
                'display_name' => $permission->display_name,
                'name' => $permission->name,
                'description' => $permission->description,
                'module' => $permission->module,
                'action' => $permission->action,
                'roles_count' => $permission->roles_count,
                'created_at' => $permission->created_at,
                'can_delete' => $permission->roles()->count() === 0,
                'id' => $permission->id
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new permission
     */
    public function create()
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can manage permissions');
        }

        // Get existing modules for suggestions
        $existingModules = Permission::distinct('module')->pluck('module')->sort();
        $existingActions = Permission::distinct('action')->pluck('action')->sort();

        return view('admin.usermanagement.permissions.create', compact('existingModules', 'existingActions'));
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can manage permissions');
        }

        $validator = Validator::make($request->all(), [
            'module' => 'required|string|max:100|regex:/^[a-z_]+$/',
            'action' => 'required|string|max:50|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'module.regex' => 'Module name must contain only lowercase letters and underscores.',
            'action.regex' => 'Action name must contain only lowercase letters and underscores.',
        ]);

        // Custom validation for unique combination
        $validator->after(function ($validator) use ($request) {
            $name = $request->module . '.' . $request->action;
            if (Permission::where('name', $name)->exists()) {
                $validator->errors()->add('module', 'This module and action combination already exists.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Create permission
        $permission = Permission::create([
            'name' => $request->module . '.' . $request->action,
            'display_name' => $request->display_name,
            'module' => $request->module,
            'action' => $request->action,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.usermanagement.permissions.index')
                        ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified permission
     */
    public function show(Permission $permission)
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can manage permissions');
        }

        $permission->load('roles');

        return view('admin.usermanagement.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission
     */
    public function edit(Permission $permission)
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can manage permissions');
        }

        // Get existing modules for suggestions
        $existingModules = Permission::distinct('module')->pluck('module')->sort();
        $existingActions = Permission::distinct('action')->pluck('action')->sort();

        return view('admin.usermanagement.permissions.edit', compact('permission', 'existingModules', 'existingActions'));
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, Permission $permission)
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can manage permissions');
        }

        $validator = Validator::make($request->all(), [
            'module' => 'required|string|max:100|regex:/^[a-z_]+$/',
            'action' => 'required|string|max:50|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'module.regex' => 'Module name must contain only lowercase letters and underscores.',
            'action.regex' => 'Action name must contain only lowercase letters and underscores.',
        ]);

        // Custom validation for unique combination
        $validator->after(function ($validator) use ($request, $permission) {
            $name = $request->module . '.' . $request->action;
            if (Permission::where('name', $name)->where('id', '!=', $permission->id)->exists()) {
                $validator->errors()->add('module', 'This module and action combination already exists.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Update permission
        $permission->update([
            'name' => $request->module . '.' . $request->action,
            'display_name' => $request->display_name,
            'module' => $request->module,
            'action' => $request->action,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.usermanagement.permissions.index')
                        ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission
     */
    public function destroy(Permission $permission)
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can manage permissions');
        }

        // Check if permission is assigned to any roles
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete permission. It is assigned to active roles.'
            ], 422);
        }

        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully.'
        ]);
    }

    /**
     * Bulk create permissions for a module
     */
    public function bulkCreate(Request $request)
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can manage permissions');
        }

        $validator = Validator::make($request->all(), [
            'module' => 'required|string|max:100|regex:/^[a-z_]+$/',
            'actions' => 'required|array|min:1',
            'actions.*' => 'string|max:50|regex:/^[a-z_]+$/',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $created = 0;
        $skipped = 0;

        foreach ($request->actions as $action) {
            $name = $request->module . '.' . $action;

            // Check if permission already exists
            if (!Permission::where('name', $name)->exists()) {
                Permission::create([
                    'name' => $name,
                    'display_name' => $this->generateDisplayName($request->module, $action),
                    'module' => $request->module,
                    'action' => $action,
                    'description' => "Allow {$action} access to {$request->module} module",
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        $message = "Created {$created} permissions.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} existing permissions.";
        }

        return redirect()->route('admin.usermanagement.permissions.index')
                        ->with('success', $message);
    }

    /**
     * Show bulk create form
     */
    public function showBulkCreate()
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            abort(403, 'Only super administrators can manage permissions');
        }

        $existingModules = Permission::distinct('module')->pluck('module')->sort();
        $commonActions = ['read', 'create', 'update', 'delete'];

        return view('admin.usermanagement.permissions.bulk-create', compact('existingModules', 'commonActions'));
    }

    /**
     * Generate display name from module and action
     */
    private function generateDisplayName($module, $action)
    {
        $moduleFormatted = ucwords(str_replace('_', ' ', $module));
        $actionFormatted = ucwords(str_replace('_', ' ', $action));

        return "{$actionFormatted} {$moduleFormatted}";
    }

    /**
     * Get permissions by module (AJAX)
     */
    public function getByModule(Request $request)
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $module = $request->get('module');
        $permissions = Permission::where('module', $module)
                                ->orderBy('action')
                                ->get(['id', 'name', 'display_name', 'action']);

        return response()->json($permissions);
    }

    /**
     * Check permission name availability (AJAX)
     */
    public function checkAvailability(Request $request)
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $module = $request->get('module');
        $action = $request->get('action');
        $excludeId = $request->get('exclude_id');

        if (!$module || !$action) {
            return response()->json(['available' => false, 'message' => 'Module and action are required']);
        }

        $name = $module . '.' . $action;

        $query = Permission::where('name', $name);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'name' => $name,
            'message' => $exists ? 'This permission already exists' : 'Permission name is available'
        ]);
    }

    /**
     * Get summary statistics for dashboard
     */
    public function getStats()
    {
        // Check permission - only super admin can manage permissions
        if (!auth('admin')->user()->hasRole('super_admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_permissions' => Permission::count(),
            'total_modules' => Permission::distinct('module')->count(),
            'total_actions' => Permission::distinct('action')->count(),
            'total_role_assignments' => Permission::withCount('roles')->get()->sum('roles_count')
        ];

        return response()->json($stats);
    }
}
