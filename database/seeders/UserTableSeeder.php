<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's clear the users table first
        User::truncate();

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@test.com',
            'password' => Hash::make('aspire@123')
        ]);
    }
}
