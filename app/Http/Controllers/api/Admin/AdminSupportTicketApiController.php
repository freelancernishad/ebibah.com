<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminSupportTicketApiController extends Controller
{
    // Get all support tickets for admin
    public function index()
    {
        $tickets = SupportTicket::with('user')->latest()->get();
        return response()->json($tickets);
    }

    // View a specific support ticket
    public function show($id)
    {
        $ticket = SupportTicket::with(['user'])->findOrFail($id);
        return response()->json($ticket);
    }

    public function reply(Request $request, $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'reply' => 'required|string',
            'status' => 'required|string|in:open,closed,pending,replay', // Define allowed statuses
            'reply_id' => 'nullable|exists:replies,id', // Check if the parent reply exists
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the support ticket by ID
        $ticket = SupportTicket::findOrFail($id);

        // Update ticket status
        $ticket->status = $request->status;

        // Create the reply
        $replyData = [
            'reply' => $request->reply,
            'reply_id' => $request->reply_id, // Set the parent reply ID if provided
        ];

        // Check if the logged-in user is an admin or a regular user
        if (auth()->guard('admin')->check()) {
            $replyData['admin_id'] = auth()->id();
        } else {
            $replyData['user_id'] = auth()->id();
        }

        // Create a new reply associated with the support ticket
        $ticket->replies()->create($replyData);

        // Save the ticket with the updated status
        $ticket->save();

        return response()->json(['message' => 'Reply sent and ticket status updated.'], 200);
    }



    // Update ticket status
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:open,closed,pending', // Define allowed statuses
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = SupportTicket::findOrFail($id);
        $ticket->status = $request->status;
        $ticket->save();

        return response()->json(['message' => 'Ticket status updated successfully.'], 200);
    }
}
