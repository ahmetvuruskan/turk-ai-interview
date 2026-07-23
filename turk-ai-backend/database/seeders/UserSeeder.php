<?php

namespace Database\Seeders;

use App\Enum\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        User::create([
            'name' => 'Ahmet',
            'surname' => 'Admin',
            'email' => 'admin@localhost.com',
            'password' => bcrypt('123456789'),
            'role' => RoleEnum::ROLE_ADMIN
        ]);


    }
}
