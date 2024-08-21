<?php

use App\Models\Package;
use App\Models\Payment;
use App\Models\PackagePurchase;
use Illuminate\Support\Facades\Auth;

     function purchaseCreate($package_id,$method='normal')
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

        // Create a new Payment record
        $payment = Payment::create([
            'package_purchase_id' => $purchase->id,
            'trxId' => $trxId,
            'user_id' => $user->id,
            'type' => 'package',
            'amount' => $amount,
            'currency' => $currency,
            'applicant_mobile' => $user->mobile_number,
            'status' => 'pending',
            'date' => now()->toDateString(),
            'month' => now()->format('F'),
            'year' => now()->year,
            'paymentUrl' => 'url',
            'ipnResponse' => 'ipn',
            'method' => $method,
            'payment_type' => 'payment_type',
            'balance' => 0,
        ]);



        return response()->json([
            'message' => 'Purchase processed successfully',
            'purchase' => $purchase,
            'payment' => $payment,
        ], 201);
    }
