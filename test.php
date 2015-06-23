<?php
	require_once("config.php");
	require_once("lib.php");
	require_once("common.php");
	$result = db_query($db_conn,"select id from $channel_tbl order by id");
	echo $result;
?>