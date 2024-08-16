<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WeatherService;

class WeatherController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function show(Request $request)
    {



        $latitude = $request->input('lat');
        $longitude = $request->input('lon');


        if ($latitude && $longitude) {

            // Call WeatherService with latitude and longitude
            $weather = $this->weatherService->getWeatherByCoordinates($latitude, $longitude);
// return $districtName = $this->weatherService->getLocationName($latitude, $longitude);

              $location = $this->weatherService->getLocationName($latitude, $longitude)['english'];

              $address = $location['address'];

              $country_code = $address['country_code'];
            if($country_code=='bd'){

                $districtName = $address['state_district'];
            }else{
                $districtName = "Dhaka";
            }



            $city = str_replace(' District', '', $districtName);
             $weather = $this->weatherService->getWeather($city);
            // $city = $weather['name']; // Get city name from response
        } else {
            // Default to New York if no coordinates provided
            $city = $request->input('city', 'Dhaka'); // Default to New York
            $weather = $this->weatherService->getWeather($city);
        }


        return $weather;
        // return view('weather', ['weather' => $weather, 'city' => $city]);
    }
}
