<?php

$post_url = 'http://127.0.0.1:1218/?name=xoyo&opt=put&data=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
$get_url = 'http://127.0.0.1:1218/?name=xoyo&opt=get';

$start = microtime(TRUE);
$i = 100000;
while ($i-- > 0)
{
    curl_get($post_url.$i);
}

$usage = microtime(TRUE) - $start;
echo 'POST: use time: ', $usage, '. Time per request:', 100000/$usage, PHP_EOL;


$start = microtime(TRUE);
$i = 100000;
while ($i-- > 0)
{
    curl_get($get_url);
}

$usage = microtime(TRUE) - $start;
echo 'GET: use time: ', $usage, '. Time per request:', 100000/$usage, PHP_EOL;


function curl_get($url)
{
    static $ch = NULL;
    if ($ch === NULL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    $result1 = curl_exec($ch);
    // without close curl
}


//Resutl:
//POST: use time: 23.697576999664. Time per request:4219.8407036051
//GET: use time: 19.489132881165. Time per request:5131.0646096854
