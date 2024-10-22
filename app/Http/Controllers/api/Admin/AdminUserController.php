<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{



    private function getUsers(Request $request, string $status): JsonResponse
    {
        // Get the search query and per_page from the request
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15); // Default to 15 if not provided

        // Query the users based on status and apply search filters
        $users = User::query()
            ->where('status', $status) // Filter by status
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%")
                        ->orWhere('whatsapp', 'like', "%{$search}%")
                        ->orWhere('gender', 'like', "%{$search}%")
                        ->orWhere('date_of_birth', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc') // Order by creation date
            ->paginate($perPage); // Paginate results


               // Apply the toArrayProfile method to each user in the collection
            $users->getCollection()->transform(function ($user) {
                return $user->toArrayProfile();
            });



        return response()->json($users); // Return the paginated results as JSON
    }





    /**
     * Display a listing of users.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return $this->getUsers($request, 'active');
    }

    public function inactiveUsers(Request $request): JsonResponse
    {
        User::setApplyActiveScope(false); // Disable the active user scope
        return $this->getUsers($request, 'inactive');
    }

    public function bannedUsers(Request $request): JsonResponse
    {
        User::setApplyActiveScope(false); // Disable the active user scope
        return $this->getUsers($request, 'banned');
    }







    /**
     * Show the user details.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        User::setApplyActiveScope(false);
        $user = User::findOrFail($id)->toArrayProfile();
        return response()->json($user);
    }



    public function activate(int $id): JsonResponse
{
    User::setApplyActiveScope(false);
    $status = "active";

    // Validate the provided status
    $validStatuses = ['active', 'inactive', 'banned'];
    if (!in_array($status, $validStatuses)) {
        return response()->json(['error' => 'Invalid status provided.'], 400);
    }

    // Find the user or fail with a 404 response
    $user = User::findOrFail($id);

    // Update the user's status
    $user->status = $status;
    $user->save();

    return response()->json([
        'message' => "User status has been successfully changed to {$status}.",
        'user' => [
            'id' => $user->id,
            'status' => $user->status,
        ],
    ]);
}

public function deactivate(int $id): JsonResponse
{
    User::setApplyActiveScope(false);
    $status = "inactive";

    // Validate the provided status
    $validStatuses = ['active', 'inactive', 'banned'];
    if (!in_array($status, $validStatuses)) {
        return response()->json(['error' => 'Invalid status provided.'], 400);
    }

    // Find the user or fail with a 404 response
    $user = User::findOrFail($id);

    // Update the user's status
    $user->status = $status;
    $user->save();

    return response()->json([
        'message' => "User status has been successfully changed to {$status}.",
        'user' => [
            'id' => $user->id,
            'status' => $user->status,
        ],
    ]);
}





    /**
     * Ban or unban a user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function ban(int $id): JsonResponse
    {
        User::setApplyActiveScope(false);
        $status = "banned";
        // Validate the provided status
        $validStatuses = ['active', 'inactive', 'banned'];
        if (!in_array($status, $validStatuses)) {
            return response()->json(['error' => 'Invalid status provided.'], 400);
        }

        // Find the user or fail with a 404 response
        $user = User::findOrFail($id);

        // Update the user's status
        $user->status = $status;
        $user->save();

        return response()->json([
            'message' => "User status has been successfully changed to {$status}.",
            'user' => [
                'id' => $user->id,
                'status' => $user->status,
            ],
        ]);


    }


    public function destroy(int $id): JsonResponse
    {
        // Find the user or fail with a 404 response
        $user = User::findOrFail($id);

        // Delete the user
        $user->delete();

        return response()->json([
            'message' => 'User account has been permanently deleted.',
            'user_id' => $user->id,
        ]);
    }

}
