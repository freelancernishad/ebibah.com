<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerReligion extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','religion'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
