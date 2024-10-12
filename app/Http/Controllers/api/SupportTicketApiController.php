<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupportTicketApiController extends Controller
{
    // Get all support tickets for the authenticated user
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())->get();
        return response()->json($tickets, 200);
    }

    // Create a new support ticket
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Ticket created successfully.', 'ticket' => $ticket], 201);
    }

    // Show a specific support ticket
    public function show(SupportTicket $ticket)
    {
        // Ensure the ticket belongs to the authenticated user
        if ($ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        return response()->json($ticket, 200);
    }



  
}

