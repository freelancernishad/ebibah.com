<?php

namespace App\Http\Controllers\api;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{

    public function update(Request $request, $id)
    {
        // Find and update the admin
    }

    public function delete($id)
    {
        // Find and delete the admin
    }

    // Add other functions as needed
}
