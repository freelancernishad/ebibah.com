<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminUserImageController extends Controller
{
    /**
     * Display a listing of approved user images.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $images = UserImage::paginate(15);
        return response()->json($images);
    }

    /**
     * Approve a user image.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function approve(int $id): JsonResponse
    {
        $image = UserImage::findOrFail($id);
        $image->status = UserImage::STATUS_APPROVED; // Change to approved status
        $image->save();

        return response()->json([
            'message' => 'Image approved successfully.',
            'image' => $image,
        ]);
    }

    /**
     * Reject a user image.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function reject(int $id): JsonResponse
    {
        $image = UserImage::findOrFail($id);
        $image->status = UserImage::STATUS_REJECTED; // Change to rejected status
        $image->save();

        return response()->json([
            'message' => 'Image rejected successfully.',
            'image' => $image,
        ]);
    }




        /**
     * Display a listing of approved user images.
     *
     * @return JsonResponse
     */
    public function approved(Request $request): JsonResponse
    {
        $images = UserImage::where('status', UserImage::STATUS_APPROVED)->paginate(15);
        return response()->json($images);
    }

    /**
     * Display a listing of rejected user images.
     *
     * @return JsonResponse
     */
    public function rejected(Request $request): JsonResponse
    {
        $images = UserImage::where('status', UserImage::STATUS_REJECTED)->paginate(15);
        return response()->json($images);
    }

    /**
     * Display a listing of pending user images.
     *
     * @return JsonResponse
     */
    public function pending(Request $request): JsonResponse
    {
        $images = UserImage::where('status', UserImage::STATUS_PENDING)->paginate(15);
        return response()->json($images);
    }



}
