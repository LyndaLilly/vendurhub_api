<?php

// app/Models/ProfileLink.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfileLink extends Model
{
    use HasFactory;

    protected $fillable = ['profile_id', 'shareable_link'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
