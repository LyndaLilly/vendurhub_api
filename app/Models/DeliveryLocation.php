<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'country',
        'state',
        'city',
        'delivery_price',
        'other_country',
        'note',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($location) {
            if ($location->other_country) {
                $location->country = null;
                $location->state   = null;
                $location->city    = null;

                // Allow 0 or keep what's set
                if (is_null($location->delivery_price)) {
                    $location->delivery_price = 0;
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
