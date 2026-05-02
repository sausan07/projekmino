<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name'   => 'mingyu',
                'email'      => 'mingyu@gmail.com',
                'password'   => Hash::make('123456'),
            ],
            [
                'name'   => 'dk',
                'email'      => 'dk@gmail.com',
                'password'   => Hash::make('123456'),
            ],
        ]);
    }
}