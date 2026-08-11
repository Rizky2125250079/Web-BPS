<?php

namespace Database\Seeders;
use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {

        User::factory()->create([
            'name' => 'Admin utama',
            'email' => 'admin@bps.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);
    }
}
