<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Help_offer;

class HelpOfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Help_offer::create([
            'request_id' => 1,
            'helper_id' => 2,
            'message' => 'I can fix your laptop in 1 hour',
            'status' => 'pending',
            'proposed_budget' => 45,
        ]);
    }
}
