<?php
require_once(__DIR__ . '/mastodon_post.config.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}


// -------------------------------- //
// 電車遅延取得

$current_yamanote = -1;
$current_fukutoshin = -1;
$current_unixtime = time();

$jsonstr = file_get_contents('https://rti-giken.jp/fhc/api/train_tetsudo/delay.json');
$jsonobj = json_decode($jsonstr, true);
foreach ($jsonobj as $line) {
    if ($line['company'] === 'JR東日本' && $line['name'] === '山手線') {
        //$current_yamanote = $line['lastupdate_gmt'];
        $current_yamanote = $current_unixtime;
    }
    if ($line['company'] === '東京メトロ' && $line['name'] === '副都心線') {
        //$current_fukutoshin = $line['lastupdate_gmt'];
        $current_fukutoshin = $current_unixtime;
    }
}
//var_dump($current_yamanote);
//var_dump($current_fukutoshin);

// 現在の数値を読み込む
$last_yamanote = explode(',', trim(file_get_contents(__DIR__ . '/last_yamanote.txt')));
$last_fukutoshin = explode(',', trim(file_get_contents(__DIR__ . '/last_fukutoshin.txt')));
//var_dump($last_yamanote);
//var_dump($last_fukutoshin);

// 山手線
$text_yamanote = '';
$status_yamanote = $last_yamanote[0];
if ($last_yamanote[0] === 'BAD' && $current_yamanote === -1) {
    $text_yamanote = '山手線は正常運転中だよ :stuck_out_tongue_winking_eye:';
    $status_yamanote = 'OK';
} else if ($last_yamanote[0] === 'OK' && 0 < $current_yamanote) {
    $text_yamanote = '山手線が遅延しているみたい! 気をつけて :innocent:';
    $status_yamanote = 'BAD';
}
file_put_contents(__DIR__ . '/last_yamanote.txt', $status_yamanote . ',' . $current_unixtime . "\n");

// 副都心線
$text_fukutoshin = '';
$status_fukutoshin = $last_fukutoshin[0];
if ($last_fukutoshin[0] === 'BAD' && $current_fukutoshin === -1) {
    $text_fukutoshin = '副都心線は正常運転中だよ :stuck_out_tongue_winking_eye:';
    $status_fukutoshin = 'OK';
} else if ($last_fukutoshin[0] === 'OK' && 0 < $current_fukutoshin) {
    $text_fukutoshin = '副都心線が遅延しているみたい! 気をつけて :innocent:';
    $status_fukutoshin = 'BAD';
}
file_put_contents(__DIR__ . '/last_fukutoshin.txt', $status_fukutoshin . ',' . $current_unixtime . "\n");

// -------------------------------- //
// Mastodon 投稿処理
if ($text_yamanote !== '') {
    $command = 'curl -X POST -d "access_token=' . MASTODON_ACCESS_TOKEN . '&status=' . urlencode($text_yamanote)
             . '&visibility=public" -Ss https://mstdn.togawa.cs.waseda.ac.jp/api/v1/statuses';
    exec($command, $out, $ret);
    var_dump($out);
    print $ret . "\n";
} else {
    print "Yamanote: Do nothing\n";
}

if ($text_fukutoshin !== '') {
    $command = 'curl -X POST -d "access_token=' . MASTODON_ACCESS_TOKEN . '&status=' . urlencode($text_fukutoshin)
             . '&visibility=public" -Ss https://mstdn.togawa.cs.waseda.ac.jp/api/v1/statuses';
    exec($command, $out, $ret);
    var_dump($out);
    print $ret . "\n";
} else {
    print "Fukutoshin: Do nothing\n";
}
