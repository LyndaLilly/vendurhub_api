<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'role',
        'verified',
        'profile_updated',
        'is_subscribed',
        'subscription_type',
        'subscription_expires_at',
        'deactivated_at',
        'last_password_change',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function verifications()
    {
        return $this->hasMany(Verification::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function deliveryLocations()
    {
        return $this->hasMany(DeliveryLocation::class);
    }

    public function profileLink()
    {
        return $this->hasOne(\App\Models\ProfileLink::class);
    }

    public function trial()
    {
        return $this->hasOne(Trial::class);
    }

    public function isActive()
    {
        return is_null($this->deactivated_at);
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'vendor_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->exists();
    }

}
