<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Kelurahan',
            'email' => 'admin@kelurahan.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Security KTP',
            'email' => 'security@kelurahan.test',
            'password' => Hash::make('password'),
            'role' => 'security',
        ]);

        User::create([
            'name' => 'Petugas Data',
            'email' => 'data@kelurahan.test',
            'password' => Hash::make('password'),
            'role' => 'data',
        ]);
    }
}
