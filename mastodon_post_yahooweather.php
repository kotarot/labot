<?php
require_once(__DIR__ . '/mastodon.config.php');
require_once(__DIR__ . '/mastodon_yahooweather.php');

// Yahoo 天気 RSS から「東京」の今日の天気をつぶやく
$posttext = get_yahooweather();

if ($posttext !== '') {
    $posttext = urlencode('おはよう！' . $posttext);
    $command = 'curl -X POST -d "access_token=' . MASTODON_ACCESS_TOKEN . '&status=' . $posttext
             . '&visibility=public" -Ss https://' . MASTODON_HOST . '/api/v1/statuses';
    exec($command, $out, $ret);
    var_dump($out);
    print $ret . "\n";
}
