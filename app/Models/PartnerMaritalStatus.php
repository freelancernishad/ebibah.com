<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerMaritalStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'marital_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
