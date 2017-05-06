<?php
require_once(__DIR__ . '/mastodon.config.php');
require_once(__DIR__ . '/mastodon_cubing.php');

try {
    $pdo = new PDO(
        'mysql:dbname=' . MASTODON_MYSQL_DATABASE . ';host=' . MASTODON_MYSQL_HOST, 
                          MASTODON_MYSQL_USER, MASTODON_MYSQL_PASSWORD
    );

    $scramble = get_scramble_333();
    if ($scramble) {
        $text = '3x3x3スクランブルだよ！タイムを返信してね: '
              . $scramble;
        $command = 'curl -X POST -d "access_token=' . MASTODON_ACCESS_TOKEN . '&status=' . urlencode($text)
                 . '&visibility=public" -Ss https://' . MASTODON_HOST . '/api/v1/statuses';
        exec($command, $out, $ret);
        var_dump($out);
        print $ret . "\n";

        $status_json = json_decode($out[0], true);
        $status_id = $status_json['id'];
        if ($ret == 0) {
            save_scramble($pdo, $status_id, $scramble);
        }
    }

} catch (PDOException $e) {
    print($e->getMessage());
    die();
}
