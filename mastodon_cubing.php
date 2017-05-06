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

// スクランブル投稿履歴を記録
function save_scramble($pdo, $status_id, $scramble) {
    $stmt = $pdo->prepare('INSERT INTO cubing_333_scrambles (status_id, scramble) VALUES (:status_id, :scramble)');
    $stmt->bindParam(':status_id', $status_id, PDO::PARAM_INT);
    $stmt->bindParam(':scramble', $scramble, PDO::PARAM_STR);
    $stmt->execute();
}

// スクランブルの投稿かどうか
function is_scramble_status_id($pdo, $status_id) {
    $stmt = $pdo->prepare('SELECT id FROM cubing_333_scrambles WHERE status_id = :status_id');
    $stmt->bindParam(':status_id', $status_id, PDO::PARAM_INT);
    $stmt->execute();
    $counts = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //var_dump($row);
        $counts++;
    }
    if (0 < $counts) {
        return true;
    } else {
        return false;
    }
}

// 記録を登録＆ランキング
function calc_cubing_ranking($pdo, $username, $scramble_status_id, $score) {
    $stmt = $pdo->prepare('INSERT INTO cubing_333 (username, scramble_status_id, score) '
          . 'VALUES (:username, :scramble_status_id, :score)');
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':scramble_status_id', $scramble_status_id, PDO::PARAM_INT);
    $stmt->bindParam(':score', $score, PDO::PARAM_STR);
    $stmt->execute();

    // これまでの記録
    $total_rank = -1;
    $total_score = -1.0;
    $r = 1;
    $stmt = $pdo->query(
        "SELECT username, MIN(score) AS s FROM cubing_333 GROUP BY username ORDER BY s ASC"
    );
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //var_dump($row);
        if ($row['username'] === $username) {
            $total_rank = $r;
            $total_score = (float)$row['s'];
        }
        $r++;
    }

    // このスクランブルの記録
    $this_rank = -1;
    $this_score = -1.0;
    $r = 1;
    $stmt = $pdo->query(
        "SELECT username, MIN(case when scramble_status_id = " . $scramble_status_id . " then score else 0 end) AS s " .
        "FROM cubing_333 GROUP BY username ORDER BY s ASC"
    );
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //var_dump($row);
        if ($row['username'] === $username) {
            $this_rank = $r;
            $this_score = (float)$row['s'];
        }
        $r++;
    }

    return array(
        'this_rank'  => $this_rank,  'this_score'  => $this_score,
        'total_rank' => $total_rank, 'total_score' => $total_score
    );
}
