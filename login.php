<?php
	require_once("common.php");
	require_once("lib.php");
	require_once("config.php");
	start_session();

	// データベースへ接続
	$db_conn = db_connect($user,$passwd,$port,$dbname,$host);
	$result = db_query($db_conn,"select * from $user_tbl ".db_limit_string(0,1));
	$rows = db_num_rows($result);

	$ip = $_SERVER["REMOTE_ADDR"];
	$id = db_escape_string($_POST['id']);
	$pass = db_escape_string($_POST['pass']);

	$msg = "";
	if($rows < 1){	// ＩＤ＆パス未設定
		$msg  = "ＩＤとパスワードを設定してください<br>";
		$msg .= '(４文字以上、半角英数字および[_][-][+]が利用可能)';
		$id_len = strlen($id);
		$pass_len = strlen($pass);
		if(preg_match('/^[a-zA-Z0-9\_\-\+]{4,}$/',$id)
		&& preg_match('/^[a-zA-Z0-9\_\-\+]{4,}$/',$pass)){
			$result = db_query($db_conn,"insert into $user_tbl (id,pass,ip) values('$id','$pass','$ip')");
			$result = db_query($db_conn,"select * from $user_tbl ".db_limit_string(0,1));
			$rows = db_num_rows($result);
			if($rows > 0){
				$msg = "";
				$_SESSION['sess_ip'] = $ip;
			}
		}else{
			if($id_len > 0 && $pass_len > 0){
				if($id_len < 4 && $pass_len < 4){
					$msg = '<font color="red">４文字以上の長さが必要です</font><br>'.$msg;
				}else{
					$msg = '<font color="red">使用できない文字が使われています</font><br>'.$msg;
				}
			}
		}
	}
	if($rows > 0){
		$row = db_fetch_array($result);
		if(!($id == "" || $pass == "")){
			$sess_err = '';
			if(isset($_SESSION['sess_ip'])){
				$sess_err = explode(' ',$_SESSION['sess_ip'],2);
			}
			if($sess_err[0] == 'SHJ'){
				if(intval($sess_err[1]) <= time()){
					$sess_err = '';
					$_SESSION['sess_ip'] = 3;
				}else{
					$_SESSION['sess_ip'] = 'SHJ '.(time()+600);
				}
			}else{
				$sess_err = '';
			}
			if($sess_err == ''){
				if($row['id'] == $id && $row['pass'] == $pass){	// 認証成功
					$lastop = time();
					$result = db_query($db_conn,"update $user_tbl set ip = '$ip',lastop = $lastop");
					$_SESSION['sess_ip'] = $ip;
					locationHTML($baseurl.$toppage_php,"");
					die();
				}else{
					$msg = "ＩＤまたはパスワードが違います";
					if(isset($_SESSION['sess_ip'])){
						if($_SESSION['sess_ip'] < 3){
							$_SESSION['sess_ip'] += 1;
						}else{
							$_SESSION['sess_ip'] = 'SHJ '.(time()+600);
						}
					}else{
						$_SESSION['sess_ip'] = 2;
					}
				}
			}
		}
	}
	html_head('ログイン');
?>
<div style="text-align:center">
<br style="line-height:32px">
<?php echo $msg;?><br>
<form method="POST">
<table style="margin-left:auto;margin-right:auto;">
<tr style="text-align:left;"><td>ＩＤ</td><td><input type="text" name="id"></td></tr>
<tr style="text-align:left;"><td>パスワード</td><td><input type="password" name="pass"></td></tr>
</table>
<input type="submit" value="送信">
</form>
</div>
<?php
	html_foot();
?>