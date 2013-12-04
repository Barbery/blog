<?php
$post_url = 'http://127.0.0.1:1218/?name=xoyo&opt=put&data=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
$get_url = 'http://127.0.0.1:1218/?name=xoyo&opt=get';

$start = microtime(TRUE);
$i = 100000;
while ($i-- > 0)
{
    curl_get($post_url);
}

$usage = microtime(TRUE) - $start;
echo 'POST: use time: ', $usage, '. Time per request:', 100000/$usage, PHP_EOL;


$start = microtime(TRUE);
$i = 100000;
while ($i-- > 0)
{
    curl_get($post_url);
}

$usage = microtime(TRUE) - $start;
echo 'GET: use time: ', $usage, '. Time per request:', 100000/$usage, PHP_EOL;



function curl_get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);

    //设置超时时间为3s
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 3);
    $result = curl_exec($ch);
    curl_close($ch);
}

//Result:
//POST: use time: 37.225346803665. Time per request:2686.3416619709
//GET: use time: 34.941364049911. Time per request:2861.9374978366
