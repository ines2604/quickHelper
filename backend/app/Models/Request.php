<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'budget',
        'category',
        'urgency',
        'deadline',
        'status',
        'location',
        'accepted_helper_id',
        'accepted_at',
        'completed_at'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function acceptedHelper()
    {
        return $this->belongsTo(User::class, 'accepted_helper_id');
    }

    public function helpOffers()
    {
        return $this->hasMany(Help_offer::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }
}
