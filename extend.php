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

	html_head('EXTEND');
	headline_menu();
?>
<script type="text/javascript">
<!--
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
function	mkTag(md){
	s = "var d = new Date();\n";
	if(md == 0){	// 時間
		targetAs = 'tc';
		dt = new Array(24);
		s += "var idx = d.getHours();\n";
	}else{			// 曜日
		targetAs = 'wc';
		dt = new Array(7);
		s += "var idx = d.getDay();\n";
	}
	s += 'var tbl = new Array(';
	for(var i = 0;i < dt.length;i ++){
		dt[i] = document.getElementById(targetAs+i).value;
		if(isNaN(dt[i]) == false && dt[i] == String(parseInt(dt[i],10))){
			s += dt[i]+',';
		}else{
			s = '';
			break;
		}
	}
	if(s != ''){
		s = s.replace(/,+$/g, "");
		s += ");\n";
		s += "var c = tbl[idx];\n";
		s += 's = \'<scr\'+\'ipt type="text/javascr\'+\'ipt" src="<?php echo mb_convert_encoding($baseurl, "EUC-JP", "SJIS");?>injector.php?c=\'+c+\'"></\'+\'script>\';'+"\n";
		s += 'document.writeln(s);';
	}else{
		s = 'チャネルＩＤを指定してください';
	}
	document.getElementById('calltag').value = s;
}
function	baseSet(md){
	var baseID = document.getElementById('baseID').value;
	var obj;
	for(var i = 0;i < 24;i ++){
		obj = document.getElementById('tc'+i);
		obj.value = obj.value.replace(/^[\s　]+|[\s　]+$/g, "");
		if(md == 0){	// 全て
			obj.value = baseID;
		}else{			// 空き
			if(obj.value == ''){obj.value = baseID;}
		}
	}
	for(var i = 0;i < 7;i ++){
		obj = document.getElementById('wc'+i);
		obj.value = obj.value.replace(/^[\s　]+|[\s　]+$/g, "");
		if(md == 0){	// 全て
			obj.value = baseID;
		}else{			// 空き
			if(obj.value == ''){obj.value = baseID;}
		}
	}
}
//-->
</script>
<div class="contents">
<form>
基本&nbsp;<input type="text" id="baseID" size="6" maxlength="6" style="ime-mode:disabled;" onkeyup="javascript:z2h('baseID');">
<input type="button" value="全て" onclick="javascript:baseSet(0);">
<input type="button" value="空き" onclick="javascript:baseSet(1);">
</form>
<br>
<table>
<tr><td style="width:350px;">
<form>
<table>
<tr><td>
<?php
	$cidtx = "<p style=\"text-align:right;\">チャネルＩＤ</p>\n";
	echo $cidtx;
	for($i = 0;$i < 12;$i ++){
		$t = ($i < 10)? "0$i":"$i";
		echo "$t:00～$t:59".'&nbsp;<input type="text" id="tc'.$i.'" size="6" maxlength="6" style="ime-mode:disabled;" onkeyup="javascript:z2h(\'tc'.$i.'\');"><br>';
	}
?>
</td>
<td>
<?php
	echo $cidtx;
	for($i = 12;$i < 24;$i ++){
		$t = $i;
		echo "$t:00～$t:59".'&nbsp;<input type="text" id="tc'.$i.'" size="6" maxlength="6" style="ime-mode:disabled;" onkeyup="javascript:z2h(\'tc'.$i.'\');"><br>';
	}
?>
</td>
</tr>
<tr>
<td colspan="2">
<p style="margin-left:125px;"><input type="button" value="生成" onclick="javascript:mkTag(0);"></p>
</td>
</tr>
</table>
</form>
</td>
<td style="vertical-align:top;padding-top:2px;">
<form>
<?php
	$wkd = array(
		'<span style="color:red;font-weight:bold;">日</span>',
		'<span style="color:black;font-weight:bold;">月</span>',
		'<span style="color:black;font-weight:bold;">火</span>',
		'<span style="color:black;font-weight:bold;">水</span>',
		'<span style="color:black;font-weight:bold;">木</span>',
		'<span style="color:black;font-weight:bold;">金</span>',
		'<span style="color:blue;font-weight:bold;">土</span>'
	);
	echo $cidtx;
	for($i = 0;$i < 7;$i ++){
		echo $wkd[$i].'&nbsp;<input type="text" id="wc'.$i.'" size="6" maxlength="6" style="ime-mode:disabled;" onkeyup="javascript:z2h(\'wc'.$i.'\');"><br>';
	}
?>
<p style="margin-left:17px;"><input type="button" value="生成" onclick="javascript:mkTag(1);"></p>
</form>
</td>
</tr>
</table>
<br>
<textarea cols="100" rows="10" id="calltag">
</textarea>
</div>
<?php
	html_foot();
?>