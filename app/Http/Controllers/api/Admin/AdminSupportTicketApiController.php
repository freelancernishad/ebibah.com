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

    // Reply to a support ticket
    public function reply(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reply' => 'required|string',
            'status' => 'required|string|in:open,closed,pending,replay', // Define allowed statuses
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = SupportTicket::findOrFail($id);

        // Update ticket status
        $ticket->status = $request->status;

        // Create a reply (you may need to implement a separate Reply model)
        // Assuming you have a replies table set up.
        $ticket->replies()->create([
            'reply' => $request->reply,
            'admin_id' => auth()->id(), // assuming you have an admin user logged in
        ]);

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
