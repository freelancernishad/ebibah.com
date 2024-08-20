<?php


namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageService;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        return Package::with('packageServices')->get();
    }

    public function store(Request $request)
    {
        $package = Package::create($request->all());

        foreach ($request->services as $service) {
            PackageService::create([
                'package_id' => $package->id,
                'name' => $service['name'],
                'slug' => $service['slug'],
                'status' => $service['status'],
            ]);
        }

        return response()->json($package, 201);
    }

    public function show($id)
    {
        return Package::with('packageServices')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        $package->update($request->all());

        // Update services as needed
        // ...

        return response()->json($package, 200);
    }

    public function destroy($id)
    {
        Package::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
