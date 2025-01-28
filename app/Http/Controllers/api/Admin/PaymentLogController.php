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
    public function index(Request $request)
    {
        $query = Payment::with('user', 'packagePurchase');

        // Global search
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('trxId', 'like', "%{$searchTerm}%")
                    ->orWhere('amount', 'like', "%{$searchTerm}%")
                    ->orWhere('currency', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('date')) {
            $query->whereDate('date', $request->input('date'));
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $payments = $query->paginate($perPage);

        // Handle PDF generation
        if ($request->pdf == 'true') {
            $data = $query->get(['trxId', 'amount', 'method', 'status', 'date']);
            $pdfData = $data->map(function ($payment) {
                return [
                    'trxId' => $payment->trxId,
                    'payment_for' => optional($payment->packagePurchase)->package_name ?? 'N/A',
                    'user_name' => optional($payment->user)->name ?? 'N/A',
                    'amount' => $payment->amount,
                    'method' => $payment->method,
                    'status' => ucfirst($payment->status),
                    'date' => $payment->date->format('Y-m-d'),
                ];
            });

            // Generate PDF
            $mpdf = new \Mpdf\Mpdf();
            $html = view('payments.report', compact('pdfData'))->render();
            $mpdf->WriteHTML($html);
            return $mpdf->Output('payments_report.pdf', 'I');
        }

        // JSON response
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
