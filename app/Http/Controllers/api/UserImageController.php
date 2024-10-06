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
        // Get the authenticated user from the JWT token
        $user = auth()->user();

        // Validate the request (no need for user_id validation)
        $validator = Validator::make($request->all(), [
            'image_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Handle the image upload
        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Store the file in the S3 disk
            $filePath = $file->storeAs('users/images/' . $user->id, $fileName, 's3');

            // Save image path to the database
            $userImage = UserImage::create([
                'user_id' => $user->id,
                'image_path' => generateCustomS3Url($filePath),
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
        // Load the user relationship (if it's not eager loaded by default)
        $userImage->load('user');

        // Extract relative path from the full URL
        $relativePath = parse_url($userImage->image_path, PHP_URL_PATH);

        // Check if the file exists using the relative path
        if (!Storage::disk('s3')->exists($relativePath)) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        // Return the full URL for S3 or cloud storage
        // $url = Storage::disk('s3')->url($relativePath);

        // Include the full URL in the response
        // $userImage->image_url = $url;

        return response()->json($userImage, 200);
    }







    public function deleteImage(Request $request)
    {
        // Get the authenticated user from the JWT token
        $user = auth()->user();

        // Validate request (no need for user_id validation as we get it from the token)
        $validator = Validator::make($request->all(), [
            'image_id' => 'required|exists:user_images,id', // Assuming `user_images` is the table name
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Find the image record for the authenticated user
        $userImage = UserImage::where('user_id', $user->id)
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
