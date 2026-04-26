<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'request_id',
        'sender_id',
        'receiver_id',
        'content',
        'file_path',
        'file_name',
        'file_type',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
