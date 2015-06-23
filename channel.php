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
	$md = $_REQUEST['md'];
	$id = intval($_REQUEST['id']);
	$oid = intval($_REQUEST['oid']);
	$order_id = intval($_REQUEST['order_id']);
	$name = $_REQUEST['name'];
	if($md != NULL){
		$result = db_query($db_conn,"select id from $channel_tbl order by id");
		$rows = db_num_rows($result);
		switch($md){
		case "ca":
			$id = 1;
			for($i = 0;$i < $rows;$i ++){
				$row = db_fetch_array($result);
				if($row['id'] != $id){
					break;
				}
				$id ++;
			}
			$result = db_query($db_conn,"insert into $channel_tbl (id,order_id,name) values($id,$rows,'$name')");
			if($result != FALSE){$pg = intval($rows/$pg_cnt);}
			break;
		case "変更":
			$sqlname = db_escape_string($name);
			if($id > 0){
				if($id == $oid){	// ＩＤ変更なし
					$result = db_query($db_conn,"update $channel_tbl set name = '$sqlname' where id = $oid");
				}else{				// ＩＤ変更あり
					$result = db_query($db_conn,"select id from $channel_tbl where id = $id");
					$rows = db_num_rows($result);
					if($rows == 0){
						$result = db_query($db_conn,"update $channel_tbl set name = '$sqlname',id = $id where id = $oid");
						if($result == true){
							$result = db_query($db_conn,"update $channel_list_tbl set id = $id where id = $oid");
						}
					}
				}
			}
			break;
		case "削除":
			if($oid > 0){
				$result = db_query($db_conn,"delete from $channel_tbl where id = $oid");
				if(result != FALSE){
					$result = db_query($db_conn,"delete from $channel_list_tbl where id = $oid");
					for($i = $order_id;$i < $rows;$i ++){
						$result = db_query($db_conn,"update $channel_tbl set order_id = $i where order_id = ".($i+1));
					}
					if($order_id > 0 && $rows == $order_id+1){
						$pg = intval(($order_id-1)/$pg_cnt);
					}
				}
			}
			break;
		case "↑":
			if($order_id > 0){
				$result = db_query($db_conn,"update $channel_tbl set order_id = -2 where order_id = ".($order_id-1));
				$result = db_query($db_conn,"update $channel_tbl set order_id = ".($order_id-1)." where order_id = ".($order_id));
				$result = db_query($db_conn,"update $channel_tbl set order_id = $order_id where order_id = -2");

				$pg = intval(($order_id-1)/$pg_cnt);
			}
			break;
		case "↓":
			if($order_id+1 < $rows){
				$result = db_query($db_conn,"update $channel_tbl set order_id = -2 where order_id = ".($order_id+1));
				$result = db_query($db_conn,"update $channel_tbl set order_id = ".($order_id+1)." where order_id = ".($order_id));
				$result = db_query($db_conn,"update $channel_tbl set order_id = $order_id where order_id = -2");

				$pg = intval(($order_id+1)/$pg_cnt);
			}
			break;
		}
		locationHTML($baseurl.$channel_php,"pg=$pg&sort=$sort");
		die();
	}
	if($sort == 0){
		$order = 'id';
	}else if($sort == 1){
		$order = 'id desc';
	}else if($sort == 2){
		$order = 'name';
	}else if($sort == 3){
		$order = 'name desc';
	}else{
		$order = 'order_id';
	}
	$result = db_query($db_conn,"select id from $channel_tbl order by $order");
	$maxrows = db_num_rows($result);
	$result = db_query($db_conn,"select * from $channel_tbl order by $order ".db_limit_string($pg * $pg_cnt,$pg_cnt));
	$rows = db_num_rows($result);

	html_head('チャネル編集');
	headline_menu();
