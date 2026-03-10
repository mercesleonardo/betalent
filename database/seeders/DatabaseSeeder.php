<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\{Gateway, Product, User};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@betalent.com', 'role' => UserRole::ADMIN],
            ['name' => 'Manager', 'email' => 'manager@betalent.com', 'role' => UserRole::MANAGER],
            ['name' => 'Finance', 'email' => 'finance@betalent.com', 'role' => UserRole::FINANCE],
            ['name' => 'User Normal', 'email' => 'user@betalent.com', 'role' => UserRole::USER],
        ];

        foreach ($users as $user) {
            User::factory()->create([
                'name'     => $user['name'],
                'email'    => $user['email'],
                'role'     => $user['role'],
                'password' => Hash::make('password'),
            ]);
        }

        Gateway::create([
            'name'      => 'GATEWAY_1',
            'is_active' => true,
            'priority'  => 1,
        ]);

        Gateway::create([
            'name'      => 'GATEWAY_2',
            'is_active' => true,
            'priority'  => 2,
        ]);

        Product::create(['name' => 'Mouse Gamer Razer DeathAdder V2 Pro', 'amount' => 29990]); // 299,90
        Product::create(['name' => 'Teclado Gamer Razer Huntsman Elite', 'amount' => 25990]);   // 259,90
        Product::create(['name' => 'Headset Gamer Razer Kraken Kitty', 'amount' => 45000]);   // 450,00

        Product::factory(5)->create();
    }
}
