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
            ]
        );
    }
}
