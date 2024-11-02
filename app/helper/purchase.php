<?php

use App\Models\Coupon;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PackagePurchase;
use Illuminate\Support\Facades\Auth;

function purchaseCreate($package_id, $request, $method = 'normal', $coupon_code = '' )
{
    $user = Auth::guard('web')->user();

    // Fetch Package details
    $package = Package::findOrFail($package_id);

    // Extract package details
    $amount = $package->sub_total_price;
    $currency = $package->currency;




    if ($coupon_code) {
        $amount = number_format(validateAndCalculateDiscount($amount, $coupon_code)['final_amount'], 2, '.', '');
    }




    // Create Package Purchase record
    $purchase = PackagePurchase::create([
        'package_id' => $package->id,
        'user_id' => $user->id,
        'price' => $amount,
        'currency' => $currency,
        'status' => 'pending',
        'purchase_date' => now(),
    ]);



    // Prepare Payment data
    $paymentData = [
        'name' => $package->package_name,
        'user_id' => $user->id,
        'amount' => $amount,
        'applicant_mobile' => $user->mobile_number, // Use employer's data
        'success_url' => $request->success_url,
        'cancel_url' => $request->cancel_url,
        'package_purchase_id' => $purchase->id, // Add the hiring_request_id if available
        'type' => "package",
    ];

    // Trigger the Stripe payment and get the redirect URL
    $paymentUrl = stripe($paymentData); // Ensure stripe function is defined elsewhere



    return [
        "amount" =>$amount,
        'purchase' => $purchase,
        'payment_url' => $paymentUrl, // Return the payment URL
    ];
}


function validateAndCalculateDiscount($amount, $code)
{
    // Find the coupon by code
    $coupon = Coupon::where('code', $code)->first();

    // Check if coupon exists, is active, and not expired
    if (!$coupon || !$coupon->is_active || $coupon->isExpired()) {
        return [
            'coupon_code' => null,
            'discount_type' => null,
            'discount_value' => 0,
            'original_amount' => number_format($amount, 2, '.', ''),
            'discount' => number_format(0, 2, '.', ''),
            'final_amount' => number_format($amount, 2, '.', ''),
            'expiry_date' => null,
            'is_active' => false,
            'type' => null,
            'message' => 'Invalid or expired coupon',
        ];
    }

    // Calculate the discount based on the amount
    $discount = $coupon->calculateDiscount($amount);
    $finalAmount = $amount - $discount;

    return [
        'coupon_code' => $coupon->code,
        'discount_type' => $coupon->discount_type,
        'discount_value' => number_format($coupon->discount_value, 2, '.', ''),
        'original_amount' => number_format($amount, 2, '.', ''),
        'discount' => number_format($discount, 2, '.', ''),
        'final_amount' => number_format($finalAmount, 2, '.', ''),
        'expiry_date' => $coupon->expiry_date,
        'is_active' => $coupon->is_active,
        'type' => $coupon->type,
        'message' => 'Coupon applied successfully',
    ];
}


