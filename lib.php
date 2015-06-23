<?php
function	colorTxt($txt,$col){
	return("<span style=\"color:$col;font-weight:bold;\">$txt</span>");
}
function	db_connect($user,$passwd,$port,$dbname,$host){
	GLOBAL	$db_type;
	$result = false;
	if($db_type == 0){	// MySQL
		$result = mysql_connect("$host","$user","$passwd");
		mysql_query("SET NAMES 'utf8';");
		if($result != FALSE){
			if(mysql_select_db("$dbname") == FALSE){
				$result = FALSE;
			}
		}
	}else{				// PostgreSQL
		$db_connect_str = "user=$user password=$passwd host=$host dbname=$dbname";
		if($port != null){
			$db_connect_str .= " port=$port";
		}
		$result = pg_connect("$db_connect_str");
		pg_set_client_encoding($result,"UTF-8");
	}
	if($result == FALSE){echo "DBへの接続に失敗しました";}
	return($result);
}
function	db_query($conn,$query){
	GLOBAL	$db_type;
	$result = false;
	if($db_type == 0){	// MySQL
		mysql_query("SET NAMES 'utf8';");
		$result = mysql_query("$query",$conn);
	}else{				// PostgreSQL
		$result = pg_query($conn,"$query");
	}
	return($result);
}
function	db_num_rows($res){
	GLOBAL	$db_type;
	$result = false;
	if($db_type == 0){	// MySQL
		$result = mysql_num_rows($res);
	}else{				// PostgreSQL
		$result = pg_num_rows($res);
	}
	return($result);
}
function	db_seek($res,$pos){
	GLOBAL	$db_type;
	$result = false;
	if($db_type == 0){	// MySQL
		$result = mysql_data_array($res,$pos);
	}else{				// PostgreSQL
		$result = pg_result_seek($res,$pos);
	}
	return($result);
}
function	db_fetch_array($res){
	GLOBAL	$db_type;
	$result = false;
	if($db_type == 0){	// MySQL
		$result = mysql_fetch_array($res);
	}else{				// PostgreSQL
		$result = pg_fetch_array($res);
	}
	return($result);
}
function	db_exist_table($conn,$tablename){
	GLOBAL	$db_type;
	$result = false;
	if($db_type == 0){	// MySQL
		$result = mysql_query("show tables like '$tablename';",$conn);
	}else{				// PostgreSQL
		$result = pg_query($conn,"select tablename from pg_tables where tablename = '$tablename';");
		$result = pg_num_rows($result);
	}
	return($result);
}
function	db_exist_seq($conn,$seqname){
	GLOBAL	$db_type;
	$result = false;
	if($db_type == 0){	// MySQL
	}else{				// PostgreSQL
		$result = pg_query($conn,"select relname from pg_statio_user_sequences where relname = '$seqname';");
		$result = pg_num_rows($result);
	}
	return($result);
}
function	db_escape_string($s){
	GLOBAL	$db_type;
	$result = false;
	if($db_type == 0){	// MySQL
		$result = mysql_escape_string($s);
	}else{				// PostgreSQL
		$result = pg_escape_string($s);
	}
	return($result);
}
function	db_limit_string($offset,$limit){
	GLOBAL	$db_type;
	$result = false;
	if($db_type == 0){	// MySQL
		$result = "limit $offset, $limit";
	}else{				// PostgreSQL
		$result = "offset $offset limit $limit";
	}
	return($result);
}
function	mkDateFormSub($v1,$v2,$sel){
	echo '<option value="-1"></option>';
	for($i = $v1;$i < $v2;$i ++){
		$s = ($i == $sel)? " selected":"";
		echo "<option value=\"$i\"$s>$i</option>";
	}
}
function	mkDateForm($name_base,$date){
	$date = intval($date);
	if(is_int($date) && $date > 0){
		$s = explode(" ",date("Y n j G i",$date));
		$yy = $s[0];
		$mm = $s[1];
		$dd = $s[2];
		$hour = $s[3];
		$min = $s[4];
	}else{
		$yy = -1;
		$mm = -1;
		$dd = -1;
		$hour = -1;
		$min = -1;
	}
	$name = $name_base . "_yy";
	echo "<select name=\"$name\">";
	mkDateFormSub(2007,2038,$yy);
	echo "</select>年";

	$name = $name_base . "_mm";
	echo "<select name=\"$name\">";
	mkDateFormSub(1,13,$mm);
	echo "</select>月";

	$name = $name_base . "_dd";
	echo "<select name=\"$name\">";
	mkDateFormSub(1,32,$dd);
	echo "</select>日";

	$name = $name_base . "_hour";
	echo "<select name=\"$name\">";
	mkDateFormSub(0,24,$hour);
	echo "</select>時";

	$name = $name_base . "_min";
	echo "<select name=\"$name\">";
	mkDateFormSub(0,60,$min);
	echo "</select>分";
}
function	date2time($yy,$mm,$dd,$hour,$min,$sec){
	if(!(is_int($yy) && is_int($mm) && is_int($dd) && is_int($hour) && is_int($min) && is_int($sec))){return(0);}
	$result = 0;
	if(1970 <= $yy && $yy < 2038 && 1 <= $mm && $mm <= 12 && 1 <= $dd && $dd <=31){
		$result = checkdate($mm,$dd,$yy);
		if($result == FALSE){
			return(0);
		}
		if(0 <= $hour && $hour < 24 && 0 <= $min && $min < 60 && 0 <= $sec && $sec < 60){
			$result = mktime($hour,$min,$sec,$mm,$dd,$yy);
		}else{
			return(0);
		}
	}
	return($result);
}
function	locationHTML($uri,$prm){
	if(!($prm == "")){
		$uri .= "?".str_replace( array( "\r", "\n" ), "", $prm );
	}
	header("Location: $uri");
}
function	sess_cookie_break(){
	// ローカルのセッションクッキーを削除
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}
}
function	sess_break(){
	sess_cookie_break();
	$oldID = session_id();
	$_SESSION = array();
	session_destroy();
	// サーバのセッションファイルを削除
	if(ini_get('session.save_handler') == "files"){
		@unlink(session_save_path() . "/sess_$oldID");
	}
}
function	chg_session($conn){
	GLOBAL	$user_tbl;
	// 旧セッション情報を退避
	$savevalue = $_SESSION; 

	sess_break();

	// 新セッションを作成し復帰
	session_start(); 
	session_regenerate_id(); 
	$_SESSION = $savevalue;
	// 最終操作時刻を更新
	$lastop = time();
	$result = db_query($conn,"update $user_tbl set lastop = $lastop");
	return($result);
}
function	start_session(){
	ini_set('session.auto_start','0');
	ini_set('session.use_only_cookies','1');
	ini_set('session.use_cookies','1');
	@ini_set('session.use_trans_sid','0');
	@ini_set('session.cookie_httponly','1');

	session_start();
}
function	chksession($conn){
	GLOBAL	$user_tbl;
	if(!isset($_SERVER['REMOTE_ADDR'])){return(true);}	// IPが取得できない場合
	$ip = $_SERVER["REMOTE_ADDR"];
	if(!isset($_SESSION['sess_ip']) || !($_SESSION['sess_ip'] == $ip)){
		return(false);
	}

	$result = db_query($conn,"select * from $user_tbl ".db_limit_string(0,1));
	$rows = db_num_rows($result);
	if($rows <= 0){return(false);}
	$row = db_fetch_array($result);
	if($row['ip'] == 'logout'){return(false);}
	if($row['lastop']+3600 < time()){return(false);}
	return($ip == $row['ip']);
}
?>
