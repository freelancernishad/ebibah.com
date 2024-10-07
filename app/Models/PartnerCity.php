<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerCity extends Model
{
    use HasFactory;

    protected $fillable = ['city'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
