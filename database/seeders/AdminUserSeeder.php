<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Create admin user ──────────────────────────────
        // Change these credentials before going live!
        User::updateOrCreate(
            ['email' => 'admin@epaksi.com'],
            [
                'name'     => 'Administrator',
                'email'    => 'admin@epaksi.com',
                'password' => Hash::make('epaksi2024!'),
                'role'     => 'admin',
            ]
        );

        $this->command->info('✅ Admin user created:');
        $this->command->info('   Email    : admin@epaksi.com');
        $this->command->info('   Password : epaksi2024!');
        $this->command->warn('   ⚠️  Change this password after first login!');
    }
}