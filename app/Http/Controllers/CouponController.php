<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderBy('id','desc')->get();
        return response()->json($coupons);
    }

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:coupons|max:255',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0',
            'expiry_date' => 'required|date',
            'type' => 'required|in:profile,package',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }


        $coupon = Coupon::create($request->all());
        return response()->json($coupon, 201);
    }

    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);
        return response()->json($coupon);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);


        $validator = Validator::make($request->all(), [
            'code' => 'required|max:255|unique:coupons,code,' . $coupon->id,
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0',
            'expiry_date' => 'required|date',
            'type' => 'required|in:profile,package',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }


        $coupon->update($request->all());
        return response()->json($coupon);
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);
    }

    public function calculateDiscount(Request $request, $code)
    {
         $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->is_active || $coupon->isExpired()) {
            return response()->json(['error' => 'Invalid or expired coupon'], 400);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }


        $discount = $coupon->calculateDiscount($request->amount);
        $finalAmount = $request->amount - $discount;

        return response()->json([
            'original_amount' => $request->amount,
            'discount' => $discount,
            'final_amount' => $finalAmount,
        ]);
    }


    public function validateCoupon(Request $request, $code)
    {

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
 
        $response = validateAndCalculateDiscount($request->amount,$code);
        return response()->json($response);
    }




}
