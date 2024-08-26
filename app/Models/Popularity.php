<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Popularity extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'views', 'likes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
