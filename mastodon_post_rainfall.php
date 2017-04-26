<?php
require_once(__DIR__ . '/mastodon.config.php');
require_once(__DIR__ . '/yahoodev.config.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}


// -------------------------------- //
// 降水確率

$current_unixtime = time();
//var_dump($current_unixtime);

$jsonstr = file_get_contents('https://map.yahooapis.jp/weather/V1/place?' .
    'coordinates=' . YAHOODEV_COORDINATES . '&appid=' . YAHOODEV_APPID . '&output=json');
$jsonobj = json_decode($jsonstr, true);
//var_dump($jsonobj);

// 現在の降水確率
$current_rainfall = $jsonobj['Feature'][0]['Property']['WeatherList']['Weather'][0]['Rainfall'];
//var_dump($current_rainfall);

// 1時間後の降水確率
$forecast_rainfall = $jsonobj['Feature'][0]['Property']['WeatherList']['Weather'][6]['Rainfall'];
//var_dump($forecast_rainfall);

// 最後につぶやいた時間を読み込む
$last_unixtime = (int)trim(file_get_contents(__DIR__ . '/last_rainfall.txt'));
//var_dump($last_unixtime);

// 現在の観測が 0% で、1時間後の降水確率が 1% 以上、
// かつ、過去3時間 (プログラム的には2時間59分) で1度もつぶやいていないときに限りつぶやく
$posttext = '';
if ($current_rainfall <= 0.0 && 0.0 < $forecast_rainfall
    && 179*60 <= $current_unixtime - $last_unixtime) {

    $posttext = 'これから雨が降るみたい (1時間後の降水確率 ' . $forecast_rainfall . '%) '
              . ':umbrella: :umbrella: :umbrella: :candy: :umbrella: '
              . '気をつけて！';
    file_put_contents(__DIR__ . '/last_rainfall.txt', $current_unixtime . "\n");
}


// -------------------------------- //
// Mastodon 投稿処理
if ($posttext !== '') {
    $command = 'curl -X POST -d "access_token=' . MASTODON_ACCESS_TOKEN . '&status=' . urlencode($posttext)
             . '&visibility=public" -Ss https://' . MASTODON_HOST . '/api/v1/statuses';
    exec($command, $out, $ret);
    var_dump($out);
    print $ret . "\n";
} else {
    print "Do nothing\n";
}
