<?php

namespace App\Http\Controllers\api;

use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    protected $modelMap = [
        'user' => \App\Models\User::class,
        // Add more mappings here if needed
        // 'post' => \App\Models\Post::class, etc.
    ];



    public function index(Request $request)
    {
        // Set default favoritable_type to 'user' if not provided
        $request->merge([
            'favoritable_type' => $request->input('favoritable_type', 'user')
        ]);

        // Validate the favoritable_type (e.g., 'user', 'post', etc.)
        $request->validate([
            'favoritable_type' => 'required|string|in:user,post', // Add more types as needed
        ]);

        // Convert favoritable_type to full model class name
        $favoritableType = $this->modelMap[$request->favoritable_type] ?? null;

        if (!$favoritableType) {
            return response()->json(['message' => 'Invalid favoritable type'], 400);
        }

        // Retrieve the authenticated user's favorites of the specified type
        $favorites = Favorite::where('user_id', Auth::id())
            ->where('favoritable_type', $favoritableType)
            ->with('favoritable') // Load related favoritable model
            ->get();

        return response()->json([
            'message' => 'Favorites retrieved successfully',
            'favorites' => $favorites
        ], 200);
    }





    /**
     * Add a favorite.
     */
    public function store(Request $request)
    {
        // Set default favoritable_type to 'user' if not provided
        $request->merge([
            'favoritable_type' => $request->input('favoritable_type', 'user')
        ]);

        // Validate the request
        $request->validate([
            'favoritable_id' => 'required|integer',
            'favoritable_type' => 'required|string|in:user,post', // List allowed types
        ]);

        // Convert favoritable_type to full model class name
        $favoritableType = $this->modelMap[$request->favoritable_type] ?? null;

        if (!$favoritableType) {
            return response()->json(['message' => 'Invalid favoritable type'], 400);
        }

        // Check if the favorite already exists to prevent duplicates
        $existingFavorite = Favorite::where('user_id', Auth::id())
            ->where('favoritable_id', $request->favoritable_id)
            ->where('favoritable_type', $favoritableType)
            ->first();

        if ($existingFavorite) {
            return response()->json(['message' => 'Already added to favorites'], 409);
        }

        // Create the favorite
        $favorite = Favorite::create([
            'user_id' => Auth::id(),
            'favoritable_id' => $request->favoritable_id,
            'favoritable_type' => $favoritableType,
        ]);

        return response()->json(['message' => 'Added to favorites', 'favorite' => $favorite], 201);
    }

    /**
     * Remove a favorite.
     */
    public function destroy(Request $request)
    {
        // Set default favoritable_type to 'user' if not provided
        $request->merge([
            'favoritable_type' => $request->input('favoritable_type', 'user')
        ]);

        // Validate the request
        $request->validate([
            'favoritable_id' => 'required|integer',
            'favoritable_type' => 'required|string|in:user,post', // List allowed types
        ]);

        // Convert favoritable_type to full model class name
        $favoritableType = $this->modelMap[$request->favoritable_type] ?? null;

        if (!$favoritableType) {
            return response()->json(['message' => 'Invalid favoritable type'], 400);
        }

        // Find the favorite to delete
        $favorite = Favorite::where('user_id', Auth::id())
            ->where('favoritable_id', $request->favoritable_id)
            ->where('favoritable_type', $favoritableType)
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Favorite not found'], 404);
        }

        // Delete the favorite
        $favorite->delete();

        return response()->json(['message' => 'Removed from favorites'], 200);
    }
}
