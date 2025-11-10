<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use Illuminate\Support\Str;

class DefaultWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if any warehouse exists
        if (Warehouse::count() > 0) {
            $this->command->info('Warehouses already exist. Skipping default warehouse creation.');
            return;
        }

        $this->command->info('Creating default warehouse...');

        // Create default main warehouse
        // $mainWarehouse = Warehouse::create([
        //     'id' => (string) Str::uuid(),
        //     'name' => 'Main Warehouse',
        //     'code' => 'WH-MAIN',
        //     'email' => 'warehouse@example.com',
        //     'phone' => '+1234567890',
        //     'address' => '123 Main Street',
        //     'city' => 'Lahore',
        //     'state' => 'Punjab',
        //     'country' => 'Pakistan',
        //     'postal_code' => '54000',
        //     'is_active' => true,
        //     'is_default' => true,
        //     'priority' => 100,
        //     'notes' => 'Default main warehouse created by system seeder',
        // ]);

        // $this->command->info('✓ Main Warehouse created: ' . $mainWarehouse->name);

        // Optionally create additional warehouses
        $additionalWarehouses = [
            [
                'name' => 'Deafult Warehouse',
                'code' => 'Deafult Warehouse',
                'email' => 'deafult@shop.com',
                'phone' => '+1234567891',
                'address' => '456 deafult Road',
                'city' => 'Deafult',
                'state' => 'Deafult',
                'country' => 'Deafult',
                'postal_code' => '75000',
                'is_active' => true,
                'is_default' => true,
                'priority' => 100,
                'notes' => 'Deafult warehouse for additional storage',
            ],
        ];

        // Ask if user wants to create additional warehouses
        if ($this->command->confirm('Do you want to create additional sample warehouses?', false)) {
            foreach ($additionalWarehouses as $warehouseData) {
                $warehouse = Warehouse::create(array_merge(
                    ['id' => (string) Str::uuid()],
                    $warehouseData
                ));
                $this->command->info('✓ Additional Warehouse created: ' . $warehouse->name);
            }
        }

        $this->command->info('');
        $this->command->info('✓ Default warehouse seeding completed successfully!');
        $this->command->info('Total warehouses created: ' . Warehouse::count());
    }
}
