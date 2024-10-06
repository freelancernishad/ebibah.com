<?php

use App\Models\Package;
use App\Models\Payment;
use App\Models\PackagePurchase;
use Illuminate\Support\Facades\Auth;

function purchaseCreate($package_id, $request, $method = 'normal')
{
    $user = Auth::guard('web')->user();

    // Fetch Package details
    $package = Package::findOrFail($package_id);

    // Extract package details
    $amount = $package->price;
    $currency = $package->currency;

    // Create Package Purchase record
    $purchase = PackagePurchase::create([
        'package_id' => $package->id,
        'user_id' => $user->id,
        'price' => $amount,
        'currency' => $currency,
        'status' => 'pending',
        'purchase_date' => now(),
    ]);

    // Generate Transaction ID
    $trxId = generateTrxId();

    // Prepare Payment data
    $paymentData = [
        'name' => $package->package_name,
        'userid' => $user->id,
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
        'purchase' => $purchase,
        'payment_url' => $paymentUrl, // Return the payment URL
    ];
}

