<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_purchase_id',
        'union',
        'trxId',
        'checkout_session_id',
        'user_id',
        'type',
        'amount',
        'currency',
        'applicant_mobile',
        'status',
        'date',
        'month',
        'year',
        'paymentUrl',
        'ipnResponse',
        'method',
        'payment_type',
        'balance',

    ];
    protected $hidden = [
        'ipnResponse',
        'paymentUrl',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }


        // Define the inverse relationship
        public function packagePurchase()
        {
            return $this->belongsTo(PackagePurchase::class);
        }

}
