<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trial extends Model
{
    protected $fillable = ['user_id', 'started_at', 'ended_at', 'active'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'active'     => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