?>
<div class="contents">
<form>
チャネル名&nbsp;<input type="text" name="name" size="60"><input type="submit" value="追加">
<input type="hidden" name="md" value="ca">
<input type="hidden" name="pg" value="<?php echo $pg?>">
<input type="hidden" name="sort" value="<?php echo $sort?>">
</form>
<br>
<script type="text/JavaScript">
<!--
function	makeCallTag(cid,opt_l,opt_m,style_m){
	if(isNaN(cid) == false && cid == String(parseInt(cid,10))){
		opt_l = (isNaN(opt_l = parseInt(opt_l)))? '':('&l=' + opt_l);
		if(style_m == 3){
			opt_m = '&m=11' + opt_m;
			s = '<'+'?php $s=@file_get_contents("<?php echo mb_convert_encoding($baseurl, "EUC-JP", "SJIS");?>injector.php?c=' + String(cid) + String(opt_l) + opt_m + '");if($s != false){$s="?'+'>\n".$s;@eval($s);}'+'?>';
		}else if(style_m == 2){
			opt_m = '&m=1' + opt_m;
			s = '<'+'?php $s=@file_get_contents("<?php echo mb_convert_encoding($baseurl, "EUC-JP", "SJIS");?>injector.php?c=' + String(cid) + String(opt_l) + opt_m + '");if($s != false){$s="?'+'>\n".$s;@eval($s);}'+'?>';
		}else{
			opt_m = (opt_m == 0)? '':('&m=' + opt_m);
			s = '<scr' + 'ipt type="text/javascript" src="<?php echo mb_convert_encoding($baseurl, "EUC-JP", "SJIS");?>injector.php?c=' + String(cid) + String(opt_l) + opt_m + '"></scr' + 'ipt>';
		}
	}else{
		s = 'チャネルＩＤを指定してください';
	}
	if(style_m == 1){
		s = s.replace(/</g,"&lt;");
		s = s.replace(/>/g,"&gt;");
	}
	document.getElementById('calltag').value = s;
}
function	z2h(nm){
	txt = document.getElementById(nm).value;
	h = "0123456789";
	z = "０１２３４５６７８９";
	s = "";
	for(i = 0;i < txt.length;i ++){
		c = txt.charAt(i);
		n = z.indexOf(c,0);
		if (n >= 0) c = h.charAt(n);
		s += c;
	}
	document.getElementById(nm).value = s;
}
//-->
</script>
<form>
ＩＤ<input type="text" size="5" name="cid" id="cid" onkeyup="javascript:z2h('cid');">
個数<input type="text" size="5" name="opt_l" id="opt_l" onkeyup="javascript:z2h('opt_l');">
改行<select name="opt_m">
<option value="0">あり</option>
<option value="1">なし</option>
</select>
用途<select name="style_m">
<option value="0">表示</option>
<option value="1">公開</option>
<option value="2">PHP</option>
<!--
<option value="3">携帯</option>
comment0ut 2015-06-19
-->
</select>
<input type="button" value="呼び出しタグ生成" onclick="javascript:makeCallTag(cid.value,opt_l.value,opt_m.value,style_m.value);">
<br>
<input type="text" size="80" id="calltag">
</form>
<br>
<br>
<?php
	if($maxrows > $pg_cnt && $pg > 0){
		echo '<a href="?pg='.($pg-1).'">&lt;&lt; prev&nbsp;</a>&nbsp;';
	}
	for($i = 0;$i < $maxrows / $pg_cnt;$i ++){
		if($i == $pg){echo "[";}
		echo '<a href="?pg='.($i).'">'.$i."</a>";
		if($i == $pg){echo "]";}
		echo "&nbsp;";
	}
	if($maxrows > $pg_cnt * ($pg+1)){
		echo '<a href="?pg='.($pg+1).'">next &gt;&gt;</a>';
	}
	echo "<br>\n";
?>
<table>
<tr><td colspan="2" align="center"><?php if($sort >= 0){echo "<a href=\"?pg=$pg\">ソート解除</a>";}?></td><td align="center">ＩＤ<br>[<a href="?pg=<?php echo $pg;?>&sort=0">▲</a>][<a href="?pg=<?php echo $pg;?>&sort=1">▼</a>]</td><td align="center">チャネル名<br>[<a href="?pg=<?php echo $pg;?>&sort=2">▲</a>][<a href="?pg=<?php echo $pg;?>&sort=3">▼</a>]</td><td></td><td></td><td></td></tr>
<?php
	for($i = 0;$i < $rows;$i ++){
		$row = db_fetch_array($result);
		echo '<tr>';
		echo '<form action="channeldetails.php">';
		echo '<td><input type="submit" name="md" value="詳細"><input type="hidden" name="id" value="'.$row['id'].'"><input type="hidden" name="pg" value="'.$pg.'"><input type="hidden" name="sort" value="'.$sort.'"></td>';
		echo "</form>";
		echo "<form>";
		echo '<td><input type="submit" name="md" value="変更"><input type="hidden" name="oid" value="'.$row['id'].'"><input type="hidden" name="order_id" value="'.$row['order_id'].'"><input type="hidden" name="pg" value="'.$pg.'"><input type="hidden" name="sort" value="'.$sort.'"></td>';
		echo '<td align="center"><input type="text" name="id" size="5" value="'.$row['id'].'" style="text-align:right"></td>';
		echo '<td><input type="text" name="name" size="60" value="'.$row['name'].'"></td>';
		echo '<td><input type="submit" name="md" value="削除"></td>';
		if($sort < 0){
			echo '<td><input type="submit" name="md" value="↑"></td>';
			echo '<td><input type="submit" name="md" value="↓"></td>';
		}
		echo "</form>";
		echo '</tr>';
		echo "\n";
	}
?>
</table>
</div>
<?php
	html_foot();
?>