<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactView extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'viewed_profile_user_id',
    ];

    // Relationship with the user viewing the contact
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with the package used for viewing contacts
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Relationship with the profile being viewed
    public function viewedProfile()
    {
        return $this->belongsTo(User::class, 'viewed_profile_user_id');
    }
}
