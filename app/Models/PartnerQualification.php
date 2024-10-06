<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerQualification extends Model
{
    use HasFactory;

    protected $table = 'partner_qualifications';

    protected $fillable = [
        'user_id',
        'qualification',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
