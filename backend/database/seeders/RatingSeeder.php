<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rating;
class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Rating::create([
            'from_user_id' => 1,
            'to_user_id' => 2,
            'rating' => 5,
            'comment' => 'Very helpful and fast!',
        ]);
    }
}
