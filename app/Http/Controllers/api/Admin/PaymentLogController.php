<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentLogController extends Controller
{

    /**
     * Display a listing of all payment transactions with pagination, filtering, and search.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with('user', 'packagePurchase');

        // Global search across relevant fields
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('trxId', 'like', "%{$searchTerm}%")
                ->orWhere('amount', 'like', "%{$searchTerm}%")
                ->orWhere('currency', 'like', "%{$searchTerm}%")
                ->orWhere('applicant_mobile', 'like', "%{$searchTerm}%")
                ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('email', 'like', "%{$searchTerm}%");
                });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        // Filter by user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Paginate with per_page parameter, defaulting to 15 if not provided
        $perPage = $request->input('per_page', 15);
        $payments = $query->paginate($perPage);

        return response()->json([
            'message' => 'Payment transactions retrieved successfully.',
            'data' => $payments,
        ]);
    }


    /**
     * Display the details of a specific payment transaction by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // Retrieve the payment transaction by ID, including related user and package purchase details
        $payment = Payment::with('user', 'packagePurchase')->findOrFail($id);

        return response()->json([
            'message' => 'Payment transaction details retrieved successfully.',
            'data' => $payment,
        ]);
    }


}
