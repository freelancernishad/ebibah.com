<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagePurchase extends Model
{
    use HasFactory;



    protected $fillable = [
        'user_id',
        'package_id',
        'price',
        'currency',
        'payment_status',
        'transaction_id',
    ];

    // Define relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Define the one-to-many relationship to the Payment model
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

}
