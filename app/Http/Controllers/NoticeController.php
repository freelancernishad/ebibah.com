<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoticeController extends Controller
{
    /**
     * Create or update the single notice.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrUpdate(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
            'type' => 'nullable|in:general,top-bar', // Validate the type
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find the first notice or create a new one
        $notice = Notice::firstOrNew();

        // Update only the fields provided in the request
        // if ($request->has('title')) {
        //     $notice->title = $request->title;
        // }

        if ($request->has('description')) {
            $notice->description = $request->description;
        }
        // if ($request->has('start_date')) {
        //     $notice->start_date = $request->start_date;
        // }
        // if ($request->has('end_date')) {
        //     $notice->end_date = $request->end_date;
        // }
        // if ($request->has('is_active')) {
        //     $notice->is_active = $request->is_active;
        // }
        // if ($request->has('type')) {
        //     $notice->type = $request->type;
        // }
        $notice->title = 'Notice';
        $notice->start_date = "2023-10-01";
        $notice->end_date = "2023-10-20";
        $notice->is_active = true;
        $notice->type = "top-bar";
        $notice->save();

        // Return success response
        return response()->json([
            'status' => true,
            'message' => $notice->wasRecentlyCreated ? 'Notice created successfully' : 'Notice updated successfully',
            'data' => $notice,
        ], $notice->wasRecentlyCreated ? 201 : 200);
    }
    /**
     * Show the single notice.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        // Find the first notice
        $notice = Notice::select('description')->first();

        // If no notice exists, return error
        if (!$notice) {
            return response()->json([
                'status' => false,
                'message' => 'No notice found',
            ], 404);
        }

        // Return the notice
        return response()->json([
            'status' => true,
            'data' => $notice,
        ]);
    }
}
