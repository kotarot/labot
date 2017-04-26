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

    // 固定キーワードと返答の定義
    $static_reactions = array(
        // あいさつ的な
        array(
            'keywords'  => array('hello', 'hi', 'hey'),
            'reactions' => array('hello', 'hi', 'hey'),
            'cond'      => $is_mention_me
        ),
        // 有能、かわいい
        array(
            'keywords'  => array('有能', '可愛い', 'かわいい'),
            'reactions' => array('えへへ :kissing_closed_eyes:'),
            'cond'      => $is_mention_me
        ),
        // お腹すいた
        array(
            'keywords'  => array('空いた', 'すいた', '減った', 'へった'),
            'reactions' => array('つ :ramen: :sushi:'),
            'cond'      => true
        ),
        // おやつ系
        array(
            'keywords'  => array('お菓子', 'おかし', 'おやつ', 'デザート', 'アイス'),
            'reactions' => array('つ :icecream: :shaved_ice: :ice_cream:'),
            'cond'      => true
        ),
        // 野菜
        array(
            'keywords'  => array('野菜', 'やさい'),
            'reactions' => array(
                'つ :tomato: :eggplant: :carrot: :hot_pepper: :cucumber: :salad: :tomato: :tomato: :tomato:'),
            'cond'      => true
        ),
        // 果物
        array(
            'keywords'  => array('果物', 'くだもの', 'フルーツ'),
            'reactions' => array(
                'つ :grapes: :melon: :watermelon: :tangerine: :lemon: :banana: :green_apple: :pear: :peach: :cherries: :strawberry: :kiwi: :banana: :banana: :banana: :banana: :banana: :banana:'),
            'cond'      => true
        ),
        // へごちん
        array(
            'keywords'  => array('へご'),
            'reactions' => array('な゛ん゛て゛す゛か゛〜 や゛め゛て゛く゛た゛さ゛い゛よ゛〜'),
            'cond'      => true
        ),
        // 時間
        array(
            'keywords'  => array('何時', 'なんじ'),
            'reactions' => array(date(DATE_ATOM)),
            'cond'      => true
        ),
        // 昼飯
        array(
            'keywords'  => array('昼ごはん', '昼ご飯', '昼飯', 'ランチ', 'ごはん', 'ご飯'),
            'reactions' => array(
                '学食', 'ひまわり', 'ヒマラヤ', 'ダイラバ', 'こがね製麺',
                '助鮨', '蕎麦', 'ビッグボーイ', 'マクドナルド',
                '麺爺 :ramen: :older_man:', '麺爺 :ramen: :older_man:', '麺爺 :ramen: :older_man:',
                '麺爺 :ramen: :older_man:', '麺爺 :ramen: :older_man:', '麺爺 :ramen: :older_man:',
                '麺爺 :ramen: :older_man:', '麺爺 :ramen: :older_man:', '麺爺 :ramen: :older_man:',
                '麺爺 :ramen: :older_man:', '麺爺 :ramen: :older_man:', '麺爺 :ramen: :older_man:'),
            'cond'      => true
        ),
        // にゃーん
        array(
            'keywords'  => array('にゃーん', 'にゃん'),
            'reactions' => array('にゃーん！'),
            'cond'      => true
        )
    );
    foreach ($static_reactions as $static_reaction) {
        $reply = contains_and_reply($content_lower,
            $static_reaction['keywords'], $static_reaction['reactions'], $static_reaction['cond']);
        if ($reply) {
            $ret['status'] = '@' . $username . ' ' . $reply;
            return $ret;
        }
    }

    // 平成何年
    $heiseis = array('平成何年');
    foreach ($heiseis as $heisei) {
        if (strpos($content_lower, $heisei) !== false) {
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
        if (strpos($content_lower, $teizemi) !== false && strpos($content_lower, 'いつ') !== false) {
            $ret['status'] = '@' . $username . ' 次の定ゼミは ' . $upcoming_teizemi[0]['date'] . ' だよ';
            $ret['visibility'] = 'private';
            return $ret;
        }
    }

    // ランニング
    $runnings = array('走った', 'はしった');
    foreach ($runnings as $running) {
        if (strpos($content_lower, $running) !== false) {
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
        if (strpos($content_lower, $studying) !== false) {
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

    // その他返信
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

// 関数 contains_and_reply
// $content に $keywords のいずれかが含まれている場合、
// $reactions の中からランダムに返す。
// ただし、$cond が真の場合のみ。
function contains_and_reply($content, $keywords, $reactions, $cond = false) {
    if ($cond) {
        foreach ($keywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                return $reactions[array_rand($reactions)];
            }
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

    $fp = fsockopen('ssl://' . MASTODON_HOST, 443, $errno, $errstr, 5);
    $req = [
        'GET /api/v1/streaming/user HTTP/1.1',
        'Host: ' . MASTODON_HOST,
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
                    $command = 'curl -X POST -d "' . $post_data
                             . '" -Ss https://' . MASTODON_HOST . '/api/v1/statuses';
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
