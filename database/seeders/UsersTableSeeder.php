<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       User::create([
        'name' => 'admin',
        'contact' => '08560216298',
        'address' => 'Depok, Jawa Barat',
        'role' => 'admin',
        'email' => 'admin@admin.com',
        'password' => Hash::make('qwerty123'),
       ]);
    }
}
