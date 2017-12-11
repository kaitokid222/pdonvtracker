<?php

$mysql_host = "localhost";
$mysql_user = "root";
$mysql_pass = "";
$mysql_db   = "nvtracker";

//NEU PDO ! :D
$db_info = array( 
	"db_host" => "localhost", 
	"db_port" => "3306",
	"db_user" => "root",
	"db_pass" => "",
	"db_name" => "nvtracker",
	"db_charset" => "UTF-8"
);
$dsn = ["mysql:host=".$db_info['db_host'].';port='.$db_info['db_port'].';dbname='.$db_info['db_name'],$db_info['db_user'], $db_info['db_pass']];
?>
