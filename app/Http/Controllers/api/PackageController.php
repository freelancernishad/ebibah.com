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
        $packages = Package::with('activeServices.service')->get();
        return response()->json($packages, 200);
    }

    // Fetch a specific package by ID
    public function show($id)
    {
        $package = Package::with('activeServices.service')->find($id);

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
        $package = Package::find($packageId);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        $packageServices = $package->activeServices()->with('service')->get();

        if ($packageServices->isEmpty()) {
            return response()->json(['message' => 'No services found for this package'], 404);
        }

        return response()->json($packageServices, 200);
    }

// Create a new package service (without associating it with a package)
public function createPackageService(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:package_services,slug',
    ]);

    // Create a new service
    $packageService = PackageService::create($validated);

    return response()->json($packageService, 201);
}


// Update an existing package service
public function updatePackageService(Request $request, $id)
{
    $packageService = PackageService::find($id);

    if (!$packageService) {
        return response()->json(['message' => 'Package service not found'], 404);
    }

    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'slug' => 'sometimes|string|max:255|unique:package_services,slug,' . $id,
    ]);

    // Update the service with the validated data
    $packageService->update($validated);

    return response()->json($packageService, 200);
}

// Delete an existing package service
public function deletePackageService($id)
{
    $packageService = PackageService::find($id);

    if (!$packageService) {
        return response()->json(['message' => 'Package service not found'], 404);
    }

    $packageService->delete();

    return response()->json(['message' => 'Package service deleted successfully'], 200);
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
            $packageActiveService->update(['status' => 'inactive']);
            return response()->json($packageActiveService, 200);
        }

        return response()->json(['message' => 'Service not found in package'], 404);
    }

    // Add multiple services to a package
    public function addMultipleServicesToPackage(Request $request, $packageId)
    {
        $validated = $request->validate([
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:package_services,id',
        ]);

        $activeServices = [];

        foreach ($validated['services'] as $service) {
            $activeService = PackageActiveService::updateOrCreate(
                [
                    'package_id' => $packageId,
                    'service_id' => $service['service_id'],
                ],
                ['status' => 'active']
            );

            $activeServices[] = $activeService;
        }

        return response()->json($activeServices, 201);
    }


   // Deactivate multiple services in a package
    public function deactivateMultipleServices(Request $request, $packageId)
    {
        $validated = $request->validate([
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:package_services,id',
        ]);

        $inactiveServices = [];

        foreach ($validated['services'] as $service) {
            $packageActiveService = PackageActiveService::where([
                'package_id' => $packageId,
                'service_id' => $service['service_id'],
            ])->first();

            if ($packageActiveService) {
                $packageActiveService->update(['status' => 'deactive']);
                $inactiveServices[] = $packageActiveService;
            }
        }

        return response()->json($inactiveServices, 200);
    }


    public function updateServicesStatus(Request $request, $packageId)
    {
        $validated = $request->validate([
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:package_services,id',
            'services.*.status' => 'required|in:active,deactive',
        ]);

        // Delete all existing services for the package
        PackageActiveService::where('package_id', $packageId)->delete();

        // Add new services with their statuses
        $servicesStatus = [];

        foreach ($validated['services'] as $service) {
            $packageActiveService = PackageActiveService::create([
                'package_id' => $packageId,
                'service_id' => $service['service_id'],
                'status' => $service['status']
            ]);

            $servicesStatus[] = $packageActiveService;
        }

        return response()->json($servicesStatus, 201);
    }



}
