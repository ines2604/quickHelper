<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar_url',
        'rating_avg',
        'rating_count',
        'password',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function acceptedRequests()
    {
        return $this->hasMany(Request::class, 'accepted_helper_id');
    }

    public function helpOffers()
    {
        return $this->hasMany(Help_offer::class, 'helper_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function ratingsGiven()
    {
        return $this->hasMany(Rating::class, 'from_user_id');
    }

    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'to_user_id');
    }
}
