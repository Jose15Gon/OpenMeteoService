<?php

namespace App\Enums;

enum WeatherMetrics: string {
    case temperature = 'temperature_2m';
    case precipitation = 'precipitation';
    case rain = 'rain';
    case windspeed = 'wind_speed_10m';
    case winddirection = 'wind_direction_10m';
    case apparent_temperature = 'apparent_temperature';
    case is_day = 'is_day';
    case relative_humidity = 'relative_humidity_2m';
    case showers = 'showers';
    case wind_gusts = 'wind_gusts_10m';
    case snowfall = 'snowfall';
    case weather_code = 'weather_code';
    case surface_pressure = 'surface_pressure';
    case pressure_msl = 'pressure_msl';
    case cloud_cover = 'cloud_cover';
}
