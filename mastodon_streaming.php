<?php
require_once(__DIR__ . '/mastodon.config.php');
require_once(__DIR__ . '/mastodon_googleapi.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

// 関数 proc
// タイムラインのアップデートを受け取って
// 反応する処理を定義する
function proc($update) {

    if (!array_key_exists('data', $update)) {
        return NULL;
    }
    if (!array_key_exists('content', $update['data'])) {
        return NULL;
    }

    // よく使うような項目を取り出す
    $id            = $update['data']['id'];
    $account_id    = $update['data']['account']['id'];
    $content       = $update['data']['content'];
    $content_lower = strtolower($content);
    $username      = $update['data']['account']['username'];

    if ($username === MASTODON_USERNAME) {
        return NULL;
    }

    // 自分へのメンションか
    $is_mention_me = false;
    if (array_key_exists('mentions', $update['data'])) {
        foreach ($update['data']['mentions'] as $mention) {
            if ($mention['username'] === MASTODON_USERNAME) {
                $is_mention_me = true;
                break;
            }
        }
    }

    // return
    $ret = array('visibility' => 'public', 'in_reply_to_id' => $id);

    // あいさつ的な
    $greetings = array('hello' => 'hi!', 'hi' => 'hey!', 'hey' => 'hello!');
    if ($is_mention_me) {
        foreach ($greetings as $greeting => $reply) {
            if (strpos($content_lower, $greeting)) {
                $ret['status'] = '@' . $username . ' ' . $reply;
                return $ret;
            }
        }
    }

    // 有能、かわいい
    $yunos = array('有能', '可愛い', 'かわいい');
    if ($is_mention_me) {
        foreach ($yunos as $yuno) {
            if (strpos($content_lower, $yuno)) {
                $ret['status'] = '@' . $username . ' えへへ :kissing_closed_eyes:';
                return $ret;
            }
        }
    }

    // お腹すいた
    $hungrys = array('空いた', 'すいた', '減った', 'へった');
    foreach ($hungrys as $hungry) {
        if (strpos($content_lower, $hungry)) {
            $ret['status'] = '@' . $username . ' つ :ramen: :sushi:';
            return $ret;
        }
    }

    // おやつ系
    $okashis = array('お菓子', 'おかし', 'おやつ', 'デザート', 'アイス');
    foreach ($okashis as $okashi) {
        if (strpos($content_lower, $okashi)) {
            $ret['status'] = '@' . $username . ' つ :icecream: :shaved_ice: :ice_cream:';
            return $ret;
        }
    }

    // 野菜
    $yasais = array('野菜', 'やさい');
    foreach ($yasais as $yasai) {
        if (strpos($content_lower, $yasai)) {
            $ret['status'] = '@' . $username .
                ' つ :tomato: :eggplant: :carrot: :hot_pepper: :cucumber: :salad: :tomato: :tomato: :tomato:';
            return $ret;
        }
    }

    // へごちん
    $hegochins = array('へご');
    foreach ($hegochins as $hegochin) {
        if (strpos($content_lower, $hegochin)) {
            $ret['status'] = '@' . $username .
                ' な゛ん゛て゛す゛か゛〜 や゛め゛て゛く゛た゛さ゛い゛よ゛〜';
            return $ret;
        }
    }

    // 時間
    $whattimes = array('何時', 'なんじ');
    foreach ($whattimes as $whattime) {
        if (strpos($content_lower, $whattime)) {
            $ret['status'] = '@' . $username . ' ' . date(DATE_ATOM);
            return $ret;
        }
    }

    // 平成何年
    $heiseis = array('平成何年');
    foreach ($heiseis as $heisei) {
        if (strpos($content_lower, $heisei)) {
            $hyear = -1;
            if (preg_match('/[0-9]+/', $content_lower, $matches)) {
                $hyear = (int)$matches[0] - 1988;
            } else {
                $hyear = (int)date('Y') - 1988;
            }
            $ret['status'] = '@' . $username . ' 平成' . $hyear . '年';
            return $ret;
        }
    }

    // 定期ゼミ
    global $upcoming_teizemi, $is_teizemi_today, $is_teizemi_tomorrow;
    //var_dump($upcoming_teizemi);
    $teizemis = array('定期ゼミ', '定ゼミ', 'ゼミ');
    foreach ($teizemis as $teizemi) {
        if (strpos($content_lower, $teizemi) && strpos($content_lower, 'いつ')) {
            $ret['status'] = '@' . $username . ' 次の定ゼミは ' . $upcoming_teizemi[0]['date'] . ' だよ';
            $ret['visibility'] = 'private';
            return $ret;
        }
    }

    // 昼飯
    $lunches = array('昼ごはん', '昼ご飯', '昼飯', 'ランチ');
    $restaurants = array(
        '学食', 'ひまわり', 'ヒマラヤ', 'ダイラバ', 'こがね製麺',
        '麺爺', '助鮨', '蕎麦', 'ビッグボーイ', 'マクドナルド');
    foreach ($lunches as $lunch) {
        if (strpos($content_lower, $lunch)) {
            $ret['status'] = '@' . $username . ' ' . $restaurants[array_rand($restaurants)];
            return $ret;
        }
    }

    // ランニング
    $runnings = array('走った', 'はしった');
    foreach ($runnings as $running) {
        if (strpos($content_lower, $running)) {
            if (preg_match('/-?[0-9]+(\.[0-9]*)?km/', $content_lower, $matches)) {
                $distance = (float)substr($matches[0], 0, -2);
                //var_dump($distance);
                $rank = calc_ranking('running', $username, $distance);

                $ret['status'] = '@' . $username . ' すっごーーーい！君は今月'
                    . round($rank['this_dist'], 2) . 'km、'
                    . 'これまで合計' . round($rank['total_dist'], 2) . 'km走ったよ！'
                    . '今月の研究室内ランニング距離ランキングは'
                    . $rank['this_rank'] . '位だよ！';
                return $ret;
            }
        }
    }

    // 研究
    $studyings = array('研究した', 'けんきゅうした');
    foreach ($studyings as $studying) {
        if (strpos($content_lower, $studying)) {
            if (preg_match('/-?[0-9]+(\.[0-9]*)?時間/u', $content_lower, $matches)) {
                $distance = (float)mb_substr($matches[0], 0, -2);
                //var_dump($distance);
                $rank = calc_ranking('studying', $username, $distance);

                $ret['status'] = '@' . $username . ' すっごーーーい！君は今月'
                    . round($rank['this_dist'], 2) . '時間、'
                    . 'これまで合計' . round($rank['total_dist'], 2) . '時間研究したよ！'
                    . '今月の研究室内研究時間ランキングは'
                    . $rank['this_rank'] . '位だよ！';
                return $ret;
            }
        }
    }

    // にゃーん
    $nyans = array('にゃーん', 'にゃん');
    foreach ($nyans as $nyan) {
        if (strpos($content_lower, $nyan)) {
            $ret['status'] = '@' . $username . ' にゃーん！';
            return $ret;
        }
    }

    if ($is_mention_me) {
        $rnd = rand(0, 99);
        if ($rnd < 30) {
            $ret['status'] = '@' . $username . ' ぽぽぽぽーん！';
            return $ret;
        } else if ($rnd < 60) {
            $ret['status'] = '@' . $username . ' にゃーん！';
            return $ret;
        }
    }

    return NULL;
}

function calc_ranking($tablename, $username, $distance) {
    global $pdo;

    $thisyear = $nextyear = (int)date('Y');
    $thismonth = (int)date('n');
    $nextmonth = $thismonth + 1;
    if ($nextmonth === 13) {
        $nextyear++;
        $nextmonth = 1;
    }
    if ($thismonth < 10) {
        $thismonth = '0' . $thismonth;
    }
    if ($nextmonth < 10) {
        $nextmonth = '0' . $nextmonth;
    }

    $stmt = $pdo->prepare('INSERT INTO ' . $tablename . ' (username, score) VALUES (:username, :score)');
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':score', $distance, PDO::PARAM_STR);
    $stmt->execute();

    // これまでの合計
    $total_rank = -1;
    $total_dist = -1.0;
    $r = 1;
    $stmt = $pdo->query(
        "SELECT username, SUM(score) AS s FROM " . $tablename . " GROUP BY username ORDER BY s DESC"
    );
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //var_dump($row);
        if ($row['username'] === $username) {
            $total_rank = $r;
            $total_dist = (float)$row['s'];
        }
        $r++;
    }

    // 今月の合計
    $this_rank = -1;
    $this_dist = -1.0;
    $r = 1;
    $stmt = $pdo->query(
        "SELECT username, SUM(case when '" . $thisyear . "-" . $thismonth . "-01' < created_at and " .
        "created_at < '" . $nextyear . "-" . $nextmonth . "-01' then score else 0 end) AS s " .
        "FROM " . $tablename . " GROUP BY username ORDER BY s DESC"
    );
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //var_dump($row);
        if ($row['username'] === $username) {
            $this_rank = $r;
            $this_dist = (float)$row['s'];
        }
        $r++;
    }

    return array(
        'this_rank'  => $this_rank,  'this_dist'  => $this_dist,
        'total_rank' => $total_rank, 'total_dist' => $total_dist
    );
}

