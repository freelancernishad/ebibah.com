<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Advertisement;
use Illuminate\Support\Facades\Validator;

class AdvertisementController extends Controller
{


    public function index(Request $request)
    {
        $query = Advertisement::query();
    
        // Filtering based on request parameters
    
        if ($request->has('pageurl')) {
            $query->where('page', 'like', '%' . $request->input('pageurl') . '%');
        }
    
        if ($request->has('url')) {
            $query->where('url', 'like', '%' . $request->input('url') . '%');
        }
    
        if ($request->has('banner_size')) {
            $query->where('banner_size', $request->input('banner_size'));
        }
    
        if ($request->has('slug')) {
            $query->where('slug', 'like', '%' . $request->input('slug') . '%');
        }
    
        if ($request->has('started_date')) {
            $query->where('started_date', '>=', $request->input('started_date'));
        }
    
        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->input('end_date'));
        }
    
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
    
     
    // Check if none of the filtering parameters exist
    if (!$request->hasAny(['started_date', 'end_date', 'status'])) {
        // Filtering for active advertisements
        $now = now()->format('Y-m-d H:i:s');
        $query->where(function ($query) use ($now) {
            $query->whereNull('started_date')->orWhere('started_date', '<=', $now);
        })->where(function ($query) use ($now) {
            $query->whereNull('end_date')->orWhere('end_date', '>=', $now);
        })->where('status', 'active');
    }

    
   
    if ($request->has('page')) {

        $perpage = $request->perpage ?? 20;

        $advertisements = $query->inRandomOrder()->paginate($perpage);
    }else{

        $advertisements = $query->inRandomOrder()->get();
    }
        // Execute the query

    
        return response()->json($advertisements, 200);
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required',
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'banner_size' => 'required',
            'url' => 'nullable|url',
            'started_date' => 'nullable|date_format:Y-m-d H:i:s',
            'end_date' => 'nullable|date_format:Y-m-d H:i:s',
            'default_banner' => 'nullable|string',
            'company_name' => 'nullable|string',
            'company_address' => 'nullable|string',
            'provider_name' => 'nullable|string',
            'provider_position' => 'nullable|string',
            'agreement_date' => 'nullable|date_format:Y-m-d H:i:s',
            'status' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('ads/banner', $fileName, 'protected');
        } else {
            return response()->json(['error' => 'No banner file provided.'], 422);
        }

        $advertisement = Advertisement::create([
            'page' => $request->page,
            'banner' => url('files/' . $filePath),
            'banner_size' => $request->banner_size,
            'url' => $request->url,
            'started_date' => $request->started_date,
            'end_date' => $request->end_date,
            'default_banner' => $request->default_banner,
            'company_name' => $request->company_name,
            'company_address' => $request->company_address,
            'provider_name' => $request->provider_name,
            'provider_position' => $request->provider_position,
            'agreement_date' => $request->agreement_date,
            'status' => $request->status
        ]);

        return response()->json(['advertisement' => $advertisement, 'message' => 'Advertisement created successfully.'], 201);
    }

    public function destroy($slug)
    {
        $advertisement = Advertisement::where('slug', $slug)->first();

        if (!$advertisement) {
            return response()->json(['message' => 'Advertisement not found'], 404);
        }

        $advertisement->delete();
        return response()->json(['message' => 'Advertisement deleted successfully'], 200);
    }
}
