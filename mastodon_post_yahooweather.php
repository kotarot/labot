<?php
require_once(__DIR__ . '/mastodon.config.php');

// Yahoo 天気 RSS から「東京」の今日の天気をつぶやく

$rsspath = 'https://rss-weather.yahoo.co.jp/rss/days/4410.xml';
$xml = simplexml_load_file($rsspath);

$todaystr = date('j') . '日';
$posttext = 'おはよう！今日の天気だよ';

foreach ($xml->channel->item as $item) {
    $title = $item->title;
    $link = $item->link;
    if (strpos($title, $todaystr) !== false) {
        $splitted = preg_split("//u", $title, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($splitted as $letter) {
            if ($letter === '晴') {
                $posttext .= ' :sunny:';
            }
            if ($letter === '曇') {
                $posttext .= ' :cloud:';
            }
            if ($letter === '雨') {
                $posttext .= ' :umbrella:';
            }
            if ($letter === '雪') {
                $posttext .= ' :snowman2:';
            }
        }
        $posttext .= "\n" . $title . ' ' . $link;
        break;
    }
}

if ($posttext !== '') {
    $posttext = urlencode($posttext);
    $command = 'curl -X POST -d "access_token=' . MASTODON_ACCESS_TOKEN . '&status=' . $posttext
             . '&visibility=public" -Ss https://' . MASTODON_HOST . '/api/v1/statuses';
    exec($command, $out, $ret);
    var_dump($out);
    print $ret . "\n";
}
