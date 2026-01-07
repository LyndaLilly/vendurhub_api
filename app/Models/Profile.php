<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'profile_image',
    'business_name',
    'business_description',
    'business_logo',
    'date_of_establishment',
    'country',
    'state',
    'city',
    'business_location',
    'contact_number_whatsapp',
    'whatsapp_link',
    'business_account_number',
    'business_account_name',
    'business_bank_name',
    'signature',
    'last_editable_update',
];


    public function shareableLink()
    {
        return $this->hasOne(ProfileLink::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profileLink()
    {
        return $this->hasOne(ProfileLink::class); // uses profile_id by default
    }

}
