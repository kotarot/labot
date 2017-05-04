<?php
require_once(__DIR__ . '/mastodon.config.php');

// Yahoo 天気 RSS から「東京」の今日の天気を取得する
function get_yahooweather() {
    $rsspath = 'https://rss-weather.yahoo.co.jp/rss/days/4410.xml';
    $xml = simplexml_load_file($rsspath);

    $todaystr = date('j') . '日';
    $posttext = '今日の天気だよ';

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

    return $posttext;
}
