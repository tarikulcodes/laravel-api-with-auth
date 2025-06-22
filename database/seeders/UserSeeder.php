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
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@test.com',
                'password' => 'password',
                'role' => 'Admin',
            ],
            [
                'name' => 'User',
                'email' => 'user@test.com',
                'password' => 'password',
                'role' => 'User',
            ],
        ];

        foreach ($users as $user) {
            $role = $user['role'];
            unset($user['role']);

            $existingUser = User::where('email', $user['email'])->first() ?? null;
            if (empty($existingUser)) {
                $newUser = User::create($user);
                $newUser->syncRoles([$role]);
            } else {
                $existingUser->update($user);
                $existingUser->syncRoles([$role]);
            }
        }
    }
}
