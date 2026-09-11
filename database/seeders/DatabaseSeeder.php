<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'operator@klinik.uin.ac.id'],
            [
                'name' => 'Operator Klinik',
                'password' => Hash::make('OperatorKlinik2026!'),
                'role' => 'operator',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@klinik.uin.ac.id'],
            [
                'name' => 'Administrator Klinik',
                'password' => Hash::make('AdminKlinik2026!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
