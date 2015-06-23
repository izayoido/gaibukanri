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

	$pg_cnt = 10;
	$pg = intval($_REQUEST['pg']);
	if(isset($_REQUEST['sort'])){
		$sort = intval($_REQUEST['sort']);
	}else{
		$sort = -1;
	}
	$id = intval($_REQUEST['id']);
	$prearg = '<input type="hidden" name="id" value="'.$id.'">';
	$prearg .= '<input type="hidden" name="pg" value="'.$pg.'">';

	$apg = intval($_REQUEST['apg']);
	$lpg = intval($_REQUEST['lpg']);
	$md = $_REQUEST['md'];
	$srt = intval($_REQUEST['srt'],2);
	$oid = intval($_REQUEST['oid']);
	$aid = intval($_REQUEST['aid']);
	$order_id = intval($_REQUEST['order_id']);
	$name = $_REQUEST['name'];
	$gid = intval($_REQUEST['gid']);
	if($md != NULL){
		$result = db_query($db_conn,"select id from $channel_list_tbl where id = $id order by order_id");
		$rows = db_num_rows($result);
		switch($md){
		case "追加":
			$result = db_query($db_conn,"insert into $channel_list_tbl (id,order_id,aid) values($id,$rows,$aid)");
			if($result != FALSE){$pg = intval($rows/$pg_cnt);}
			break;
		case "削除":
			$result = db_query($db_conn,"delete from $channel_list_tbl where id = $id and order_id = $order_id");
			if(result != FALSE){
				for($i = $order_id;$i < $rows;$i ++){
					$result = db_query($db_conn,"update $channel_list_tbl set order_id = $i where id = $id and order_id = ".($i+1));
				}
				if($order_id > 0 && $rows == $order_id+1){
					$pg = intval(($order_id-1)/$pg_cnt);
				}
			}
			break;
		case "↑":
			if($order_id > 0){
				$result = db_query($db_conn,"update $channel_list_tbl set order_id = -2 where id = $id and order_id = ".($order_id-1));
				$result = db_query($db_conn,"update $channel_list_tbl set order_id = ".($order_id-1)." where id = $id and order_id = ".($order_id));
				$result = db_query($db_conn,"update $channel_list_tbl set order_id = $order_id where id = $id and order_id = -2");

				$pg = intval(($order_id-1)/$pg_cnt);
			}
			break;
		case "↓":
			if($order_id+1 < $rows){
				$result = db_query($db_conn,"update $channel_list_tbl set order_id = -2 where id = $id and order_id = ".($order_id+1));
				$result = db_query($db_conn,"update $channel_list_tbl set order_id = ".($order_id+1)." where id = $id and order_id = ".($order_id));
				$result = db_query($db_conn,"update $channel_list_tbl set order_id = $order_id where id = $id and order_id = -2");

				$pg = intval(($order_id+1)/$pg_cnt);
			}
			break;
		}
		if(!($md == '詳細' or $md == '更新')){
			locationHTML($baseurl.$channeldetails_php,"pg=$pg&id=$id&apg=$apg");
			die();
		}
	}
	$result = db_query($db_conn,"select * from $channel_tbl where id = $id");
	$row = db_fetch_array($result);

	html_head('チャネル編集');
	headline_menu();
?>
<div class="contents">
編集中のチャネル　ＩＤ:<?php echo $row['id'];?>　名称:<?php echo $row['name'];?>
<form method="GET" action="<?php echo $channel_php;?>">
<input type="hidden" name="pg" value="<?php echo $pg;?>"> 
<input type="hidden" name="sort" value="<?php echo $sort;?>"> 
<input type="submit" value="戻る"> 
</form>
<br>
広告の追加<br>
<form>
広告グループ
<select name="gid">
<option value="0">全て</option>
<?php
	$result = db_query($db_conn,"select * from $group_tbl order by id");
	$rows = db_num_rows($result);
	for($i = 0;$i < $rows;$i ++){
		$row = db_fetch_array($result);
		echo '<option value="'.($row['id']+1).'"';
		if($gid == ($row['id']+1)){echo ' selected';}
		echo '>'.$row['name'].'</option>';
	}
?>
</select>
<?php
	echo $prearg;
	echo '<input type="hidden" name="apg" value="'.$apg.'">';
	echo '<input type="hidden" name="lpg" value="'.$lpg.'">';
