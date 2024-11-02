<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'discount_type', 'discount_value', 'expiry_date', 'type', 'is_active'
    ];

    public function isExpired()
    {
        return Carbon::now()->gt(Carbon::parse($this->expiry_date));
    }

    public function calculateDiscount($amount)
    {
        if ($this->discount_type === 'fixed') {
            return min($this->discount_value, $amount);
        } elseif ($this->discount_type === 'percent') {
            return ($this->discount_value / 100) * $amount;
        }
        return 0;
    }
}
