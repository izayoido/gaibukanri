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
	$md = $_REQUEST['md'];
	$id = intval($_REQUEST['id']);
	$name = $_REQUEST['name'];
	if($md != NULL){
		$result = db_query($db_conn,"select id from $group_tbl order by id");
		$rows = db_num_rows($result);
		switch($md){
		case "ga":
			$result = db_query($db_conn,"insert into $group_tbl (id,name) values($rows,'$name')");
			if($result != FALSE){$pg = intval($rows/$pg_cnt);}
			break;
		case "変更":
			$sqlname = db_escape_string($name);
			$result = db_query($db_conn,"update $group_tbl set name = '$sqlname' where id = $id");
			break;
		case "削除":
			$result = db_query($db_conn,"delete from $group_tbl where id = $id");
			if(result != FALSE){
				$result = db_query($db_conn,"update $data_tbl set group_id = -1 where group_id = $id");
				for($i = $id;$i < $rows;$i ++){
					$result = db_query($db_conn,"update $group_tbl set id = $i where id = ".($i+1));
				}
				if($id > 0 && $rows == $id+1){
					$pg = intval(($id-1)/$pg_cnt);
				}
			}
			break;
		case "↑":
			if($id > 0){
				$result = db_query($db_conn,"update $group_tbl set id = -2 where id = ".($id-1));
				$result = db_query($db_conn,"update $group_tbl set id = ".($id-1)." where id = ".($id));
				$result = db_query($db_conn,"update $group_tbl set id = $id where id = -2");

				$result = db_query($db_conn,"update $data_tbl set group_id = -2 where group_id = ".($id-1));
				$result = db_query($db_conn,"update $data_tbl set group_id = ".($id-1)." where group_id = ".($id));
				$result = db_query($db_conn,"update $data_tbl set group_id = $id where group_id = -2");

				$pg = intval(($id-1)/$pg_cnt);
			}
			break;
		case "↓":
			if($id+1 < $rows){
				$result = db_query($db_conn,"update $group_tbl set id = -2 where id = ".($id+1));
				$result = db_query($db_conn,"update $group_tbl set id = ".($id+1)." where id = ".($id));
				$result = db_query($db_conn,"update $group_tbl set id = $id where id = -2");

				$result = db_query($db_conn,"update $data_tbl set group_id = -2 where group_id = ".($id+1));
				$result = db_query($db_conn,"update $data_tbl set group_id = ".($id+1)." where group_id = ".($id));
				$result = db_query($db_conn,"update $data_tbl set group_id = $id where group_id = -2");

				$pg = intval(($id+1)/$pg_cnt);
			}
			break;
		}
		locationHTML($baseurl.$group_php,"pg=$pg");
		die();
	}
	$result = db_query($db_conn,"select id from $group_tbl order by id");
	$maxrows = db_num_rows($result);
	$result = db_query($db_conn,"select * from $group_tbl order by id ".db_limit_string($pg * $pg_cnt,$pg_cnt));
	$rows = db_num_rows($result);

	html_head('グループ編集');
	headline_menu();
?>
<div class="contents">
<form>
グループ名&nbsp;<input type="text" name="name" size="60"><input type="submit" value="追加">
<input type="hidden" name="md" value="ga">
<input type="hidden" name="pg" value="<?php echo $pg?>">
</form>
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
?>
<table>
<tr><td></td><td>グループ名</td><td></td></tr>
<?php
	for($i = 0;$i < $rows;$i ++){
		$row = db_fetch_array($result);
		echo "<form>";
		echo '<tr>';
		echo '<td><input type="submit" name="md" value="変更"><input type="hidden" name="id" value="'.$row['id'].'"><input type="hidden" name="pg" value="'.$pg.'"></td>';
		echo '<td><input type="text" name="name" size="60" value="'.$row['name'].'"></td>';
		echo '<td><input type="submit" name="md" value="削除"></td>';
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