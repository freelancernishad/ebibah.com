<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'page', 'started_date', 'end_date', 'default_banner', 'company_name',
        'company_address', 'provider_name', 'provider_position', 'agreement_date',
        'status', 'url', 'banner', 'slug', 'banner_size'
    ];

    protected $dates = [
        'started_date', 'end_date', 'agreement_date'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($advertisement) {
            $advertisement->slug = Advertisement::generateUniqueSlug($advertisement->page);
        });
    }

    protected static function generateUniqueSlug($page)
    {
        $slug = Str::slug($page);
        $originalSlug = $slug;
        $count = 1;

        while (Advertisement::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
