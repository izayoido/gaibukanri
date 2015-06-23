<?php
	require_once("config.php");
	require_once("lib.php");
	require_once("common.php");
	$db_conn = db_connect($user,$passwd,$port,$dbname,$host);
	$result = db_query($db_conn,"select id from $channel_tbl order by id");
	echo "$result";//Resource id #6
	echo "aaa";
	$rows = db_num_rows($result);
	echo $rows;//19
	$result = db_query($db_conn,"select id from $channel_tbl where id = 19");
	echo $result;//Resource id #7
	echo "<pre>";
	var_dump($result);
	echo "</pre>";
?>