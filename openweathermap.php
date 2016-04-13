<?php
require_once(__DIR__ . '/slack_post.config.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

// -------------------------------- //
// OpenWeatherMap

// Code のリスト: http://openweathermap.org/weather-conditions
function get_weather_info($code) {
    // Group 2xx: Thunderstorm
    if (200 <= $code && $code < 300) {
        return array('group' => 'Thunderstorm', 'icon' => ':zap:');
    }
    // Group 3xx: Drizzle
    if (300 <= $code && $code < 400) {
        return array('group' => 'Drizzle', 'icon' => ':umbrella:');
    }
    // Group 5xx: Rain
    if (500 <= $code && $code < 600) {
        return array('group' => 'Rain', 'icon' => ':umbrella:');
    }
    // Group 6xx: Snow
    if (600 <= $code && $code < 700) {
        return array('group' => 'Snow', 'icon' => ':snowman:');
    }
    // Group 7xx: Atmosphere
    if (700 <= $code && $code < 800) {
        return array('group' => 'Atmosphere', 'icon' => ':foggy:');
    }
    // Group 800: Clear
    if ($code === 800) {
        return array('group' => 'Clear', 'icon' => ':sunny:');
    }
    // Group 80x: Clouds
    if (801 <= $code && $code < 900) {
        return array('group' => 'Clouds', 'icon' => ':cloud:');
    }
    // Group 90x: Extreme / Group 9xx: Additional
    if (900 <= $code && $code < 1000) {
        return array('group' => 'Extreme', 'icon' => ':cyclone:');
    }
    return array('group' => '', 'icon' => ':question:');
}