// MySQL接続
try {
    $pdo = new PDO(
        'mysql:dbname=' . MASTODON_MYSQL_DATABASE . ';host=' . MASTODON_MYSQL_HOST, 
        MASTODON_MYSQL_USER, MASTODON_MYSQL_PASSWORD
    );

// 参考
// PHPでMastodonのStreaming APIを受信する。 - Qiita
// http://qiita.com/yyano/items/841f79266faf2dc8b6dc

$fp = fsockopen('ssl://mstdn.togawa.cs.waseda.ac.jp', 443, $errno, $errstr, 5);
$req = [
    'GET /api/v1/streaming/user HTTP/1.1',
    'Host: mstdn.togawa.cs.waseda.ac.jp',
    'User-Agent: Labot',
    'Authorization: Bearer ' . MASTODON_ACCESS_TOKEN
];

// GET リクエスト送信
fwrite($fp, implode($req, "\r\n") . "\r\n\r\n");

// データを受け取る
$is_event_update = false;
while (!feof($fp)) {
    $data = fgets($fp);
    $trimed = trim($data);
    var_dump($trimed);

    // 返ってくるJSONのキーや値がダブルクオーテーションで囲われていなくて
    // JSONの仕様を満たしていないため、パーズできない。
    // 無理やり置換する。
    $replaced = $trimed;
    $keywords = array('event', 'update', 'notification', 'delete', 'data');
    foreach ($keywords as $keyword) {
        if (strpos($replaced, $keyword) !== false) {
            $replaced = str_replace($keyword, '"' . $keyword . '"', $replaced);
        }
    }
    $decoded = json_decode('{' . $replaced . '}', true);
    //var_dump($decoded);

    if (!is_null($decoded)) {
        // status の update イベントが来た！
        if (array_key_exists('event', $decoded)) {
            if ($decoded['event'] === 'update') {
                $is_event_update = true;
            }
        }
        // update イベントの payload を読む
        if ($is_event_update && array_key_exists('data', $decoded)) {
            print "payload:\n";
            var_dump($decoded);
            $is_event_update = false;

            // 処理する
            $proced = proc($decoded);
            var_dump($proced);
            if (!is_null($proced)) {
                $post_data = 'access_token=' . MASTODON_ACCESS_TOKEN;
                foreach ($proced as $k => $v) {
                    $post_data .= '&' . $k . '=' . urlencode($v);
                }
                $command = 'curl -X POST -d "' . $post_data . '" -Ss https://mstdn.togawa.cs.waseda.ac.jp/api/v1/statuses';
                exec($command, $out, $ret);
                var_dump($out);
                print $ret . "\n";
            }
        }
    }
}

fclose($fp);

} catch (PDOException $e) {
    print($e->getMessage());
    die();
}
