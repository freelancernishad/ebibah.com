<?php

namespace App\Http\Controllers\Gateway;

use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\User;
use App\Models\Package;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use App\Models\PackagePurchase;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // Create a Payment Intent
    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::guard('web')->user();
        $package = Package::findOrFail($request->input('package_id'));

        // Create Payment Intent
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $package->price * 100, // Convert amount to cents
                'currency' => $package->currency,
                'metadata' => [
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                ],
            ]);




             return  purchaseCreate($request->package_id,'stripe');
             
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Handle Stripe Webhook
    public function handleWebhook(Request $request)
    {
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $this->updatePaymentStatus($paymentIntent, 'completed');
        } elseif ($event->type === 'payment_intent.payment_failed') {
            $paymentIntent = $event->data->object;
            $this->updatePaymentStatus($paymentIntent, 'failed');
        }

        return response()->json(['status' => 'success'], 200);
    }

    private function updatePaymentStatus($paymentIntent, $status)
    {
        $payment = Payment::where('trxId', $paymentIntent->id)->first();
        if ($payment) {
            $payment->update(['status' => $status]);

            $packagePurchase = $payment->packagePurchase;
            $packagePurchase->update(['status' => $status]);

            if ($status === 'completed') {
                $user = $payment->user;
                $user->update([
                    'active_package_id' => $packagePurchase->package_id,
                ]);
            }
        }
    }
}
