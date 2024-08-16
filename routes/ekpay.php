<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ekpay\EkpayPaymentController;


Route::post('/ipn',[EkpayPaymentController::class ,'ipn']);
Route::post('/re/call/ipn',[EkpayPaymentController::class ,'ReCallIpn']);
Route::post('/check/payments/ipn',[EkpayPaymentController::class ,'AkpayPaymentCheck']);
