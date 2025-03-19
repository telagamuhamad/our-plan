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
        // create user
        DB::table('users')->insert([
            [
                'name' => 'Muhammad Telaga Nur Handi Nindita',
                'email' => 'telagamuhamad@gmail.com',
                'password' => Hash::make('passwordTelaga'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(), 
            ],
            [
                'name' => 'Tarizma Ardis Octaviani',
                'email' => 'ardisoctaviani01@gmail.com',
                'password' => Hash::make('passwordTarizma'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
