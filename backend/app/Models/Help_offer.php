<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Help_offer extends Model
{
    protected $fillable = [
        'request_id',
        'helper_id',
        'message',
        'status',
        'proposed_budget',
        'responded_at',
        'file_path',
        'file_name',
        'file_type'
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'proposed_budget' => 'decimal:2',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function helper()
    {
        return $this->belongsTo(User::class, 'helper_id');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function hasFile()
    {
        return !is_null($this->file_path);
    }
}
