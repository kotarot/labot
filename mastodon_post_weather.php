<?php
require_once(__DIR__ . '/slack_post.config.php');
require_once(__DIR__ . '/mastodon_post.config.php');
include_once(__DIR__ . '/openweathermap.php');

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


$url = 'http://api.openweathermap.org/data/2.5/forecast?q=' . OWM_CITY . '&units=metric&appid=' . OWM_API_KEY;
$response = file_get_contents($url);
$responsedata = json_decode($response, true);
if ($responsedata['cod'] === '200') {
    // `list` は3時間ごとに入っている
    // 9時 -> 午前、15時 -> 午後、21時 -> 夜 ということにする
    foreach ($responsedata['list'] as $slot) {
        if ($slot['dt_txt'] === $todaystr . ' 09:00:00') {
            $postdata['weather'][0]['main'] = $slot['weather'][0]['main'];
            $info = get_weather_info($slot['weather'][0]['id']);
            $postdata['weather'][0]['icon'] = $info['icon'];
        } else if ($slot['dt_txt'] === $todaystr . ' 15:00:00') {
            $postdata['weather'][1]['main'] = $slot['weather'][0]['main'];
            $info = get_weather_info($slot['weather'][0]['id']);
            $postdata['weather'][1]['icon'] = $info['icon'];
        } else if ($slot['dt_txt'] === $todaystr . ' 21:00:00') {
            $postdata['weather'][2]['main'] = $slot['weather'][0]['main'];
            $info = get_weather_info($slot['weather'][0]['id']);
            $postdata['weather'][2]['icon'] = $info['icon'];
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
// Mastodon 投稿処理

//$text = '[' . $todaystr .'] 今日の天気だよ: '
$text = 'おはよう! 今日の天気だよ: '
      . '午前 ' . $postdata['weather'][0]['main'] . ' ' . $postdata['weather'][0]['icon'] . ' → '
      . '午後 ' . $postdata['weather'][1]['main'] . ' ' . $postdata['weather'][1]['icon'] . ' → '
      . '夜 '   . $postdata['weather'][2]['main'] . ' ' . $postdata['weather'][2]['icon'] . ' '
      . '(最高気温 ' . $postdata['temperature']['max'] . '°C / 最低気温 ' . $postdata['temperature']['min'] . '°C)';
$text = urlencode($text);

$command = 'curl -X POST -d "access_token=' . MASTODON_ACCESS_TOKEN . '&status=' . $text
         . '&visibility=public" -Ss http://mstdn.togawa.cs.waseda.ac.jp/api/v1/statuses';
exec($command, $out, $ret);
var_dump($out);
print $ret . "\n";
