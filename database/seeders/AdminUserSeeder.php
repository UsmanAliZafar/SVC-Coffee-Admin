<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        DB::table('admin_user_roles')->truncate();
        DB::table('admin_users')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Create Super Admin User
        $superAdmin = AdminUser::create([
            'name' => 'Super Administrator',
            'email' => 'admin@coffee.com',
            'password' => 'password123', // Will be automatically hashed by the model
            'phone' => '+1234567890',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdmin->roles()->attach($superAdminRole);
        }

        // 2. Create Manager User
        $manager = AdminUser::create([
            'name' => 'Store Manager',
            'email' => 'manager@coffee.com',
            'password' => 'password123',
            'phone' => '+1234567891',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $manager->roles()->attach($managerRole);
        }

        // 3. Create Staff User
        $staff = AdminUser::create([
            'name' => 'Staff Member',
            'email' => 'staff@coffee.com',
            'password' => 'password123',
            'phone' => '+1234567892',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $staffRole = Role::where('name', 'staff')->first();
        if ($staffRole) {
            $staff->roles()->attach($staffRole);
        }

        // 4. Create Inventory Manager User
        $inventoryManager = AdminUser::create([
            'name' => 'Inventory Manager',
            'email' => 'inventory@coffee.com',
            'password' => 'password123',
            'phone' => '+1234567893',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $inventoryManagerRole = Role::where('name', 'inventory_manager')->first();
        if ($inventoryManagerRole) {
            $inventoryManager->roles()->attach($inventoryManagerRole);
        }

        // 5. Create Customer Service User
        $customerService = AdminUser::create([
            'name' => 'Customer Service Rep',
            'email' => 'support@coffee.com',
            'password' => 'password123',
            'phone' => '+1234567894',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $customerServiceRole = Role::where('name', 'customer_service')->first();
        if ($customerServiceRole) {
            $customerService->roles()->attach($customerServiceRole);
        }

        // 6. Create Test User with Multiple Roles (Manager + Inventory Manager)
        $multiRoleUser = AdminUser::create([
            'name' => 'John Doe',
            'email' => 'john@coffee.com',
            'password' => 'password123',
            'phone' => '+1234567895',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign multiple roles
        $managerRole = Role::where('name', 'manager')->first();
        $inventoryManagerRole = Role::where('name', 'inventory_manager')->first();
        if ($managerRole && $inventoryManagerRole) {
            $multiRoleUser->roles()->attach([$managerRole->id, $inventoryManagerRole->id]);
        }

        // 7. Create Inactive User (for testing account status)
        $inactiveUser = AdminUser::create([
            'name' => 'Inactive User',
            'email' => 'inactive@coffee.com',
            'password' => 'password123',
            'phone' => '+1234567896',
            'is_active' => false, // Inactive user
            'email_verified_at' => now(),
        ]);

        $staffRole = Role::where('name', 'staff')->first();
        if ($staffRole) {
            $inactiveUser->roles()->attach($staffRole);
        }

        $this->command->info('Admin Users created successfully!');
        $this->command->info('=== Login Credentials ===');
        $this->command->info('Super Admin: admin@coffee.com / password123');
        $this->command->info('Manager: manager@coffee.com / password123');
        $this->command->info('Staff: staff@coffee.com / password123');
        $this->command->info('Inventory Manager: inventory@coffee.com / password123');
        $this->command->info('Customer Service: support@coffee.com / password123');
        $this->command->info('Multi-Role User: john@coffee.com / password123');
        $this->command->info('Inactive User: inactive@coffee.com / password123 (will not be able to login)');
    }
}
