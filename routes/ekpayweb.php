<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ekpay\EkpayPaymentController;


Route::get('/payment/success', [EkpayPaymentController::class, 'handlePaymentSuccess']);


Route::get('/payment/success/confirm', [EkpayPaymentController::class,'sonodpaymentSuccess']);
