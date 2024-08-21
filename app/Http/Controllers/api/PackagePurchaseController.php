<?php

namespace App\Http\Controllers\api;

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

       return  purchaseCreate($request->package_id);


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
