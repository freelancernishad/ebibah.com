<?php

namespace App\Http\Controllers\Backed;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\SettingValue;

class SettingBackedController extends Controller
{
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

        return response()->json(['success' => true, 'setting' => $setting], 201);
    }

    // Update a setting and its values
    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $setting->update(['name' => $request->name]);

        // Clear existing values before updating
        $setting->values()->delete();

        foreach ($request->values as $value) {
            $setting->values()->create([
                'value_id' => $value['id'],
                'name' => $value['name']
            ]);
        }

        return response()->json(['success' => true, 'setting' => $setting], 200);
    }

    // Delete a setting and its values
    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);
        $setting->values()->delete();  // Delete associated values
        $setting->delete();  // Delete the setting itself

        return response()->json(['success' => true], 200);
    }



        // Store a single setting value
        public function storeValue(Request $request, $setting_id)
        {
            $setting = Setting::findOrFail($setting_id);

            $settingValue = $setting->values()->create([
                'value_id' => $request->value_id,
                'name' => $request->name
            ]);

            return response()->json(['success' => true, 'setting_value' => $settingValue], 201);
        }

        // Update a specific setting value
        public function updateValue(Request $request, $setting_id, $value_id)
        {
            $setting = Setting::findOrFail($setting_id);
            $settingValue = $setting->values()->where('value_id', $value_id)->firstOrFail();

            $settingValue->update([
                'name' => $request->name
            ]);

            return response()->json(['success' => true, 'setting_value' => $settingValue], 200);
        }

        // Delete a specific setting value
        public function destroyValue($setting_id, $value_id)
        {
            $setting = Setting::findOrFail($setting_id);
            $settingValue = $setting->values()->where('value_id', $value_id)->firstOrFail();

            $settingValue->delete();

            return response()->json(['success' => true], 200);
        }


}
