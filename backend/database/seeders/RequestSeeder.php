<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Request;

class RequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Request::create([
        'user_id' => 1,
        'title' => 'Fix my laptop',
        'description' => 'My laptop is not turning on',
        'budget' => 50,
        'category' => 'it_support',
        'urgency' => 'high',
        'status' => 'open',
        'location' => 'Tunis',
    ]);

        Request::create([
            'user_id' => 2,
            'title' => 'Help moving furniture',
            'description' => 'Need help moving sofa',
            'budget' => 30,
            'category' => 'housekeeping',
            'urgency' => 'medium',
            'status' => 'open',
            'location' => 'Ariana',
        ]);
    }
}
