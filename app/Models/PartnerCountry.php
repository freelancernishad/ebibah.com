<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerCountry extends Model
{
    use HasFactory;

    protected $fillable = ['country'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
