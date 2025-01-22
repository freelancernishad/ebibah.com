<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory,Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_code',
        'two_factor_expires_at',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Required method from JWTSubject
    public function getJWTCustomClaims()
    {
        return [];
    }



     /**
     * Generate a 6-digit OTP and set its expiration time.
     */
    public function generateTwoFactorCode()
    {
        $this->timestamps = false; // Prevent updating the `updated_at` column
        $this->two_factor_code = $this->generateNumericOTP(6); // Generate a 6-digit numeric OTP
        $this->two_factor_expires_at = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes
        $this->save();
    }

    /**
     * Generate a numeric OTP of the specified length.
     *
     * @param int $length
     * @return string
     */
    protected function generateNumericOTP($length = 6)
    {
        $digits = '0123456789';
        $otp = '';

        for ($i = 0; $i < $length; $i++) {
            $otp .= $digits[rand(0, strlen($digits) - 1)];
        }

        return $otp;
    }

    /**
     * Reset the OTP and its expiration time.
     */
    public function resetTwoFactorCode()
    {
        $this->timestamps = false;
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }


}
