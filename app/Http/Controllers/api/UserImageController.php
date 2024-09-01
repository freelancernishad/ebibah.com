<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\UserImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserImageController extends Controller
{
    /**
     * Store a newly created image in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Handle the image upload
        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('users/images/'.$request->user_id, $fileName, 'protected');

            // Save image path to the database
            $userImage = UserImage::create([
                'user_id' => $request->user_id,
                'image_path' => $filePath,
            ]);

            return response()->json([
                'message' => 'Image uploaded successfully',
                'image' => $userImage,
            ], 201);
        } else {
            return response()->json(['error' => 'No image file provided.'], 422);
        }
    }

    /**
     * Display the specified image.
     *
     * @param  \App\Models\UserImage  $userImage
     * @return \Illuminate\Http\Response
     */
    public function show(UserImage $userImage)
    {
        if (!Storage::exists($userImage->image_path)) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        return response()->file(storage_path('app/public/' . $userImage->image_path));
    }



    public function delete(Request $request)
{
    // Validate request
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'image_id' => 'required|exists:user_images,id', // Assuming `user_images` is the table name
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    // Find the image record
    $userImage = UserImage::where('user_id', $request->user_id)
                          ->where('id', $request->image_id)
                          ->first();

    if (!$userImage) {
        return response()->json(['error' => 'Image not found.'], 404);
    }

    // Delete the image file from storage
    if (Storage::disk('protected')->exists($userImage->image_path)) {
        Storage::disk('protected')->delete($userImage->image_path);
    }

    // Delete the image record from the database
    $userImage->delete();

    return response()->json(['message' => 'Image deleted successfully.'], 200);
}


}
