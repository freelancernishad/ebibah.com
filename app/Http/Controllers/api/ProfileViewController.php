<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfileView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileViewController extends Controller
{
    // Store a new profile view
    public function store(Request $request)
    {
        $viewer_id = Auth::id();
        $viewed_id = $request->viewed_id;

        // Check if the user is viewing their own profile
        if ($viewer_id == $viewed_id) {
            return response()->json(['message' => 'You cannot view your own profile'], 400);
        }

        // Check if the profile view already exists
        $existingView = ProfileView::where('viewer_id', $viewer_id)
            ->where('viewed_id', $viewed_id)
            ->first();

        if ($existingView) {
            // Update the timestamp of the existing view
            $existingView->touch();
            return response()->json(['message' => 'Profile view updated'], 200);
        }

        $profileView = ProfileView::create([
            'viewer_id' => $viewer_id,
            'viewed_id' => $viewed_id,
        ]);

        return response()->json(['message' => 'Profile viewed successfully', 'profileView' => $profileView], 201);
    }

    // Get all profiles viewed by the authenticated user
    public function profilesViewed()
    {
        $views = ProfileView::where('viewer_id', Auth::id())->with('viewed')->get();
        return response()->json(['views' => $views], 200);
    }

    // Get all users who have viewed the authenticated user's profile
    public function whoViewedMyProfile()
    {
        $views = ProfileView::where('viewed_id', Auth::id())->with('viewer')->get();
        return response()->json(['views' => $views], 200);
    }
}
