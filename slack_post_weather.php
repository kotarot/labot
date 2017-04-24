<?php
require_once(__DIR__ . '/slack_post.config.php');
include_once(__DIR__ . '/openweathermap.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}


$todaystr = date('Y-m-d');
$postdata = get_postdata($todaystr);


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
