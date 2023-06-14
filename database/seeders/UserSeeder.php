<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Lurasyah',
            'username' => 'Lurasyah',
            'role' => 'SuperAdmin',
            'email' => 'lurasyahgroup@gmail.com',
            'otp_code' => 123456,
            'biodata' => 'I am a SuperAdmin',
            'provinsi' => 'Yogyakarta',
            'kota' => 'Bantul',
            'kecamatan' => 'Kretek',
            'desa' => 'Titihargo',
            'email_verified_at' => now(),
            'password' => bcrypt('1234567890'),
        ]);
    }
}
