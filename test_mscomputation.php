<?php
require_once(__DIR__ . '/msazure.config.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}


// -------------------------------- //
// MS Search -- Computation のテスト

//$query = '小倉唯';
//$query = 'How many feet in 10 meters?';
$query = '10メートルは何フィート？';

$command = 'curl --header "Ocp-Apim-Subscription-Key: ' . MSSEARCH_KEY . '"'
         . ' -Ss "https://api.cognitive.microsoft.com/bing/v5.0/search?q='
         . urlencode($query) . '&mkt=ja-JP&setLang=JA"';
exec($command, $out, $ret);
//var_dump($out);
$searchres = json_decode($out[0], true);
var_dump($searchres);
