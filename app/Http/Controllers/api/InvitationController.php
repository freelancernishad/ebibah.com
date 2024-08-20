<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InvitationController extends Controller
{
    // Send an invitation
    public function sendInvitation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $sender_id = Auth::id();
        $receiver_id = $request->receiver_id;

        // Check if an invitation already exists between these two users
        $existingInvitation = Invitation::where('sender_id', $sender_id)
            ->where('receiver_id', $receiver_id)
            ->first();

        if ($existingInvitation) {
            return response()->json(['message' => 'Invitation already sent'], 400);
        }

        $invitation = Invitation::create([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'status' => 'sent',
        ]);

        return response()->json(['message' => 'Invitation sent successfully', 'invitation' => $invitation], 201);
    }

    // Accept an invitation
    public function acceptInvitation($id)
    {
        $invitation = Invitation::find($id);

        if (!$invitation || $invitation->receiver_id !== Auth::id()) {
            return response()->json(['message' => 'Invitation not found or you are not authorized'], 404);
        }

        $invitation->update([
            'status' => 'accepted',
        ]);

        return response()->json(['message' => 'Invitation accepted successfully', 'invitation' => $invitation], 200);
    }

    // Reject an invitation
    public function rejectInvitation($id)
    {
        $invitation = Invitation::find($id);

        if (!$invitation || $invitation->receiver_id !== Auth::id()) {
            return response()->json(['message' => 'Invitation not found or you are not authorized'], 404);
        }

        $invitation->update([
            'status' => 'rejected',
        ]);

        return response()->json(['message' => 'Invitation rejected successfully', 'invitation' => $invitation], 200);
    }

    // Get all invitations sent by the authenticated user
    public function sentInvitations()
    {
        $invitations = Invitation::where('sender_id', Auth::id())->get();
        return response()->json(['invitations' => $invitations], 200);
    }

    // Get all invitations received by the authenticated user
    public function receivedInvitations()
    {
        $invitations = Invitation::where('receiver_id', Auth::id())->get();
        return response()->json(['invitations' => $invitations], 200);
    }
}
