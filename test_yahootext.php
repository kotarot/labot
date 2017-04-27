<?php
require_once(__DIR__ . '/yahoodev.config.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}


// -------------------------------- //
// テキスト解析のテスト

$sentence = '辛いさんがでたか';
//$sentence = '文章は趣旨一つに著作する文献なでため、著作するれ他を受信者可能の研究SAをするれがもしで、一つのライセンスは、侵害しれ否と執筆ありこととして手続明確ますですばくださいますない。一方、法典の検証物は、文の向上なら一定慎重ですユースに引用さ、同じペディアがして台詞で括弧することが検証されある。';

$xmlstr = file_get_contents('https://jlp.yahooapis.jp/MAService/V1/parse?' .
    'appid=' . YAHOODEV_APPID . '&results=ma&sentence=' . urlencode($sentence));
$xmlobj = new SimpleXMLElement($xmlstr);
var_dump($xmlobj);
var_dump((int)($xmlobj->ma_result->total_count));
