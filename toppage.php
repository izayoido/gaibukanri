<?php
	require_once("config.php");
	require_once("lib.php");
	require_once("common.php");
	start_session();

	$db_conn = db_connect($user,$passwd,$port,$dbname,$host);
	if(chksession($db_conn) == FALSE){
		sess_cookie_break();
		locationHTML($baseurl.$login_php,"");
		die();
	}
	chg_session($db_conn);

	html_head('ＴＯＰ');
	headline_menu();
?>
<div class="contents">
上部メニューから操作してください<br>
<?php
	// 無効（間近）なチャネルを警告
	echo '<br>';
	$sdy = time();
	$edy = $sdy + 86400*7;
	$clists = db_query($db_conn,"select id from $channel_tbl");
	$rows = db_num_rows($clists);
	if($rows > 0){
		for($i = $rows;$i > 0;$i --){
			$row = db_fetch_array($clists);
			$clist = db_query($db_conn,"select distinct aid from $channel_list_tbl where id = ".$row['id']); // チャネルに登録された広告一覧
			$rows = db_num_rows($clist);
			if($rows > 0){
				$dt = db_fetch_array($clist);
				$ids = '((id = '.$dt['aid'].')';
				for($j = $rows-1;$j > 0;$j --){
					$dt = db_fetch_array($clist);
					$ids .= ' or (id = '.$dt['aid'].')';
				}
				$ids .= ')';
				$res = db_query($db_conn,"select id,start_day,end_day from $data_tbl where $ids and stat = 1 and start_day <= $edy and ((end_day = 0) or (end_day > $sdy))");
				$dat = array();
				for($r = db_num_rows($res);$r > 0;$r --){
					$dat[] = db_fetch_array($res);
				}
				$r = check($dat,$sdy,$edy);
				if($r < 0){	// 有効な広告が登録されていないチャネル
					echo colorTxt('注意！','red') . 'チャネルＩＤ['.$row['id'].']：有効な広告がありません<br>'."\n";
				}else if($r > 0){	// 今は有効でも７日以内になくなる
					echo colorTxt('注意！','red') . 'チャネルＩＤ['.$row['id'].']：'.date("Y/m/d H:i",$r).' に有効な広告が無くなります<br>'."\n";
				}
			}else{	// 広告が登録されていないチャネル
				echo colorTxt('注意！','red') . 'チャネルＩＤ['.$row['id'].']：なにも登録されていません<br>'."\n";
			}
		}
	}else{	// チャネルがない場合は全広告チェック
		echo colorTxt('注意！','red') . 'チャネルがありません<br>'."\n";
		$result = db_query($db_conn,"select id,start_day,end_day from $data_tbl where stat = 1 and start_day <= $edy and ((end_day = 0) or (end_day > $sdy))");
		$rows = db_num_rows($result);
		$dat = array();
		for($r = db_num_rows($result);$r > 0;$r --){
			$dat[] = db_fetch_array($result);
		}
		$r = check($dat,$sdy,$edy);
		if($r < 0){	// 有効な広告が一つも無い
			echo colorTxt('注意！','red') . '有効な広告がありません<br>'."\n";
		}else if($r > 0){	// 今は有効でも７日以内になくなる
			echo colorTxt('注意！','red').date("Y/m/d H:i",$r).' に有効な広告が無くなります<br>'."\n";
		}
	}
	echo '<br>';
	$ver = "1.31";
	//if(@readfile("http://www.infogrove.net/viper/news.php?v=$ver") == false){
	//	echo '<iframe src="http://www.infogrove.net/viper/newsframe.php?v=$ver" width="100%">'."\n";
	//}
?>
</div>
<?php
	html_foot();
function	check($dat,$sdy,$edy){
	if($dat == null){return(-1);}
	foreach($dat as $idx=>$dt){
		if($dt['start_day'] < $sdy){$dat[$idx]['start_day'] = $sdy;}
		if($dt['end_day'] > $edy || $dt['end_day'] == 0){$dat[$idx]['end_day'] = $edy;}
	}
	do{
		// 配列を第１キー'start_day'、第２キー'end_day'でソート
		$start_day = array();
		$end_day = array();
		foreach ($dat as $dt) {
			$start_day[]  = $dt['start_day'];
			$end_day[] = $dt['end_day'];
		}
		array_multisort($start_day,SORT_ASC,$end_day,SORT_DESC,$dat);
		if($dat[0]['start_day'] > $sdy){return(-1);}
		if($dat[0]['end_day'] >= $edy){return(0);}
		$cnt = count($dat);
		$pt = 0;
		for($idx = 1;$idx < $cnt;$idx ++){
			if($dat[$idx]['start_day'] <= $dat[$pt]['end_day']){
				$dat[$idx]['start_day'] = $sdy;
			}
			if($dat[$idx]['start_day'] == $sdy){
				if($dat[$idx]['end_day'] > $dat[$pt]['end_day']){
					$dat[$pt] = null;
					$pt = $idx;
				}else{
					$dat[$idx] = null;
				}
			}
		}
		$ndat = array();
		foreach($dat as $dt){
			if($dt != null){
				$ndat[] = $dt;
			}
		}
		$dat = $ndat;
	}while(count($dat) != $cnt);
	return($dat[0]['end_day']);
}
?>
