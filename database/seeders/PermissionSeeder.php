<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define modules and their actions for Coffee Ecommerce System
        $modules = [
            'dashboard' => ['read'],
            'products' => ['create', 'read', 'update', 'delete'],
            'categories' => ['create', 'read', 'update', 'delete'],
            'inventory' => ['create', 'read', 'update', 'delete'],
            'orders' => ['create', 'read', 'update', 'delete'],
            'customers' => ['create', 'read', 'update', 'delete'],
            'coffee_machines' => ['create', 'read', 'update', 'delete'],
            'coffee_beans' => ['create', 'read', 'update', 'delete'],
            'spare_parts' => ['create', 'read', 'update', 'delete'],
            'reports' => ['read', 'export'],
            'settings' => ['read', 'update'],
            'admin_users' => ['create', 'read', 'update', 'delete'],
            'roles' => ['create', 'read', 'update', 'delete'],
            'permissions' => ['read', 'update'],
            'tags' => ['create', 'read', 'update', 'delete'],
            'vendors' => ['create', 'read', 'update', 'delete'],
        ];

        // Create permissions
        $allPermissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permission = Permission::create([
                    'name' => "{$module}.{$action}",
                    'display_name' => $this->generateDisplayName($module, $action),
                    'module' => $module,
                    'action' => $action,
                    'description' => "Allow {$action} access to {$module} module"
                ]);
                $allPermissions[] = $permission->id;
            }
        }

        // Create Roles with detailed permissions

        // 1. Super Admin Role - Full access to everything
        $superAdmin = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Administrator',
            'description' => 'Full access to all system features including user management and system settings',
            'is_active' => true,
        ]);
        $superAdmin->permissions()->sync($allPermissions); // All permissions

        // 2. Manager Role - Business operations management
        $manager = Role::create([
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Manage products, inventory, orders, customers, and view reports',
            'is_active' => true,
        ]);

        $managerPermissions = Permission::whereIn('name', [
            // Dashboard
            'dashboard.read',

            // Products Management - Full access
            'products.create', 'products.read', 'products.update', 'products.delete',

            // Categories Management - Full access
            'categories.create', 'categories.read', 'categories.update', 'categories.delete',

            // Inventory Management - Full access
            'inventory.create', 'inventory.read', 'inventory.update', 'inventory.delete',

            // Orders Management - Full access
            'orders.create', 'orders.read', 'orders.update', 'orders.delete',

            // Customers Management - Full access
            'customers.create', 'customers.read', 'customers.update', 'customers.delete',

            // Coffee Product Categories - Full access
            'coffee_machines.create', 'coffee_machines.read', 'coffee_machines.update', 'coffee_machines.delete',
            'coffee_beans.create', 'coffee_beans.read', 'coffee_beans.update', 'coffee_beans.delete',
            'spare_parts.create', 'spare_parts.read', 'spare_parts.update', 'spare_parts.delete',

            // Reports - Read and export access
            'reports.read', 'reports.export',

            // Settings - Read only
            'settings.read',
        ])->pluck('id');
        $manager->permissions()->sync($managerPermissions);

        // 3. Staff Role - Limited daily operations
        $staff = Role::create([
            'name' => 'staff',
            'display_name' => 'Staff Member',
            'description' => 'Limited access for daily operations, order processing, and inventory updates',
            'is_active' => true,
        ]);

        $staffPermissions = Permission::whereIn('name', [
            // Dashboard
            'dashboard.read',

            // Products - Read only
            'products.read',

            // Categories - Read only
            'categories.read',

            // Inventory - Read and update (for stock adjustments)
            'inventory.read', 'inventory.update',

            // Orders - Read and update (for order processing)
            'orders.read', 'orders.update',

            // Customers - Read and update (for customer service)
            'customers.read', 'customers.update',

            // Coffee Product Categories - Read only
            'coffee_machines.read',
            'coffee_beans.read',
            'spare_parts.read',
        ])->pluck('id');
        $staff->permissions()->sync($staffPermissions);

        // 4. Inventory Manager Role - Specialized for inventory management
        $inventoryManager = Role::create([
            'name' => 'inventory_manager',
            'display_name' => 'Inventory Manager',
            'description' => 'Specialized role for managing inventory, stock levels, and warehouse operations',
            'is_active' => true,
        ]);

        $inventoryPermissions = Permission::whereIn('name', [
            'dashboard.read',
            'products.read', 'products.update', // Can update product info
            'categories.read',
            'inventory.create', 'inventory.read', 'inventory.update', 'inventory.delete', // Full inventory access
            'coffee_machines.read', 'coffee_machines.update',
            'coffee_beans.read', 'coffee_beans.update',
            'spare_parts.read', 'spare_parts.update',
            'reports.read', // Can view reports
        ])->pluck('id');
        $inventoryManager->permissions()->sync($inventoryPermissions);

        // 5. Customer Service Role - Customer and order focused
        $customerService = Role::create([
            'name' => 'customer_service',
            'display_name' => 'Customer Service',
            'description' => 'Handle customer inquiries, process orders, and manage customer accounts',
            'is_active' => true,
        ]);

        $customerServicePermissions = Permission::whereIn('name', [
            'dashboard.read',
            'products.read', // To help customers with product info
            'orders.read', 'orders.update', // Process orders
            'customers.create', 'customers.read', 'customers.update', // Manage customers
            'coffee_machines.read',
            'coffee_beans.read',
            'spare_parts.read',
        ])->pluck('id');
        $customerService->permissions()->sync($customerServicePermissions);

        $this->command->info('Permissions and Roles created successfully!');
        $this->command->info('Created Roles: Super Admin, Manager, Staff, Inventory Manager, Customer Service');
        $this->command->info('Total Permissions: ' . count($allPermissions));
    }

    /**
     * Generate display name for permission
     */
    private function generateDisplayName(string $module, string $action): string
    {
        $moduleNames = [
            'dashboard' => 'Dashboard',
            'products' => 'Products',
            'categories' => 'Categories',
            'inventory' => 'Inventory',
            'orders' => 'Orders',
            'customers' => 'Customers',
            'coffee_machines' => 'Coffee Machines',
            'coffee_beans' => 'Coffee Beans',
            'spare_parts' => 'Spare Parts',
            'reports' => 'Reports',
            'settings' => 'Settings',
            'admin_users' => 'Admin Users',
            'roles' => 'Roles',
            'permissions' => 'Permissions',
        ];

        $actionNames = [
            'create' => 'Create',
            'read' => 'View',
            'update' => 'Edit',
            'delete' => 'Delete',
            'export' => 'Export',
        ];

        $moduleName = $moduleNames[$module] ?? ucwords(str_replace('_', ' ', $module));
        $actionName = $actionNames[$action] ?? ucfirst($action);

        return "{$actionName} {$moduleName}";
    }
}
