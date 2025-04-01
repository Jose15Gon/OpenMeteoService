<?php

use App\Enums\WeatherMetrics;
use App\Services\OpenMeteoService;
use Illuminate\Support\Facades\Artisan;
; 

Artisan::command('get-weather {lat} {lng}', function ($lat, $lng) {

    $meteoService = new OpenMeteoService((float) $lat, (float) $lng);
    $openMeteoService = new OpenMeteoService((float) $lat, (float) $lng);

    try {


        $meteoService->setMetrics(WeatherMetrics::Rain, WeatherMetrics::Precipitation, WeatherMetrics::CloudCover);
        $weather = $meteoService->currentWeather();

        $this->info("Clima actual:");
$this->info("Temperatura: " . $weather['temperature'] . " °C");
$this->info("Precipitaciones: " . $weather['precipitation'] . " mm");
$this->info("Lluvia: " . $weather['rain'] . " mm");
$this->info("Velocidad del viento: " . $weather['windspeed'] . " km/h");
$this->info("Dirección del viento: " . $weather['winddirection'] . " °");
$this->info("Sensación térmica: " . $weather['apparent_temperature'] . " °C");
$this->info("Día/Noche: " . ($weather['is_day'] == 1 ? 'Día' : 'Noche'));
$this->info("Humedad relativa: " . $weather['relative_humidity'] . " ");
$this->info("Chubascos: " . $weather['showers'] . " ");
$this->info("Ráfagas de viento: " . $weather['wind_gusts'] . " ");
$this->info("Nevadas: " . $weather['snowfall'] . " ");
$this->info("Código meteorológico: " . $weather['weather_code']);
$this->info("Presión en superficie: " . $weather['surface_pressure'] . " ");
$this->info("Presión a nivel del mar: " . $weather['pressure_msl'] . " ");
$this->info("Nubes: " . $weather['cloud_cover'] . " ");
$this->info("-------");


        $startDate = new DateTime('2025-04-01');
        $endDate = new DateTime('2025-04-02');
        
        $openMeteoService->setMetrics(WeatherMetrics::Temperature, WeatherMetrics::Precipitation);


        $historicalData = $openMeteoService->historicalWeather($startDate, $endDate);
        
        $this->info("Datos históricos:");
foreach ($historicalData as $data) {
    $this->info("Fecha: " . $data['time']);
    $this->info("Temperatura: " . $data['temperature'] . " °C");
    $this->info("Precipitaciones: " . $data['precipitation'] . " mm");
    $this->info("Lluvia: " . $data['rain'] . " mm");
    $this->info("Velocidad del viento: " . $data['windspeed'] . " km/h");
    $this->info("Dirección del viento: " . $data['winddirection'] . " °");
    $this->info("Sensación térmica: " . $data['apparent_temperature'] . " °C");
    $this->info("Día/Noche: " . ($data['is_day'] == 1 ? 'Día' : 'Noche'));
    $this->info("Humedad relativa: " . $data['relative_humidity'] . " ");
    $this->info("Chubascos: " . $data['showers'] . " ");
    $this->info("Ráfagas de viento: " . $data['wind_gusts'] . " ");
    $this->info("Nevadas: " . $data['snowfall'] . " ");
    $this->info("Código meteorológico: " . $data['weather_code']);
    $this->info("Presión en superficie: " . $data['surface_pressure'] . " ");
    $this->info("Presión a nivel del mar: " . $data['pressure_msl'] . " ");
    $this->info("Nubes: " . $data['cloud_cover'] . " ");
    $this->info("-------");
}

        
    } catch (\Exception $e) {
        $this->error("Error: " . $e->getMessage());
    }

})->purpose('Get info from OpenMeteo API');

