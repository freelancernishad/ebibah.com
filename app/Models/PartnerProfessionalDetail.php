<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerProfessionalDetail extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','profession'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
