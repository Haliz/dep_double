<?
require_once (__DIR__.'/crest.php');


function stepRequest($method, $params = [])
{
static $fixTime;
// var_dump($method);
// echo '<br />';
// var_dump($params);
// echo '<br />';

// echo "До фиксации $fixTime";
// echo '<br />';

while(($per= microtime(true)-$fixTime)<0.5) {
usleep(100000);
// echo "Период $per";
// echo '<br />';
}
$fixTime = microtime(true);
// echo "Фиксация времени $fixTime";
// echo '<br />';
// echo '<br />';
return	CRest::call($method, $params);
}