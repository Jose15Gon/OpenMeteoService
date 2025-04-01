<?php

/**
 * Service to interact with the Open Meteo API to fetch weather data.
 *
 * This class handles requests to the Open Meteo API to retrieve
 * the current weather data based on latitude and longitude.
 *
 * @package   App\Services
 * @author    José González <jose@bitgenio.com>
 * @copyright 2025 Bitgenio DevOps SLU
 * @since 28/03/2025
 * @version 1.1.0
 */

namespace App\Services;

use App\Enums\WeatherMetrics;
use DateTime;
use Illuminate\Support\Facades\Http;

class OpenMeteoService
{
    
    private const API_URL_CURRENT = 'https://api.open-meteo.com/v1/forecast';
    private array $metrics = [];


     /**
     * OpenMeteoService constructor.
     *
     * This constructor initializes the latitude and longitude
     * values that will be used to fetch weather data.
     *
     * @access public
     * @author José González <jose@bitgenio.com>
     * @since 28/03/2025
     * @param float $lat Latitude of the location.
     * @param float $lng Longitude of the location.
     */
    public function __construct(private float $lat, private float $lng)
    {
       
    }

     /**
     * Set the weather metrics that should be fetched from the API.
     * 
     * @param WeatherMetrics ...$metrics A list of weather metrics to be included in the API request.
     * @return void
     */
    public function setMetrics(WeatherMetrics ...$metrics): void
    {
        $this->metrics = $metrics;
    }

    /**
     * Fetch the current weather data from the Open Meteo API.
     *
     * This method constructs the API request, fetches the current weather data,
     * and returns an array containing various weather metrics. If a metric is unavailable, it will return 
     * 'No seleccionado'
     *
     * @access private
     * @author José González <jose@bitgenio.com>
     * @since 28/03/2025
     * @return array An array containing the weather metrics.
     */

     private function buildUrl(string $type, array $additionalParams = []): string
     {
         $metricsString = implode(',', array_map(fn(WeatherMetrics $metric) => $metric->value, $this->metrics));
         
         $url = self::API_URL_CURRENT . '?latitude=' . $this->lat . '&longitude=' . $this->lng;
         $url .= '&' . $type . '=' . $metricsString;
         
         foreach ($additionalParams as $key => $value) {
             $url .= '&' . $key . '=' . $value;
         }
 
         return $url;
     }

     /**
     * Handle the response from the API and check for errors.
     *
     * This method checks if the API response has failed. If the response failed,
     * it throws an exception with an error message. If it doesn't fail, it decodes
     * the JSON response body into an array and returns it.
     *
     * @access private
     * @author José González <jose@bitgenio.com>
     * @since 01/04/2025
     * @param $response The response object from the API request.
     * @param string $errorMessage The error message to throw if the response fails.
     * @return array The decoded JSON response as an array.
     */
     private function handleApiResponse($response, string $errorMessage): array
     {
         if ($response->failed()) {
             throw new \Exception($errorMessage);
         }
         $data = json_decode($response->body(), true);
 
         return $data;
     }

    public function currentWeather(): array
    {

        $url = $this->buildUrl('current');
        $response = Http::get($url);

        $data = $this->handleApiResponse($response, 'Error al obtener los datos del clima actual');

        if (!isset($data['current'])) {
            throw new \Exception('La API no devolvió datos de clima actual');
        }
        return $this->mapWeatherData($data, 'current');
    }

    /**
     * Fetch historical weather data from the Open Meteo API.
     *
     * This method retrieves historical weather data for a given date range.
     * It constructs the API request using the provided latitude, longitude,
     * start date, and end date, then returns an array containing various weather metrics for each hour.
     * If any metric is unavailable, it will return 'No seleccionado'.
     * 
     * @access public
     * @author José González <jose@bitgenio.com>
     * @since 28/03/2025
     * @param DateTime $startDate Start date for historical data (YYYY-MM-DD).
     * @param DateTime $endDate End date for historical data (YYYY-MM-DD).
     * @return array An array of historical weather data, containing the temperature, precipitation, rain, wind speed, wind direction, apparent temperature, and day/night status.
     */

     public function historicalWeather(DateTime $startDate, DateTime $endDate): array
     {

        $start = $startDate->format('Y-m-d');
        $end = $endDate->format('Y-m-d');
        
        $additionalParams = [
            'start_date' => $start,
            'end_date' => $end
        ];

        $url = $this->buildUrl('hourly', $additionalParams);
     
         $response = Http::get($url);

         $data = $this->handleApiResponse($response, 'Error al obtener los datos históricos del clima');
     
         if (!isset($data['hourly'])) {
             throw new \Exception('La API no devolvió datos históricos del clima');
         }
     
         return $this->mapWeatherData($data, 'hourly');
     }

     public function mapWeatherData(array $data, string $type): array
{
    $arrayMetrics = [
        'temperature_2m', 'precipitation', 'rain', 'wind_speed_10m', 'wind_direction_10m', 'apparent_temperature', 
        'is_day', 'relative_humidity_2m', 'showers', 'wind_gusts_10m', 'snowfall', 'weather_code', 'surface_pressure', 
        'pressure_msl', 'cloud_cover'
    ];
    $arrayEnums = [
        'temperature', 'precipitation', 'rain', 'windspeed', 'winddirection', 'apparent_temperature', 'is_day', 
        'relative_humidity', 'showers', 'wind_gusts', 'snowfall', 'weather_code', 'surface_pressure', 'pressure_msl', 
        'cloud_cover'
    ];
    
    $weatherData = [];

    if ($type === 'current') {
        foreach ($arrayMetrics as $i => $metric) {
            $weatherData[$arrayEnums[$i]] = $data['current'][$metric] ?? 'No seleccionado';
        }
    }
    elseif ($type === 'hourly') {
        foreach ($data['hourly']['time'] as $index => $time) {
            $hourlyData = ['time' => $time];
            foreach ($arrayMetrics as $i => $metric) {
                $hourlyData[$arrayEnums[$i]] = $data['hourly'][$metric][$index] ?? 'No seleccionado';
            }
            $weatherData[] = $hourlyData;
        }
    }
    return $weatherData;
}


}
