<?php

if(!isset($_COOKIE["YDZCMID"]))
{
    echo "no mapping";
    //mappingʧ��
    return;
}
else
{
    //mapping�ɹ�
    $dbconnect = mysql_connect('www.yundouzi.com:3306','cm_admin','cm_admin') or die('Could not connect: ' . mysql_error());
    mysql_select_db("cm") or die("Unable to select database!");
    
    //����������ѯ
    $query = sprintf("SELECT * FROM `map_hy` WHERE `domain` LIKE '%s'", $_SERVER['SERVER_NAME']);
    $result = mysql_query($query) or die("Error in query: $query. ".mysql_error());
    $info = mysql_fetch_array($result);
    if(empty($info)){
        //����һ��map
        echo "add mapping";
        $query = sprintf("INSERT INTO `cm`.`map_hy` (`cookie`, `domain`, `hy_cookie`) VALUES ('%s', '%s', '%s');",
            $_COOKIE["YDZCMID"],
            $_SERVER['SERVER_NAME'],
            $_GET["allyes_id"]);        
    }else{
        //����һ������map
        echo "update mapping";
        $query = sprintf("UPDATE `cm`.`map_hy` SET `cookie` = '%s', `hy_cookie` = '%s' WHERE `map_hy`.`domain` = '%s';",
            $_COOKIE["YDZCMID"],
            $_GET["allyes_id"],
            $_SERVER['SERVER_NAME']);
    }
    $result = mysql_query($query) or die("Error in query: $query. ".mysql_error());
    
    mysql_free_result($result);
    mysql_close($dbconnect);
}

?>