<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
import('backup');

need_manager();
need_auth('admin');

function _go_reload() {
	redirect( WEB_ROOT . '/manage/misc/restore.php' );
}

/* get tables */
$db_name = $INI['db']['name'];
$tables = DB::GetQueryResult("SHOW TABLE STATUS FROM `{$db_name}`", false);
/* end */

$backupdir = DIR_ROOT . '/data';
$handle = opendir($backupdir); $bs = array();
while ($file = readdir($handle)) {
    if(eregi("^[0-9]{8}[A-Z]{4}([0-9a-zA-Z_]+)(\.sql)$", $file))
        $bs[$file] = $file;
}
krsort($bs);
closedir($handle);

$action = strval($_REQUEST['action']);

if ($action=="restore") {
    if($_POST['restorefrom']=="server"){

		$serverfile = strval($_POST['serverfile']);
        if(!$serverfile) {
            Session::Set('error', "您选择从服务器文件恢复备份，但没有指定备份文件");
			_go_reload();
        }

        if(!eregi("_v[0-9]+", $serverfile)) {
            $filename = $backupdir . '/' . $serverfile;
            if( backup_import($filename)) {
               Session::Set('notice', "备份文件{$serverfile}成功导入数据库");
			}
            else {
                Session::Set('error', "备份文件{$serverfile}导入失败");
			}
			_go_reload();

        }else{
            $filename = $backupdir . '/' . $serverfile;
            if( true === backup_import($filename)) {
                Session::Set('notice', "{$serverfile}成功导入数据库");
			}
            else {
                Session::Set('error', "{$serverfile}导入失败");
				_go_reload();
            }

            $voltmp = explode("_v",$serverfile);
            $volname = $voltmp[0];
            $volnum = explode(".sq",$voltmp[1]);
            $volnum = intval($volnum[0])+1;
            $nextfile = $volname."_v".$volnum.".sql";
            if(file_exists("{$backupdir}/{$nextfile}")){
                Session::Set('notice', "程序将在3秒钟后自动导入此分卷备份的下一部份：文件{$nextfile}");
                Session::Set('nextfile', $nextfile);
				_retore_script();
            }else{
                Session::Set('notice', "此分卷备份全部导入成功");
            }

			_go_reload();
        }
    }

    if($_POST['restorefrom']=="localpc"){
        switch ($_FILES['myfile']['error']){
            case 1:
            case 2:
            $msgs = "您上传的文件大于服务器限定值，上传未成功";
            break;
            case 3:
            $msgs = "未能从本地完整上传备份文件";
            break;
            case 4:
            $msgs = "从本地上传备份文件失败";
            break;
            case 0:
            break;
        }
        if($msgs){
			Session::Set('error', $msgs);
			_go_reload();
        }

		if ( true === backup_import($_FILES['myfile']['tmp_name'])) {
			Session::Set('notice', "本地备份文件上传成功");
		}else {
			Session::Set('error', "本地备份文件导入数据库失败");
		}
		_go_reload();
	}

	if($_SESSION['nextfile']) {

		$serverfile = strval($_SESSION['nextfile']);
		$filename = $backupdir .'/' .$serverfile;
		if( true === backup_import($filename)) {
			Session::Set('notice', "{$serverfile}成功导入数据库");
		}
		else {
			Session::Set('error', "{$serverfile}导入失败");
			_go_reload();
		}

		$voltmp = explode("_v", $serverfile);
		$volname = $voltmp[0];
		$volnum = explode(".sq",$voltmp[1]);
		$volnum = intval($volnum[0])+1;
		$nextfile = $volname."_v".$volnum.".sql";
		if(file_exists("{$backupdir}/{$nextfile}")){
			Session::Set('notice', "程序将在3秒钟后自动导入此分卷备份的下一部份：文件{$nextfile}");
			Session::Set('nextfile', $nextfile);
			_retore_script();
		}else{
			Session::Set('notice', "此分卷备份全部导入成功");
		}
		_go_reload();
	}
}

$show = array();
$show[] = "本功能在恢复备份数据的同时，将全部覆盖原有数据";
$show[] = "数据恢复只能恢复由本系统导出的数据文件，其他软件导出格式无法识别";
$show[] = "从本地恢复数据最大数据2M";
$show[] = "如果您使用了分卷备份，只需手工导入文件卷1，其他数据文件会由系统导入";

include template('manage_misc_restore');

function _retore_script() {
	$script = "<meta http-equiv='refresh' content='3; url=restore.php?action=restore' />" ;
	Session::Set('script', $script);
}