?>
<input type="submit" value="更新">
</form>
<?php
	$s = ($gid == 0)? "":" where group_id = ".($gid-1);
	$result = db_query($db_conn,"select * from $data_tbl$s order by id");
	$rows = db_num_rows($result);
	if($rows == 0){
		echo '広告が見つかりません<br>';
	}else{
		$arg = "?pg=$pg&id=$id&lpg=$lpg&apg=";
		if($rows > $pg_cnt && $apg > 0){
			echo '<a href="'.$arg.($apg-1).'">&lt;&lt; prev&nbsp;</a>&nbsp;';
		}
		for($i = 0;$i < $rows / $pg_cnt;$i ++){
			if($i == $apg){echo "[";}
			echo '<a href="'.$arg.($i).'">'.$i."</a>";
			if($i == $apg){echo "]";}
			echo "&nbsp;";
		}
		if($rows > $pg_cnt * ($apg+1)){
			echo '<a href="'.$arg.($apg+1).'">next &gt;&gt;</a>';
		}
		echo '<table border="1">';
		echo '<tr bgcolor="#0080FF"><th></th><th>ＩＤ</th><th>状態</th><th>名前</th><th>開始日</th><th>終了日</th></tr>';
		for($i = 0;$i < $rows;$i ++){
			$row = db_fetch_array($result);
			echo "<form><tr><td>";
			echo '<input type="submit" name="md" value="追加">';
			echo $prearg;
			echo '<input type="hidden" name="apg" value="'.$apg.'">';
			echo '<input type="hidden" name="aid" value="'.$row['id'].'">';
			echo '<input type="hidden" name="lpg" value="'.$lpg.'">';
			echo '</td><td style="text-align:right">';
			echo $row['id'];
			echo '</td><td>';
			if($row['stat'] == 1){
				echo '表示&nbsp;&nbsp;';
			}else{
				echo '非表示';
			}
			echo '</td><td title="'.htmlspecialchars(stripslashes($row['tag']), ENT_QUOTES).'">';
			echo htmlspecialchars(stripslashes($row['name']), ENT_QUOTES);
			echo '</td><td>';
			if($row['start_day'] != 0){
				echo date("Y/m/d H:i:s",$row['start_day']);
			}else{
				echo '--/--/-- --:--:--';
			}
			echo '&nbsp;</td><td>';
			if($row['end_day'] != 0){
				echo date("Y/m/d H:i:s",$row['end_day']);
			}else{
				echo '--/--/-- --:--:--';
			}
			echo "</td></tr></form>";
		}
		echo "</table>";
	}
?>
<br>
登録済み広告<br>
<?php
	$result = db_query($db_conn,"select id from $channel_list_tbl where id = $id");
	$maxrows = db_num_rows($result);
	$arg = "?pg=$pg&id=$id&apg=$apg&lpg=";
	if($maxrows > $pg_cnt && $lpg > 0){
		echo '<a href="'.$arg.($lpg-1).'">&lt;&lt; prev&nbsp;</a>&nbsp;';
	}
	for($i = 0;$i < $maxrows / $pg_cnt;$i ++){
		if($i == $lpg){echo "[";}
		echo '<a href="'.$arg.($i).'">'.$i."</a>";
		if($i == $lpg){echo "]";}
		echo "&nbsp;";
	}
	if($maxrows > $pg_cnt * ($lpg+1)){
		echo '<a href="'.$arg.($lpg+1).'">next &gt;&gt;</a>';
	}
?>
<table>
<tr><td></td><td>広告名</td><td></td></tr>
<?php
	$result = db_query($db_conn,"select * from $channel_list_tbl where id = $id order by order_id ".db_limit_string($lpg * $pg_cnt,$pg_cnt));
	$rows = db_num_rows($result);
	for($i = 0;$i < $rows;$i ++){
		$row = db_fetch_array($result);
		$res = db_query($db_conn,"select * from $data_tbl where id = ".$row['aid']);
		$arow = db_fetch_array($res);
		echo '<form>';
		echo $prearg;
		echo '<input type="hidden" name="aid" value="'.$row['id'].'">';
		echo '<input type="hidden" name="order_id" value="'.$row['order_id'].'">';
		echo '<input type="hidden" name="apg" value="'.$apg.'">';
		echo '<input type="hidden" name="lpg" value="'.$lpg.'">';
		echo '<tr>';
		echo '<td><input type="submit" name="md" value="削除"></td>';
//		echo '<td><p style="text-align:right">'.$arow['id'].'</p></td>';
		echo '<td>'.$arow['name'].'</td>';
		echo '<td><input type="submit" name="md" value="↑"></td>';
		echo '<td><input type="submit" name="md" value="↓"></td>';
		echo '</tr>';
		echo "</form>\n";
	}
?>
</table>
</div>
<?php
	html_foot();
?>
