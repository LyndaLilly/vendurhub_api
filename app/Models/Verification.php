<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
 protected $fillable = [
    'user_id',
    'type',
    'code',
    'expires_at',
    'verified_at',
];

public function user()
{
    return $this->belongsTo(User::class);
}

}