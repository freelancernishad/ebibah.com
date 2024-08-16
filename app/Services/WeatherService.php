<?php
namespace App\Services;

use GuzzleHttp\Client;

class WeatherService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getWeather($city)
    {
        $apiKey = env('WEATHER_API_KEY');
        $url = "http://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}";

        $response = $this->client->get($url);
        $weatherData = json_decode($response->getBody(), true);

        if ($weatherData) {
            // Convert temperature from Kelvin to Celsius
            if (isset($weatherData['main']['temp'])) {
                $weatherData['main']['temp_celsius'] = round($weatherData['main']['temp'] - 273.15, 2);
            }
        }

        return $weatherData;
    }


    public function getWeatherByCoordinates($lat, $lon)
    {
        $apiKey = env('WEATHER_API_KEY');
        $url = "http://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$apiKey}";

        $response = $this->client->get($url);
        $weatherData = json_decode($response->getBody(), true);

        if ($weatherData) {
            // Convert temperature from Kelvin to Celsius
            if (isset($weatherData['main']['temp'])) {
                $weatherData['main']['temp_celsius'] = round($weatherData['main']['temp'] - 273.15, 2);
            }
        }

        return $weatherData;
    }
    public function getLocationName($lat, $lon)
    {
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&zoom=10&accept-language=bd";

        $response = $this->client->get($url);
        $locationData = json_decode($response->getBody(), true);

        if (isset($locationData['display_name'])) {
            $locationNames = [
                'bangla' => $locationData,
                'english' => $this->getEnglishLocationName($lat, $lon) // Fetch English location name separately
            ];
            return $locationNames;
        } else {
            return null; // Return null if location name is not found
        }
    }

    private function getEnglishLocationName($lat, $lon)
    {
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&zoom=10&accept-language=en";

        $response = $this->client->get($url);
        $locationData = json_decode($response->getBody(), true);

        if (isset($locationData)) {
            return $locationData;
        } else {
            return null; // Return null if English location name is not found
        }
    }

}
