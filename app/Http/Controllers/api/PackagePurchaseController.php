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
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
            'coupon_code' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Process the purchase and get the package purchase details
        $purchaseResponse = purchaseCreate($request->package_id,$request,'normal',$request->coupon_code);


        $paymentUrl = $purchaseResponse['payment_url'];


        return response()->json([
            'message' => 'Purchase processed successfully',
            'purchase' => $purchaseResponse['purchase'],
            'payment_url' => $paymentUrl, // Include the payment URL in the response
        ], 201);
    }



    public function ipnresponse(Request $request)
{

    // Validate IPN request
    $validator = Validator::make($request->all(), [
        'trxId' => 'required|string|exists:payments,checkout_session_id',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Retrieve payment by transaction ID
    $payment = Payment::where('checkout_session_id', $request->input('trxId'))->first();

    if (!$payment) {
        return response()->json(['error' => 'Payment not found'], 404);
    }



        // Update PackagePurchase record status
        $packagePurchase = $payment->packagePurchase;
        // Update User's active_package_id
        $user = $payment->user;
        $package = $packagePurchase->package;

        $user->update([
            'active_package_id' => $package->id,
        ]);

        return response()->json([
            'message' => 'Payment verified',
            'payment' => $payment,
        ], 200);

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
