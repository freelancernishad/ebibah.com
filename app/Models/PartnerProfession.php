<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerProfession extends Model
{
    use HasFactory;

    protected $table = 'partner_professions';

    protected $fillable = [
        'user_id',
        'profession',
    ];
    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
