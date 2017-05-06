<?php
require_once(__DIR__ . '/mastodon.config.php');

// 333のスクランブルを取得する
function get_scramble_333() {
    $command = CHAMPLE_HOME . '/chample -n 1';
    exec($command, $out, $ret);
    //var_dump($out);
    //print $ret . "\n";
    if ($ret == 0) {
        return $out[1];
    } else {
        return NULL;
    }
}
