<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullname',
        'whatsapp',
        'email',
        'address',
        'mobile_number',
        'country',
        'state',
        'city',
        'quantity',
        'delivery_price',
        'delivery_state',
        'delivery_city',
        'product_price',
        'total_price',
        'payment_type',
        'payment_proof',
        'image_choice',
        'status',
        'product_id',
        'vendor_id',
    ];

    protected $appends = ['payment_proof_url'];

    public function getPaymentProofUrlAttribute()
    {
        return $this->payment_proof 
            ? asset('storage/' . $this->payment_proof)
            : null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
