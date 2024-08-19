<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // Fetch settings and their values in a formatted way
    public function index()
    {
        $settings = Setting::with('values')->get();
        $formattedSettings = [];

        foreach ($settings as $setting) {
            $formattedValues = [];
            foreach ($setting->values as $value) {
                $formattedValues[] = [
                    'id' => $value->value_id, // e.g., A+, B+
                    'name' => $value->name // e.g., A+, Divorce, Islam, etc.
                ];
            }

            // Example to add "All" option as the first item
            array_unshift($formattedValues, ['id' => '', 'name' => 'All']);

            // Format the output as key-value pairs
            $formattedSettings[$setting->name] = $formattedValues;
        }

        return response()->json($formattedSettings);
    }

    // Store new settings and values
    public function store(Request $request)
    {
        $setting = Setting::create(['name' => $request->name]);

        foreach ($request->values as $value) {
            $setting->values()->create([
                'value_id' => $value['id'],
                'name' => $value['name']
            ]);
        }

        return response()->json(['success' => true], 201);
    }
}
