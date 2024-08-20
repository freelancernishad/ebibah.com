<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageService extends Model
{
    use HasFactory;



    protected $fillable = [
        'package_id',
        'name',
        'slug',
        'status',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

}
