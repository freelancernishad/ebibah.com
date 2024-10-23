<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PackageService extends Model
{
    use HasFactory;



    protected $fillable = [
        'name',
        'slug',
        'indexno',
    ];


    /**
     * Boot the model to add global scope for sorting by indexno.
     */
    protected static function booted()
    {
        static::addGlobalScope('ordered', function (Builder $builder) {
            $builder->orderBy('indexno', 'asc'); // Sort by indexno ascending
        });
    }
}
