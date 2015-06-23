<?php
	require_once("common.php");
	require_once("lib.php");
	require_once("config.php");
	start_session();

	$db_conn = db_connect($user,$passwd,$port,$dbname,$host);
	if(chksession($db_conn) == FALSE){
		sess_cookie_break();
		locationHTML($baseurl.$login_php,"");
		die();
	}

	$result = db_query($db_conn,"select * from $user_tbl ".db_limit_string(0,1));
	$rows = db_num_rows($result);
	if($rows > 0){
		$result = db_query($db_conn,"update $user_tbl set ip = 'logout'");
	}

	sess_break();

	html_head('ログアウト');
	echo '<script type="text/javascript">'."\n";
	echo '<!--'."\n";
	echo 'setTimeout("location.href=\''.$login_php.'\'",3000);'."\n";
	echo '//-->'."\n";
	echo '</script>'."\n";
	echo '<div align="center">';
	echo '<br style="line-height:32px">';
	echo 'ログアウトしました<br>';
	echo '<a href="'.$login_php.'">ログインページへ</a>';
	html_foot();
?>
