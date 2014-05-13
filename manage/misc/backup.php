<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
import('backup');

need_manager(true);

function _go_reload() {
	redirect( WEB_ROOT . '/manage/misc/backup.php' );
}

/* get tables */
$db_name = $INI['db']['name'];
$tables = DB::GetQueryResult("SHOW TABLE STATUS FROM `{$db_name}`", false);
/* end */

if (is_get()) {
	$results = DB::GetQueryResult("SHOW TABLE STATUS FROM `{$db_name}`", false);
	$option_table = Utility::OptionArray($results, 'name', 'name');
	die(include template('manage_misc_backup'));
}

$bftype=$_POST['bfzl'];
if($bftype=="quanbubiao"){
	if(!$_POST['fenjuan']){ //不是分卷
		$sql = null;
		foreach($tables AS $one) {
			$table = $one['name'];
			$sql .= backup_make_header($table);
			$query = DB::Query("SELECT * FROM `{$table}`");
			while($r = DB::NextRecord($query) ) {
				$sql .= backup_make_record($table, $r);
			}
		}
		$filename = date("Ymd").Utility::GenSecret(4).'_all.sql';
		if($_POST['weizhi']=="localpc") {
			backup_down_file($sql, $filename);
		}
		elseif($_POST['weizhi']=="server"){
			if( true === backup_write_file($sql,$filename)) {
				Session::Set('notice', "全部数据表数据备份完成");
			}
			else {
				Session::Set('error', "备份全部数据表失败");
			}
		}
		_go_reload();
	}else{  //分卷备份
		if(!$_POST['filesize']){
			Session::Set('error', "请填写备份文件分卷大小！");
			_go_reload();
		}

		$filenamep = date("Ymd").Utility::GenSecret(4).'_all';
		$p=1; $sql = null;

		foreach($tables AS $one) {
			$table = $one['name'];
			$sql .= backup_make_header($table);
			$query = DB::Query("SELECT * FROM `{$table}`");
			while($r = DB::NextRecord($query) ) {
				$sql .= backup_make_record($table, $r);
				if(strlen($sql)>=$_POST['filesize']*1024){
					$filename = $filenamep  . ("_v".$p.".sql");
					if( true !== backup_write_file($sql,$filename)) {
						Session::Set('error',  "备份失败");
						_go_reload();
					}
					$p++; $sql = null;
				}
			}
		}

		if($sql) {
			$filename = $filenamep  . ("_v".$p.".sql");
			if( true !== backup_write_file($sql,$filename)) {
				Session::Set('error', "备份失败");
				_go_reload();
			}
		}

		Session::Set('notice', "全部数据表备份成功！");
		_go_reload();
	}
} elseif($bftype=="danbiao") {

	$table = mysql_escape_string(strval($_POST['tablename']));

	if(!$table) {
		Session::Set('error', "请选择要备份的数据表");
		_go_reload();
	}

	if(!$_POST['fenjuan']){ //不分卷
		$sql = null;
		$sql .= backup_make_header($table);
		$query = DB::Query("SELECT * FROM `{$table}`");
		while($r = DB::NextRecord($query)){
			$sql .= backup_make_record($table, $r);
		}
		$filename = date("Ymd").Utility::GenSecret(4)."_{$table}.sql";
		if($_POST['weizhi']=="localpc") {
			backup_down_file($sql, $filename);
		}
		elseif($_POST['weizhi']=="server"){
			if( true === backup_write_file($sql, $filename)) {
				Session::Set('notice', "表-{$table}-数据备份完成");
			} else {
				Session::Set('error', "备份表-{$table}-失败");
			}
			_go_reload();
		}
	} else { //分卷备份
		if(!$_POST['filesize']){
			Session::Set('error', "请填写备份文件分卷大小！");
			_go_reload();
		}

		$sql = null;
		$sql .= backup_make_header($table);
		$p=1;
		$filenamep = date("Ymd").Utility::GenSecret(4)."_{$table}";

		$query = DB::Query("SELECT * FROM `{$table}`");
		while($r = DB::NextRecord($query)){
			$sql .= backup_make_record($table, $r);
			if(strlen($sql)>=$_POST['filesize']*1024){
				$filename = $filenamep . ("_v".$p.".sql");
				if( true !== backup_write_file($sql,$filename)){
					Session::Set('error',"备份表-{$table}-{$p}-失败");
					_go_reload();
				}
				$p++; $sql = null;
			}
		}

		if($sql) {
			if( true !== backup_write_file($sql,$filename)){
				Session::Set('error', "备份表-{$table}-失败");
				_go_reload();
			}
		}
		Session::Set('notice', "表-{$table}-数据备份完成");
		_go_reload();
	}

	if($_POST['weizhi']=="localpc" && $_POST['fenjuan']=='yes') {
		Session::Set('error', "选择备份到服务器，才能使用分卷备份");
		_go_reload();
	}

	if($_POST['fenjuan']=="yes" && !$_POST['filesize']) {
		Session::Set('error', "选择了分卷备份，未填写分卷文件大小");
		_go_reload();
	}

	$backupdir = DIR_ROOT . '/data';
	if($_POST['weizhi']=="server" && is_writeable($backupdir)) {
		Session::Set('error', "备份文件存放目录{$backupdir}不可写，请修改目录属性");
		_go_reload();
	}
	_go_reload();
}
