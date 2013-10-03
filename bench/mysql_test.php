<?php
/**
 * title:NGINX+PHP-FPM+SOCKET VS NGINX+PHP-FPM+TCP VS NGINX+APACHE+MOD_PHP
 * url:http://www.stutostu.com/?p=1228
 */


$dsn      = 'mysql:dbname=test;host=127.0.0.1;charset=utf8';
$user     = 'root';
$password = '';

try
{
    $db = new PDO($dsn, $user, $password);
}
catch (PDOException $e)
{
    exit($e->getMessage());
}

$sql = 'SELECT * FROM `user` LIMIT 5';
$sth = $db->prepare($sql);
$sth->execute();
print_r($sth->fetchAll());