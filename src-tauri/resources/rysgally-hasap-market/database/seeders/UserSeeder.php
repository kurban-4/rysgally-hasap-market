<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\License;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // First, activate the license to bypass permission checks
        if (Schema::hasTable('licenses')) {
            License::updateOrCreate(
                ['key' => 'RYSGALLY-HASAP-BUILD'],
                [
                    'is_activated' => true,
                    'activated_at' => now()
                ]
            );
        }

        // Create admin user
        $admin = User::firstOrCreate([
            'username' => 'admin',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create sample users for other roles
        $salesman = User::firstOrCreate([
            'username' => 'salesman',
        ], [
            'name' => 'Salesman User',
            'password' => Hash::make('salesman123'),
            'role' => 'salesman',
        ]);

        $storage = User::firstOrCreate([
            'username' => 'storage',
        ], [
            'name' => 'Storage User',
            'password' => Hash::make('storage123'),
            'role' => 'storage',
        ]);

        $wholesale = User::firstOrCreate([
            'username' => 'wholesale',
        ], [
            'name' => 'Wholesale User',
            'password' => Hash::make('wholesale123'),
            'role' => 'wholesale',
        ]);
    }
}
