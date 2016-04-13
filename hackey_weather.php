<?php
require_once(__DIR__ . '/slack_post.config.php');
require_once(__DIR__ . '/hackey.config.php');
include_once(__DIR__ . '/openweathermap.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

// 天気グループと色の関係
$weather_colors = array(
    'Thunderstorm' => 'blue',
    'Drizzle'      => 'blue',
    'Rain'         => 'blue',
    'Snow'         => 'purple',
    'Atmosphere'   => 'purple',
    'Clear'        => 'green',
    'Clouds'       => 'yellow',
    'Extreme'      => 'red'
);

// 気温と色の関係
function temperature_color($temp) {
    if ($temp < 0.0) {
        return 'purple';
    } else if ($temp < 10.0) {
        return 'blue';
    } else if ($temp < 20.0) {
        return 'green';
    } else if ($temp < 30.0) {
        return 'yellow';
    }
    return 'red';
}

// 開始時刻
$time_start = time();

$url = 'http://api.openweathermap.org/data/2.5/weather?q=' . OWM_CITY . '&appid=' . OWM_API_KEY;
$response = file_get_contents($url);
$responsedata = json_decode($response, true);
//var_dump($responsedata);

// 現在の天気
$current_weather_code = $responsedata['weather'][0]['id'];
//$current_weather_main = $responsedata['weather'][0]['main'];
$current_weather_info = get_weather_info((int)$current_weather_code);
$current_weather_group = $current_weather_info['group'];
print '$current_weather_code: ' . $current_weather_code . "\n";
print '$current_weather_group: ' . $current_weather_group . "\n";

// LED色
$led_color_weather = '';
if (array_key_exists($current_weather_group, $weather_colors)) {
    $led_color_weather = $weather_colors[$current_weather_group];
} else {
    exit(1);
}
print '$led_color_weather: ' . $led_color_weather . "\n";

// 現在の気温
$current_weather_temp = $responsedata['main']['temp'] - 273.15;
print '$current_weather_temp: ' . $current_weather_temp . "\n";

// LED色
$led_color_temp = temperature_color($current_weather_temp);
if (!array_key_exists($current_weather_group, $weather_colors)) {
    exit(1);
}
print '$led_color_temp: ' . $led_color_temp . "\n";

// 天気に応じて 10分間 (= 600秒) LEDをちかちかさせる
// ちなみにAPI遅延を加味して1イテレーションだいたい8秒ぐらい
for ($i = 0; ; $i++) {
    //print "ready weather_$i\n";
    file_get_contents($hackey_led_4sec_urls[$led_color_weather]);
    //print "done weather_$i\n";
    sleep(4);

    //print "ready temp_$i\n";
    file_get_contents($hackey_led_1sec_urls[$led_color_temp]);
    //print "done temp_$i\n";
    sleep(2);

    // 経過秒数
    $time_elapsed = time() - $time_start;
    //print "$time_elapsed\n";
    if (600 - 8 <= $time_elapsed) {
        exit();
    }
}
