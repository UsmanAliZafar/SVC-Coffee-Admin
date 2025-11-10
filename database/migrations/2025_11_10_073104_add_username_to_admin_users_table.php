<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add username column as nullable first (without unique constraint)
        Schema::table('admin_users', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_users', 'username')) {
                $table->string('username')->nullable()->after('email');
            }

            if (!Schema::hasColumn('admin_users', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('last_login_ip');
            }

            if (!Schema::hasColumn('admin_users', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });

        // Step 2: Populate usernames for ALL existing users (including empty ones)
        $this->populateUsernames();

        // Step 3: Now add unique constraint after all users have unique usernames
        Schema::table('admin_users', function (Blueprint $table) {
            // Make username NOT NULL and UNIQUE
            $table->string('username')->unique()->nullable(false)->change();
        });
    }

    /**
     * Populate usernames for existing admin users
     */
    private function populateUsernames(): void
    {
        // Get ALL users (including those with null or empty username)
        $users = DB::table('admin_users')->get();

        foreach ($users as $user) {
            // Skip if username already has a valid value
            if (!empty($user->username) && $user->username !== '') {
                continue;
            }

            // Generate username from email (part before @)
            $emailUsername = explode('@', $user->email)[0];

            // Clean username (remove special characters, keep only alphanumeric and underscore)
            $emailUsername = preg_replace('/[^a-zA-Z0-9_]/', '_', $emailUsername);

            // Make it unique if needed
            $username = $emailUsername;
            $counter = 1;

            // Check for duplicates and increment counter
            while (DB::table('admin_users')
                     ->where('username', $username)
                     ->where('id', '!=', $user->id)
                     ->exists()) {
                $username = $emailUsername . '_' . $counter;
                $counter++;
            }

            // Update the user with new username
            DB::table('admin_users')
                ->where('id', $user->id)
                ->update([
                    'username' => $username,
                    'updated_at' => now()
                ]);

            echo "✓ Updated user ID {$user->id} ({$user->email}) → username: {$username}\n";
        }

        echo "\n✓ All usernames populated successfully!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            if (Schema::hasColumn('admin_users', 'username')) {
                $table->dropUnique(['username']); // Drop unique constraint first
                $table->dropColumn('username');
            }

            if (Schema::hasColumn('admin_users', 'created_by')) {
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('admin_users', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }
};
