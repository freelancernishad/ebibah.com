<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;



    protected $fillable = [
        'package_name',
        'price',
        'discount_type',
        'discount',
        'sub_total_price',
        'currency',
        'duration',
        'profile_view',
    ];


    public function activeServices()
    {
        return $this->hasMany(PackageActiveService::class);
    }

}
