<?php
$hr = httpsqs_connect("127.0.0.1", 1218);
$start = microtime(TRUE);
$i = 100000;
while ($i-- > 0)
{
    $data = httpsqs_get($hr, 'xoyo', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
}

$usage = microtime(TRUE) - $start;
echo 'POST: use time: ', $usage, '. Time per request:', 100000/$usage, PHP_EOL;



$start = microtime(TRUE);
$i = 100000;
while ($i-- > 0)
{
    $data = httpsqs_get($hr, 'xoyo');
}

$usage = microtime(TRUE) - $start;
echo 'GET: use time: ', $usage, '. Time per request:', 100000/$usage, PHP_EOL;

//Result:
//POST: use time: 31.089762926102. Time per request:3216.4928448536
//GET: use time: 27.732163906097. Time per request:3605.9212811018
