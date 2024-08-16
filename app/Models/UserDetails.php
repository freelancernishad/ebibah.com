<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'ip_address',
        'mac_address',
    ];

    public function readArticle()
    {
        return $this->belongsTo(ReadArticle::class);
    }
}
