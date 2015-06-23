<?php
	$db_type = 0;	// 使用するデータベースがMySQLなら0、PostgreSQLなら1（殆どの場合0でOK）

	$dbname = "gaibukanri";	// データベース名
	$user   = "gaibukanri";	// データベースの接続ユーザー名(データベース名と同じことが多いです。が、確認してくださいね)
	$passwd = "kotukotukotukotu";	// データベースの接続パスワード
	$host   = "localhost";	// データベースの接続ホスト名
//	$port  = ここを書き換え;	// データベースの接続ポート番号（必要な場合は行頭の//を削除して指定）

	$baseurl = "http://jidoka-dou.net/gaibukanri/";	// "http://～/"でPHPを置いたフォルダのURLを指定

	// 以下は必要な場合のみ変更(２byteコード（日本語等）不可)
	$data_tbl = "viper_data_tbl";	// 広告データのテーブル名
	$group_tbl = "viper_group_tbl";	// グループデータのテーブル名
	$channel_tbl = "viper_channel_tbl";	// チャンネルデータのテーブル名
	$channel_list_tbl = "viper_channel_list_tbl";	// チャンネルリストデータのテーブル名
	$user_tbl = "viper_user_tbl";	// ユーザーデータのテーブル名
?>