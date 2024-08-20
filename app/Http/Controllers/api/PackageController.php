<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\PackageActiveService;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    // Fetch all packages
    public function index()
    {
        $packages = Package::with(['services', 'activeServices'])->get();
        return response()->json($packages, 200);
    }

    // Fetch a specific package by ID
    public function show($id)
    {
        $package = Package::with(['services', 'activeServices'])->find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        return response()->json($package, 200);
    }

    // Create a new package
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount' => 'nullable|numeric',
            'sub_total_price' => 'required|numeric',
            'currency' => 'required|string|max:10',
            'duration' => 'required|integer',
        ]);

        $package = Package::create($validated);

        return response()->json($package, 201);
    }

    // Update a package
    public function update(Request $request, $id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        $validated = $request->validate([
            'package_name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount' => 'nullable|numeric',
            'sub_total_price' => 'sometimes|numeric',
            'currency' => 'sometimes|string|max:10',
            'duration' => 'sometimes|integer',
        ]);

        $package->update($validated);

        return response()->json($package, 200);
    }

    // Delete a package
    public function destroy($id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        $package->delete();

        return response()->json(['message' => 'Package deleted successfully'], 200);
    }

    // Fetch services related to a specific package
    public function getPackageServices($packageId)
    {
        $packageServices = PackageService::where('package_id', $packageId)->get();

        if ($packageServices->isEmpty()) {
            return response()->json(['message' => 'No services found for this package'], 404);
        }

        return response()->json($packageServices, 200);
    }

    // Add services to a package
    public function addServiceToPackage(Request $request, $packageId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:package_services,slug',
        ]);

        $validated['package_id'] = $packageId;

        $packageService = PackageService::create($validated);

        return response()->json($packageService, 201);
    }

    // Activate a service in a package
    public function activateService(Request $request, $packageId, $serviceId)
    {
        $packageActiveService = PackageActiveService::updateOrCreate(
            [
                'package_id' => $packageId,
                'service_id' => $serviceId,
            ],
            ['status' => 'active']
        );

        return response()->json($packageActiveService, 200);
    }

    // Deactivate a service in a package
    public function deactivateService(Request $request, $packageId, $serviceId)
    {
        $packageActiveService = PackageActiveService::where([
            'package_id' => $packageId,
            'service_id' => $serviceId,
        ])->first();

        if ($packageActiveService) {
            $packageActiveService->update(['status' => 'deactive']);
            return response()->json($packageActiveService, 200);
        }

        return response()->json(['message' => 'Service not found in package'], 404);
    }
}
