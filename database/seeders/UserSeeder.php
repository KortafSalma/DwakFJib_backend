<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin System',
            'email' => 'admin@dwakfjib.ma',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $users = [
            ['name' => 'Amine Benali', 'email' => 'amine.benali@email.ma'],
            ['name' => 'Salma El Ouafi', 'email' => 'salma.ouafi@email.ma'],
            ['name' => 'Hassan Mokhtari', 'email' => 'hassan.mokhtari@email.ma'],
            ['name' => 'Nadia Berrada', 'email' => 'nadia.berrada@email.ma'],
            ['name' => 'Omar Tazi', 'email' => 'omar.tazi@email.ma'],
            ['name' => 'Leila Benjelloun', 'email' => 'leila.benjelloun@email.ma'],
            ['name' => 'Youssef El Fassi', 'email' => 'youssef.fassi@email.ma'],
            ['name' => 'Khadija Amrani', 'email' => 'khadija.amrani@email.ma'],
        ];

        foreach ($users as $user) {
            User::factory()->create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_USER,
            ]);
        }

        User::factory(5)->create();
    }
}
