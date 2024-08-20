<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'union',
        'trxId',
        'sonodId',
        'sonod_type',
        'amount',
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



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sonodId');
    }
}
