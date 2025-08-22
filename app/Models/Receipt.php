<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = [
        'vendor_id',
        'buyer_fullname',
        'payment_status',
        'delivery_status',
        'receipt_number', 
    ];

    public function items()
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
