<?php

/**
 * title:NGINX+PHP-FPM+SOCKET VS NGINX+PHP-FPM+TCP VS NGINX+APACHE+MOD_PHP
 * url:http://www.stutostu.com/?p=1228
 */


$a = 0;
for($i=0; $i < 10000; $i++)
{
    $a += $i;
}
echo $a;