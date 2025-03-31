<?php

namespace App\Enums;

enum WeatherMetrics: string {
    case Temperature = 'temperature_2m';
    case Precipitation = 'precipitation';
    case Rain = 'rain';
    case WindSpeed = 'wind_speed_10m';
    case WindDirection = 'wind_direction_10m';
    case ApparentTemperature = 'apparent_temperature';
    case IsDay = 'is_day';
    case RelativeHumidity = 'relative_humidity_2m';
    case Showers = 'showers';
    case WindGusts = 'wind_gusts_10m';
    case Snowfall = 'snowfall';
    case WeatherCode = 'weather_code';
    case SurfacePressure = 'surface_pressure';
    case PressureMSL = 'pressure_msl';
    case CloudCover = 'cloud_cover';
}
