<?php
require_once(__DIR__ . '/mastodon.config.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

// -------------------------------- //
// Mastodon 投稿処理

$text_candidates = array(
    'coffee' =>
        '10時になったよ! 研究をやめにして休憩しよう :coffee: :coffee: :coffee: :coffee:',
    'lunch' =>
        '12時になったよ! 研究をやめにしてご飯を食べよう :fries: :pizza: :hotdog: :taco: :burrito: :stuffed_flatbread: :curry: :ramen: :spaghetti: :sushi: :fried_shrimp:',
    'nap' =>
        '2時になったよ! 研究をやめにして昼寝をしよう :sleeping_accommodation: :sleeping_accommodation: :sleeping_accommodation: :sleeping_accommodation:',
    'oyatsu' =>
        '3時になったよ! 研究をやめにしておやつを食べよう :pancakes: :doughnut: :cookie: :dango: :chocolate_bar: :custard:',
    'beer' =>
        '定時になったよ! 研究をやめにしてビールを飲もう :beer: :beers: :beer: :beers: :beer: :beers: :beer: :beers: :beer: :beers: :beer: :beers: :beer: :beers: :beer: :beers:',
    'gotobed' =>
        '0時になったよ! 研究をやめにして今日はおやすみ :zzz: :zzz: :zzz: :zzz: :zzz: :zzz: :zzz: :zzz:'
);

$text = '';
if (array_key_exists($argv[1], $text_candidates)) {
    $text = $text_candidates[$argv[1]];
    $command = 'curl -X POST -d "access_token=' . MASTODON_ACCESS_TOKEN . '&status=' . urlencode($text)
             . '&visibility=public" -Ss https://' . MASTODON_HOST . '/api/v1/statuses';
    exec($command, $out, $ret);
    var_dump($out);
    print $ret . "\n";
} else {
    print "Do nothing\n";
}
