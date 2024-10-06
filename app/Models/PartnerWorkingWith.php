<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerWorkingWith extends Model
{
    use HasFactory;

    protected $table = 'partner_working_with';

    protected $fillable = [
        'user_id',
        'working_with',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
