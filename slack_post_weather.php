<?php
require __DIR__ . '/slack_post.config.php';

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

$todaystr = date('Y-m-d');
$postdata = array(
    // 天気
    'weather' => array(
        // 午前 (9時)
        array(
            'main' => '不明',
            'icon' => ':question:'
        ),
        // 午後 (15時)
        array(
            'main' => '不明',
            'icon' => ':question:'
        ),
        // 夜 (21時)
        array(
            'main' => '不明',
            'icon' => ':question:'
        )
    ),
    // 気温
    'temperature' => array(
        'max' => -999,
        'min' => 999
    )
);


// -------------------------------- //
// OpenWeatherMap

// Code のリスト: http://openweathermap.org/weather-conditions
function get_weather_icon($code) {
    // Group 2xx: Thunderstorm
    if (200 <= $code && $code < 300) {
        return ':zap:';
    }
    // Group 3xx: Drizzle
    if (300 <= $code && $code < 400) {
        return ':umbrella:';
    }
    // Group 5xx: Rain
    if (500 <= $code && $code < 600) {
        return ':umbrella:';
    }
    // Group 6xx: Snow
    if (600 <= $code && $code < 700) {
        return ':snowman:';
    }
    // Group 7xx: Atmosphere
    if (700 <= $code && $code < 800) {
        return ':foggy:';
    }
    // Group 800: Clear
    if ($code === 800) {
        return ':sunny:';
    }
    // Group 80x: Clouds
    if (801 <= $code && $code < 900) {
        return ':cloud:';
    }
    // Group 90x: Extreme / Group 9xx: Additional
    if (900 <= $code && $code < 1000) {
        return ':cyclone:';
    }
    return ':question:';
}

$url = 'http://api.openweathermap.org/data/2.5/forecast?q=' . OWM_CITY . '&units=metric&appid=' . OWM_API_KEY;
$response = file_get_contents($url);
$responsedata = json_decode($response, true);
if ($responsedata['cod'] === '200') {
    // `list` は3時間ごとに入っている
    // 9時 -> 午前、15時 -> 午後、21時 -> 夜 ということにする
    foreach ($responsedata['list'] as $slot) {
        if ($slot['dt_txt'] === $todaystr . ' 09:00:00') {
            $postdata['weather'][0]['main'] = $slot['weather'][0]['main'];
            $postdata['weather'][0]['icon'] = get_weather_icon($slot['weather'][0]['id']);
        } else if ($slot['dt_txt'] === $todaystr . ' 15:00:00') {
            $postdata['weather'][1]['main'] = $slot['weather'][0]['main'];
            $postdata['weather'][1]['icon'] = get_weather_icon($slot['weather'][0]['id']);
        } else if ($slot['dt_txt'] === $todaystr . ' 21:00:00') {
            $postdata['weather'][2]['main'] = $slot['weather'][0]['main'];
            $postdata['weather'][2]['icon'] = get_weather_icon($slot['weather'][0]['id']);
        }

        // 最高気温/最低気温
        if (substr($slot['dt_txt'], 0, 10) === $todaystr) {
            //print $slot['main']['temp'] . "\n";
            $postdata['temperature']['max'] = max((int)round($slot['main']['temp']), $postdata['temperature']['max']);
            $postdata['temperature']['min'] = min((int)round($slot['main']['temp']), $postdata['temperature']['min']);
        }
    }
}


// -------------------------------- //
// Slack投稿処理

$text = '[' . $todaystr .'] *今日の天気:* '
      . '午前 ' . $postdata['weather'][0]['main'] . ' ' . $postdata['weather'][0]['icon'] . ' → '
      . '午後 ' . $postdata['weather'][1]['main'] . ' ' . $postdata['weather'][1]['icon'] . ' → '
      . '夜 '   . $postdata['weather'][2]['main'] . ' ' . $postdata['weather'][2]['icon'] . ' '
      . '(最高気温 ' . $postdata['temperature']['max'] . '°C / 最低気温 ' . $postdata['temperature']['min'] . '°C)';
$text = urlencode($text);

$url = "https://slack.com/api/chat.postMessage?token=" . SLACK_API_KEY
     . "&channel=%23general&username=" . BOT_NAME . "&icon_emoji=" . BOT_ICON . "&text=${text}";
$response = file_get_contents($url);
print $response . "\n";
