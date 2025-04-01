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
     * @access public
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

    public function currentWeather(): array
    {

        $url = $this->buildUrl('current');

        $response = Http::get($url);

        if ($response->failed()) {
            throw new \Exception('Error al obtener los datos del clima actual');
        }

        $data = json_decode($response->body(), true);

        if (!isset($data['current'])) {
            throw new \Exception('La API no devolvió datos de clima actual');
        }

        return [
            'temperature' => $data['current']['temperature_2m'] ?? 'No seleccionado',
            'precipitation' => $data['current']['precipitation'] ?? 'No seleccionado',
            'rain' => $data['current']['rain'] ?? 'No seleccionado',
            'windspeed' => $data['current']['wind_speed_10m'] ?? 'No seleccionado',
            'winddirection' => $data['current']['wind_direction_10m'] ?? 'No seleccionado',
            'apparent_temperature' => $data['current']['apparent_temperature'] ?? 'No seleccionado',
            'is_day' => $data['current']['is_day'] ?? 'No seleccionado',
            'relative_humidity' => $data['current']['relative_humidity_2m'] ?? 'No seleccionado',
            'showers' => $data['current']['showers'] ?? 'No seleccionado',
            'wind_gusts' => $data['current']['wind_gusts_10m'] ?? 'No seleccionado',
            'snowfall' => $data['current']['snowfall'] ?? 'No seleccionado',
            'weather_code' => $data['current']['weather_code'] ?? 'No seleccionado',
            'surface_pressure' => $data['current']['surface_pressure'] ?? 'No seleccionado',
            'pressure_msl' => $data['current']['pressure_msl'] ?? 'No seleccionado',
            'cloud_cover' => $data['current']['cloud_cover'] ?? 'No seleccionado',
        ];
        
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
     
         if ($response->failed()) {
             throw new \Exception('Error al obtener los datos históricos del clima');
         }
     
         $data = json_decode($response->body(), true);
     
         if (!isset($data['hourly'])) {
             throw new \Exception('La API no devolvió datos históricos del clima');
         }
     
        $historicalData = [];
        foreach ($data['hourly']['time'] as $index => $time) {
            $historicalData[] = [
                'time' => $time,
                'temperature' => $data['hourly']['temperature_2m'][$index] ?? 'No seleccionado',
                'precipitation' => $data['hourly']['precipitation'][$index] ?? 'No seleccionado',
                'rain' => $data['hourly']['rain'][$index] ?? 'No seleccionado',
                'windspeed' => $data['hourly']['wind_speed_10m'][$index] ?? 'No seleccionado',
                'winddirection' => $data['hourly']['wind_direction_10m'][$index] ?? 'No seleccionado',
                'apparent_temperature' => $data['hourly']['apparent_temperature'][$index] ?? 'No seleccionado',
                'is_day' => $data['hourly']['is_day'][$index] ?? 'No seleccionado',
                'relative_humidity' => $data['hourly']['relative_humidity_2m'][$index] ?? 'No seleccionado',
                'showers' => $data['hourly']['showers'][$index] ?? 'No seleccionado',
                'wind_gusts' => $data['hourly']['wind_gusts_10m'][$index] ?? 'No seleccionado',
                'snowfall' => $data['hourly']['snowfall'][$index] ?? 'No seleccionado',
                'weather_code' => $data['hourly']['weather_code'][$index] ?? 'No seleccionado',
                'surface_pressure' => $data['hourly']['surface_pressure'][$index] ?? 'No seleccionado',
                'pressure_msl' => $data['hourly']['pressure_msl'][$index] ?? 'No seleccionado',
                'cloud_cover' => $data['hourly']['cloud_cover'][$index] ?? 'No seleccionado',
            ];
}


     
         return $historicalData;
     }

}
