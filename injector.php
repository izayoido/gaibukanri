<?php
	require_once("lib.php");
	require_once("config.php");

	$db_conn = db_connect($user,$passwd,$port,$dbname,$host);

	$channel = intval($_REQUEST['c']);
	$cnt = intval($_REQUEST['l']);
	if($cnt <= 0){$cnt = 1;}
	$md = intval($_REQUEST['m'],2);	// [0xx:数値文字参照 1xx:無変換][x0x:js x1x:plane] [xx0:改行あり xx1:改行なし]
	$date = time();
	$result = db_query($db_conn,"select aid from $channel_list_tbl where id = $channel");
	$rows = db_num_rows($result);
	if($rows == 0){	// 無効なチャネル:全データを対象に
		$dat = fromalldata($db_conn,$cnt);
	}else{
		$dat = array();
		for($i = 0;$i < $rows;$i ++){
			$row = db_fetch_array($result);
			$res = db_query($db_conn,"select id from $data_tbl where id = ".$row['aid']." and stat = 1 and start_day <= $date and ((end_day = 0) or (end_day > $date))");
			if(db_num_rows($res) > 0){
				$d = db_fetch_array($res);
				$dat[] = $d['id'];
			}
		}
		if(count($dat) == 0){	// 有効な広告なし:全データを対象に
			$dat = fromalldata($db_conn,$cnt);
		}
	}
	list($msec,$sec) = explode(" ",microtime());
	srand($msec * 100000);
	$list = array();
	for($i = 0;$i < $cnt;$i ++){
		$rnd = rand(0,count($dat) -1);
		$list[] = $dat[$rnd];
		$buf = array();
		for($j = 0;$j < count($dat);$j ++){
			if($dat[$j] != $dat[$rnd]){
				$buf[] = $dat[$j];
			}
		}
		$dat = $buf;
		if(count($dat) <= 0){break;}
	}
	if(($md & 0x02) == 0){	// JavaScript形式
		header("Content-Type: text/javascript; charset=EUC-JP");
	}else{
		header("Content-Type: text/plain; charset=SHIFT_JIS");
	}
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // 過去の日付
	header("Last-Modified: ". gmdate("D, d M Y H:i:s"). " GMT");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	for($i = 0;$i < count($list);$i ++){
		$result = db_query($db_conn,"select tag from $data_tbl where id = ".$list[$i]);
		$row = db_fetch_array($result);
		$tag = stripslashes($row['tag']);
		if(($md & 0x04) == 0){	// 数値文字参照形式
			// 数値文字参照に変換
			$convmap = array(0x0080, 0xffff, 0, 0xffff);
			$s = mb_convert_encoding($tag,"UTF-8","ASCII,JIS,UTF-8,EUC-JP,SJIS");
			$tag = mb_encode_numericentity($s, $convmap, "UTF-8");
		}else{
			// 絵文字変換
			$tag = mb_convert_encoding($tag,"SJIS","ASCII,JIS,UTF-8,EUC-JP,SJIS");
			$tag = mb_eregi_replace("emoji\(([a-fA-F\d]*)\)","<?php echo pack('H*','\\1');?>",$tag);
		}
		if(($md & 0x02) == 0){	// JavaScript形式
			$tag = str_replace("'","'+\"'\"+'",$tag);
			$tag = str_replace("+''+","+",$tag);
			$tag = str_replace("<!--","<'+'!'+'--",$tag);
			$tag = str_replace("//","/'+'/",$tag);
			$tag = str_replace("</","<'+'/",$tag);
			$tag = eregi_replace("script","scr'+'ipt",$tag);
			$tag = preg_replace("/(\r\n|\n|\r)/","');$1document.writeln('",$tag);
			$tag = "document.writeln('$tag');\n";
		}
		if((($md & 0x01) == 0) && (($i+1) < count($list))){	// 改行つき
			$tag .= (($md & 0x02) == 0)? "document.write('<br />');":"<br />";
		}

		echo $tag."\n";
	}
function	droparray($list,$val){
	$result = array();
	for($i = 0;$i < count($list);$i ++){
		if($list[$i] != $val){
			$resut[] = $list[$i];
		}
	}
	return($result);
}
function	fromalldata($db_conn,$cnt){
	GLOBAL	$data_tbl;
	$date = time();
	$result = db_query($db_conn,"select id from $data_tbl where stat = 1 and start_day <= $date and ((end_day = 0) or (end_day > $date))");
	$rows = db_num_rows($result);
	if($rows == 0){	// 有効な広告なし
		die();
	}
	$dat = array();
	for($i = 0;$i < $rows;$i ++){
		$row = db_fetch_array($result);
		$dat[] = $row['id'];
	}
	return($dat);
}
?>