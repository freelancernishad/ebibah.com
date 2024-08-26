<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\PackageActiveService;
use Illuminate\Http\Request;

class PackageController extends Controller
{
// Fetch all packages with allowed services
public function index()
{
    $packages = Package::with('activeServices.service')->get();

    // Fetch all package services for reference
    $allPackageServices = PackageService::all()->keyBy('id');

    // Transform the packages to include only allowed_services array
    $packagesWithAllowedServices = $packages->map(function ($package) use ($allPackageServices) {
        $activeServices = $package->activeServices->keyBy('service_id');

        $allowedServices = $activeServices->map(function ($activeService) {
            return [
                'name' => $activeService->service->name,
                'status' => $activeService->status
            ];
        })->values();

        // If no active services, use all package services with 'deactive' status
        if ($allowedServices->isEmpty()) {
            $allowedServices = $allPackageServices->map(function ($service) {
                return [
                    'name' => $service->name,
                    'status' => 'deactive'
                ];
            })->values();
        }

        return [
            'id' => $package->id,
            'package_name' => $package->package_name,
            'price' => $package->price,
            'discount_type' => $package->discount_type,
            'discount' => $package->discount,
            'sub_total_price' => $package->sub_total_price,
            'currency' => $package->currency,
            'duration' => $package->duration,
            'created_at' => $package->created_at,
            'updated_at' => $package->updated_at,
            'allowed_services' => $allowedServices
        ];
    });

    return response()->json($packagesWithAllowedServices, 200);
}


// Fetch a specific package by ID
public function show($id)
{
    // Fetch the package with active services
    $package = Package::with('activeServices.service')->find($id);

    if (!$package) {
        return response()->json(['message' => 'Package not found'], 404);
    }

    // Fetch all package services for reference
    $allPackageServices = PackageService::all()->keyBy('id');

    // Map active services
    $activeServices = $package->activeServices->keyBy('service_id')->map(function ($activeService) {
        return [
            'name' => $activeService->service->name,
            'status' => $activeService->status
        ];
    })->values();

    // If no active services, use all package services with 'deactive' status
    if ($activeServices->isEmpty()) {
        $activeServices = $allPackageServices->map(function ($service) {
            return [
                'name' => $service->name,
                'status' => 'deactive'
            ];
        })->values();
    }

    // Prepare the response data
    $responseData = [
        'id' => $package->id,
        'package_name' => $package->package_name,
        'price' => $package->price,
        'discount_type' => $package->discount_type,
        'discount' => $package->discount,
        'sub_total_price' => $package->sub_total_price,
        'currency' => $package->currency,
        'duration' => $package->duration,
        'created_at' => $package->created_at,
        'updated_at' => $package->updated_at,
        'allowed_services' => $activeServices
    ];

    return response()->json($responseData, 200);
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
        // return Package::find($packageId);

        // Delete all existing services for the package
        PackageActiveService::where('package_id', $packageId)->delete();

        // Add new services with their statuses
        $servicesStatus = [];

        foreach ($validated['services'] as $service) {
            // Check if the service ID exists in the package_services table
            $packageService = PackageService::find($service['service_id']);
            if ($packageService) {
                $packageActiveService = PackageActiveService::create([
                    'package_id' => $packageId,
                    'service_id' => $service['service_id'],
                    'status' => $service['status']
                ]);

                $servicesStatus[] = $packageActiveService;
            } else {
                return response()->json(['message' => 'Service ID ' . $service['service_id'] . ' does not exist'], 400);
            }
        }

        return response()->json($servicesStatus, 201);
    }

    public function getAllPackageServices()
    {
        $packageServices = PackageService::all();
        return response()->json($packageServices, 200);
    }



}