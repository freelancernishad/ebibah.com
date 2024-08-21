<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Gateway\StripeController;

Route::post('/gateway/stripe/payment-intent', [StripeController::class, 'createPaymentIntent']);
Route::post('/gateway/stripe/webhook', [StripeController::class, 'handleWebhook']);
