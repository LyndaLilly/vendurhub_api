<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan',
        'is_active',
        'starts_at',
        'ended_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'ended_at'   => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relation to the User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
