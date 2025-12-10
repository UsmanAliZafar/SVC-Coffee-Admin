<?php
// app/Models/Newsletter.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Newsletter extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'email',
        'name',
        'is_subscribed',
        'email_verified',
        'verification_token',
        'email_verified_at',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'is_subscribed' => 'boolean',
        'email_verified' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    // Scopes
    public function scopeSubscribed($query)
    {
        return $query->where('is_subscribed', true);
    }

    public function scopeUnsubscribed($query)
    {
        return $query->where('is_subscribed', false);
    }

    public function scopeVerified($query)
    {
        return $query->where('email_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('email_verified', false);
    }

    // Methods
    public function subscribe(): void
    {
        $this->update([
            'is_subscribed' => true,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);
    }

    public function unsubscribe(): void
    {
        $this->update([
            'is_subscribed' => false,
            'unsubscribed_at' => now(),
        ]);
    }

    public function verify(): void
    {
        $this->update([
            'email_verified' => true,
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);
    }

    public function generateVerificationToken(): string
    {
        $token = Str::random(64);
        $this->update(['verification_token' => $token]);
        return $token;
    }

    public function isVerified(): bool
    {
        return $this->email_verified;
    }
}
