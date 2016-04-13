<?php
require_once(__DIR__ . '/slack_post.config.php');
include_once(__DIR__ . '/openweathermap.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

// Hackey の LED を (4秒間) 点灯させる URL リスト
$hackey_urls = array(
    'red' =>    'http://hackey-app.cerevo.com/api/v1/blink/3edb27c6d4a40081',
    'green' =>  'http://hackey-app.cerevo.com/api/v1/blink/63725cf41251f6ca',
    'blue' =>   'http://hackey-app.cerevo.com/api/v1/blink/9be00fb035e1678f',
    'yellow' => 'http://hackey-app.cerevo.com/api/v1/blink/ae45b7d6be0280ec',
    'purple' => 'http://hackey-app.cerevo.com/api/v1/blink/eb8e37537f76ee37'
);

// 天気グループと色の関係
$weather_colors = array(
    'Thunderstorm' => 'blue',
    'Drizzle'      => 'blue',
    'Rain'         => 'blue',
    'Snow'         => 'green',
    'Atmosphere'   => 'green',
    'Clear'        => 'yellow',
    'Clouds'       => 'purple',
    'Extreme'      => 'red',
);

$url = 'http://api.openweathermap.org/data/2.5/weather?q=' . OWM_CITY . '&appid=' . OWM_API_KEY;
$response = file_get_contents($url);
$responsedata = json_decode($response, true);

// 現在の天気
$current_weather_code = $responsedata['weather'][0]['id'];
//$current_weather_main = $responsedata['weather'][0]['main'];
$current_weather_info = get_weather_info($current_weather_code);
$current_weather_group = $current_weather_info['group'];
print '$current_weather_code: ' . $current_weather_code . "\n";
print '$current_weather_group: ' . $current_weather_group . "\n";


// LED色
$led_color = '';
if (array_key_exists($current_weather_group, $weather_colors)) {
    $led_color = $weather_colors[$current_weather_group];
} else {
    exit(1);
}
print '$led_color: ' . $led_color . "\n";

// 天気に応じて 10分間 (= 600秒 = 6秒 * 100) LEDをちかちかさせる
// API遅延を加味して1イテレーションだいたい6秒ぐらい
for ($i = 0; $i < 100; $i++) {
    //print "ready $i\n";
    file_get_contents($hackey_urls[$led_color]);
    //print "done $i\n";
    sleep(5);
}
