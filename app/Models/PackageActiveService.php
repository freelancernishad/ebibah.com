<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageActiveService extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'service_id',
        'status',
    ];


    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function service()
    {
        return $this->belongsTo(PackageService::class, 'service_id');
    }

}
