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
        
        if (Schema::hasTable('licenses')) {
            License::updateOrCreate(
                ['key' => 'RYSGALLY-HASAP-BUILD'],
                [
                    'is_activated' => true,
                    'activated_at' => now()
                ]
            );
        }

        
        $admin = User::updateOrCreate([
            'username' => 'admin',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        
        $salesman = User::updateOrCreate([
            'username' => 'salesman',
        ], [
            'name' => 'Salesman User',
            'password' => Hash::make('salesman123'),
            'role' => 'salesman',
        ]);

        $storage = User::updateOrCreate([
            'username' => 'storage',
        ], [
            'name' => 'Storage User',
            'password' => Hash::make('storage123'),
            'role' => 'storage',
        ]);

        $wholesale = User::updateOrCreate([
            'username' => 'wholesale',
        ], [
            'name' => 'Wholesale User',
            'password' => Hash::make('wholesale123'),
            'role' => 'wholesale',
        ]);
    }
}
