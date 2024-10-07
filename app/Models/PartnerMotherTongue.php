<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerMotherTongue extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','mother_tongue'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
