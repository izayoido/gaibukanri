<?php
	$channel_php = 'channel.php';
	$channeldetails_php = 'channeldetails.php';
	$advertise_php = 'advertise.php';
	$advertisedetails_php = 'advertisedetails.php';
	$group_php = 'group.php';
	$login_php = 'login.php';
	$logout_php = 'logout.php';
	$account_php = 'account.php';
	$toppage_php = 'toppage.php';
	$extend_php = 'extend.php';
function	html_head($title){
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // 過去の日付
	header("Content-Type: text/html; charset=UTF-8");
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">'."\n";
	echo '<html lang="ja"><head>'."\n";
	echo '<link rel="stylesheet" type="text/css" href="viper.css">';
	echo '<meta name="robots" content="noindex,nofollow">'."\n";
	echo '<meta http-equiv="Pragma" content="no-cache">'."\n";
	echo '<meta http-equiv="Cache-Control" content="no-cache">'."\n";
	echo '<meta http-equiv="expires" content="0">'."\n";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'."\n";
	echo '<title>Viper - '.$title.'</title></head>'."\n";
	echo '<body>'."\n";
	echo '<div class="container">'."\n";
}
function	headline_menu(){
	GLOBAL	$channel_php;
	GLOBAL	$channeldetails_php;
	GLOBAL	$advertise_php;
	GLOBAL	$advertisedetails_php;
	GLOBAL	$group_php;
	GLOBAL	$logout_php;
	GLOBAL	$account_php;
	GLOBAL	$toppage_php;
	GLOBAL	$extend_php;
	echo '<div class="menu">';
	echo '<table><tr>';
	echo '<td><a href="'.$toppage_php.'">ＴＯＰ</a></td>';
	echo '<td><a href="'.$advertise_php.'">広告</a></td>';
	echo '<td><a href="'.$channel_php.'">チャネル</a></td>';
	echo '<td><a href="'.$group_php.'">グループ</a></td>';
	echo '<td><a href="'.$extend_php.'">EXTEND</a></td>';
	echo '<td><a href="'.$account_php.'">アカウント</a></td>';
	echo '<td><a href="'.$logout_php.'">ログアウト</a></td>';
	echo '</tr></table>';
	echo '</div>'."\n";
}
function	html_foot(){
	echo '</body>';
	echo '</html>';
}
function	quicktag($conn){
	GLOBAL	$user_tbl;
	echo '<script type="text/JavaScript">'."\n";
	echo '<!--'."\n";
	echo 'function	insert(txt,aid,iid){'."\n";
	echo 'var c = txt.split("/");'."\n";
	echo 'var durl = c[0]+"//"+c[2]+"/"+c[3]+iid;'."\n";
	echo '	txt = txt + aid + "&" + iid;'."\n";
	echo '	document.getElementById(\'tag\').value = "<a href=\""+txt+"\" target=\"_blank\" onmouseover=\"window.status=\'"+durl+"\';return true;\" onmouseout=\"window.status=\'\';\">※ここを任意に編集※</a>";'."\n";
	echo '}'."\n";
	echo '//-->'."\n";
	echo '</script>'."\n";

	$result = db_query($conn,"select * from $user_tbl ".db_limit_string(0,1));
	$row = db_fetch_array($result);
	echo '<table>';
	echo '<caption align="left">クイックタグ</caption>';
	// infotop
	echo '<tr>';
	echo '<td><form><a href="http://www.infotop.jp/two.php?pid=6271" target="_blank" onFocus="window.status=\'http://www.infotop.jp/\';return true;" onBlur="window.status=\'\';" onMouseMove="window.status=\'http://www.infotop.jp/\';return true;" onMouseOver="window.status=\'http://www.infotop.jp/\';return true;" onmouseout="window.status=\'\';"><img src="infotop.gif" border="0" alt="infotop"></a></td>';
	echo '<td>&nbsp;アフィリエイタＩＤ(aid)</td><td><input type="text" size="10" name="aid"';
	if($row['infotop'] != null){
		echo ' value="'.$row['infotop'].'"';
	}
	echo '></td>';
	echo '<td>&nbsp;商品ＩＤ(iid)</td><td><input type="text" size="10" name="iid"></td>';
	echo '<td>&nbsp;<input type="button" value="生成" onclick="insert(\'http://www.infotop.jp/click.php?\',\'aid=\'+aid.value,\'iid=\'+iid.value);"></td>';
	echo '</form></td></tr>';
	// infocart
	echo '<tr>';
	echo '<td><form><a href="http://www.infocart.jp/?e=000138" target="_blank" onFocus="window.status=\'http://www.infocart.jp/\';return true;" onBlur="window.status=\'\';" onMouseMove="window.status=\'http://www.infocart.jp/\';return true;" onmouseover="window.status=\'http://www.infocart.jp/\';return true;" onmouseout="window.status=\'\';"><img src="infocart.gif" border="0" alt="infotop"></a></td>';
	echo '<td>&nbsp;アフィリエイタＩＤ(af)</td><td><input type="text" size="10" name="aid"';
	if($row['infocart'] != null){
		echo ' value="'.$row['infocart'].'"';
	}
	echo '></td>';
	echo '<td>&nbsp;商品ＩＤ(item)</td><td><input type="text" size="10" name="iid"></td>';
	echo '<td>&nbsp;<input type="button" value="生成" onclick="insert(\'http://www.infocart.jp/af.php?\',\'af=\'+aid.value,\'item=\'+iid.value);"></td>';
	echo '</form></td></tr>';

	echo '</table>'."\n";
}
?>
