<?php

namespace App\Http\Controllers;

use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SocialLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $socialLinks = SocialLink::all();

        return response()->json($socialLinks);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'platform' => 'required|string|unique:social_links',
            'link' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $socialLink = new SocialLink();
        $socialLink->platform = $request->platform;
        $socialLink->link = $request->link;
        $socialLink->save();

        return response()->json(['message' => 'Social link created successfully'], 201);
    }

    /**
     * Display the specified resource by platform.
     *
     * @param  string  $platform
     * @return \Illuminate\Http\Response
     */
    public function showByPlatform($platform)
    {
        $socialLinks = SocialLink::where('platform', $platform)->get();

        return response()->json($socialLinks);
    }

    /**
     * Update the specified resource in storage by ID or Platform.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $idOrPlatform
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idOrPlatform)
    {
        $validator = Validator::make($request->all(), [
            'link' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $socialLink = SocialLink::where('id', $idOrPlatform)
            ->orWhere('platform', $idOrPlatform)
            ->first();

        if (!$socialLink) {
            return response()->json(['error' => 'Social link not found'], 404);
        }

        $socialLink->update([
            'link' => $request->link,
        ]);

        return response()->json(['message' => 'Social link updated successfully']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SocialLink  $socialLink
     * @return \Illuminate\Http\Response
     */
    public function destroy(SocialLink $socialLink)
    {
        $socialLink->delete();

        return response()->json(['message' => 'Social link deleted successfully']);
    }
}
