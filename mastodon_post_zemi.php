<?php
require_once(__DIR__ . '/mastodon.config.php');
require_once(__DIR__ . '/mastodon_googleapi.php');

var_dump($upcoming_teizemi);
var_dump($is_teizemi_today);
var_dump($is_teizemi_tomorrow);

$text = '';
if ($argv[1] === 'today' && $is_teizemi_today) {
    $text = '今日は定ゼミだよ';
}
if ($argv[1] === 'tomorrow' && $is_teizemi_tomorrow) {
    $text = '明日は定ゼミだよ';
}
if ($text !== '') {
    $command = 'curl -X POST -d "access_token=' . MASTODON_ACCESS_TOKEN . '&status=' . $text
        . '&visibility=private" -Ss https://' . MASTODON_HOST . '/api/v1/statuses';
    exec($command, $out, $ret);
    var_dump($out);
    print $ret . "\n";
} else {
    print "Do nothing\n";
}
