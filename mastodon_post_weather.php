<?php
require_once(__DIR__ . '/slack_post.config.php');
require_once(__DIR__ . '/mastodon.config.php');
include_once(__DIR__ . '/openweathermap.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}


$todaystr = date('Y-m-d');
$postdata = get_postdata($todaystr);


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
         . '&visibility=public" -Ss https://mstdn.togawa.cs.waseda.ac.jp/api/v1/statuses';
exec($command, $out, $ret);
var_dump($out);
print $ret . "\n";
