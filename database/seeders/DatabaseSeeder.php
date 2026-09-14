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
        User::updateOrCreate(
            ['email' => 'operator@klinik.uin.ac.id'],
            [
                'name' => 'Operator Klinik',
                'password' => Hash::make('123'),
                'role' => 'operator',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@klinik.uin.ac.id'],
            [
                'name' => 'Administrator Klinik',
                'password' => Hash::make('123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->call(FakultasProdiSeeder::class);
    }
}
