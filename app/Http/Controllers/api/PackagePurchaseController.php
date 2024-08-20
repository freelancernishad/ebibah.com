<?php

namespace App\Http\Controllers\Api;

use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\PackagePurchase;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PackagePurchaseController extends Controller
{
    /**
     * Create a new purchase and payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchase(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::guard('web')->user();

        // Fetch Package details
        $package = Package::findOrFail($request->input('package_id'));

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
            'method' => 'method',
            'payment_type' => 'payment_type',
            'balance' => 0,
        ]);



        return response()->json([
            'message' => 'Purchase processed successfully',
            'purchase' => $purchase,
            'payment' => $payment,
        ], 201);
    }



    public function ipnresponse(Request $request)
{
    // Validate IPN request
    $validator = Validator::make($request->all(), [
        'trxId' => 'required|string|exists:payments,trxId',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Retrieve payment by transaction ID
    $payment = Payment::where('trxId', $request->input('trxId'))->first();

    if (!$payment) {
        return response()->json(['error' => 'Payment not found'], 404);
    }

    // Verify payment status
    $status = $request->input('status');

    if ($status === 'paid') {
        // Update Payment record status
        $payment->update([
            'status' => 'completed', // Assuming 'completed' is the status for successful payments
        ]);

        // Update PackagePurchase record status
        $packagePurchase = $payment->packagePurchase;
        $packagePurchase->update([
            'payment_status' => 'completed', // Update to 'completed' or appropriate status
        ]);

        // Update User's active_package_id
        $user = $payment->user;
        $package = $packagePurchase->package;

        $user->update([
            'active_package_id' => $package->id,
        ]);

        return response()->json([
            'message' => 'Payment verified and updated successfully',
            'payment' => $payment,
            'packagePurchase' => $packagePurchase,
            'user' => $user,
        ], 200);
    } else {
        // Handle non-paid statuses
        $payment->update([
            'status' => 'failed', // Assuming 'failed' is the status for unsuccessful payments
        ]);

        return response()->json([
            'message' => 'Payment verification failed',
            'payment' => $payment,
        ], 400);
    }
}









    /**
     * List all purchases by authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userPurchases()
    {
        $user = Auth::user();
        $purchases = PackagePurchase::where('user_id', $user->id)->with('payments')->get();

        return response()->json($purchases, 200);
    }

    /**
     * List all purchases for admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allPurchases()
    {
        // Assuming admin check is handled by middleware
        $purchases = PackagePurchase::with('payments')->get();

        return response()->json($purchases, 200);
    }




}
