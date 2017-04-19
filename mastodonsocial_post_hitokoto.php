<?php
require_once(__DIR__ . '/mastodonsocial_post.config.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

// -------------------------------- //
// Mastodon 投稿処理

$text_candidates = array(
    'coffee' =>
        '10時になったよ! 仕事やめて休憩しよう :coffee: :coffee: :coffee: :coffee:',
    'lunch' =>
        '12時になったよ! 仕事やめてご飯を食べよう :fries: :pizza: :hotdog: :taco: :burrito: :stuffed_flatbread: :curry: :ramen: :spaghetti: :sushi: :fried_shrimp:',
    'nap' =>
        '2時になったよ! 仕事やめて昼寝をしよう :sleeping_accommodation: :sleeping_accommodation: :sleeping_accommodation: :sleeping_accommodation:',
    'oyatsu' =>
        '3時になったよ! 仕事やめておやつを食べよう :pancakes: :doughnut: :cookie: :dango: :chocolate_bar: :custard:',
    'beer' =>
        '定時になったよ! 仕事やめてビールを飲もう :beer: :beers: :beer: :beers: :beer: :beers: :beer: :beers: :beer: :beers: :beer: :beers: :beer: :beers: :beer: :beers:',
    'gotobed' =>
        '0時になったよ! エロ画像見てないでおやすみ :zzz: :zzz: :zzz: :zzz: :zzz: :zzz: :zzz: :zzz:',
    'chinchin' =>
        'おちんちんびろーん'
);

$text = '';
if (array_key_exists($argv[1], $text_candidates)) {
    $text = $text_candidates[$argv[1]];
    $text = urlencode($text);
}
if ($text !== '') {
    // mstdn.jp
    $command = 'curl -X POST -d "access_token=' . MASTODONSOCIAL_MSTDNJP_ACCESS_TOKEN . '&status=' . $text
             . '&visibility=public" -Ss https://mstdn.jp/api/v1/statuses';
    exec($command, $out, $ret);
    var_dump($out);
    print $ret . "\n";

    // pawoo.net
    $command = 'curl -X POST -d "access_token=' . MASTODONSOCIAL_PAWOO_ACCESS_TOKEN . '&status=' . $text
             . '&visibility=public" -Ss https://pawoo.net/api/v1/statuses';
    exec($command, $out, $ret);
    var_dump($out);
    print $ret . "\n";
} else {
    print "Do nothing\n";
}
