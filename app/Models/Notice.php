<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    // Define notice types
    const TYPE_GENERAL = 'general';
    const TYPE_TOP_BAR = 'top-bar';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'is_active',
        'type', // Add the type field
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Accessor for formatted start date.
     */
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date->format('d M Y');
    }

    /**
     * Mutator for title (to ensure it's always capitalized).
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = ucfirst($value);
    }

    /**
     * Scope a query to only include top-bar notices.
     */
    public function scopeTopBar($query)
    {
        return $query->where('type', self::TYPE_TOP_BAR);
    }

    /**
     * Scope a query to only include general notices.
     */
    public function scopeGeneral($query)
    {
        return $query->where('type', self::TYPE_GENERAL);
    }
}
