<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Ines Jaziri',
            'email' => 'inesjaziri2003@gmail.com',
            'phone' => '99921231',
            'password' => Hash::make('inesjaziri2003'),
        ]);

        User::create([
            'name' => 'Ranim',
            'email' => 'ranim@gmail.com',
            'phone' => '98765432',
            'password' => Hash::make('ranim'),
        ]);
    }
}
