<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Message;
class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Message::create([
            'request_id' => 1,
            'sender_id' => 1,
            'receiver_id' => 2,
            'content' => 'Can you help me today?',
        ]);

        Message::create([
            'request_id' => 1,
            'sender_id' => 2,
            'receiver_id' => 1,
            'content' => 'Yes I am available!',
        ]);
    }
}
