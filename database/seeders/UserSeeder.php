<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      DB::table('users')->insert([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'role' => 'admin',
    'mobile' => '0123456789',
    'account' => 0,
    'password' => Hash::make('12345678'),
    'is_approved' => true,
    'is_active' => true,
    'profile_photo' => 'hh',
    'id_photo' => 'hh',
    'address' => 'hh',
    'card_type' => 'visa',
    'card_number' => '1234567890123456',
    'expiry_date' => Date('2025-12-31'),
    'birthdate' => Date('1990-01-01'),
    'security_code' => '123',
    'email_verified_at' => now(),
    'created_at' => now(),
    'updated_at' => now(),
]);
    }
}
