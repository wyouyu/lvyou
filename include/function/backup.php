<?php
function backup_write_file($sql, $filename) {
    $re=true;
	$backupdir = DIR_ROOT . '/data';
	if( !@$fp=fopen("{$backupdir}/{$filename}","w+")) {
		return "failed to open target file";
	}
	$sql = "SET NAMES UTF8;\n{$sql}";
	if(!@fwrite($fp,$sql)) { return "failed to write file";}
	if(!@fclose($fp)) { return "failed to close target file";}
	return true;
}

function backup_down_file($sql,$filename){
	ob_get_clean();
	header("Content-Encoding: none");
	header("Content-Type: ".(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));
	header("Content-Disposition: ".(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'inline; ' : 'attachment; ')."filename=".$filename);
	header("Content-Length: ".strlen($sql));
	header("Pragma: no-cache");
	header("Expires: 0");
	die($sql);
}

function backup_make_header($table){
    $sql = "DROP TABLE IF EXISTS `".$table."`;\n";
    $result = DB::GetQueryResult("SHOW CREATE TABLE `{$table}`", true);
    $tmp = preg_replace("/[\r\n]+/", '', $result['create table']);
    $sql .= $tmp.";\n";
    return $sql;
}

function backup_make_record($table, $r){
    $comma = null;
	$num_fields = count($r); $r = array_values($r);
    $sql .= "INSERT INTO `{$table}` VALUES(";
	for($i = 0; $i < $num_fields; $i++){
		if (is_null($r[$i])) {
			$sql .= ($comma."NULL"); 
		} else {
			$sql .= ($comma."'".mysql_escape_string($r[$i])."'"); 
		}
		$comma = ",";
	}
	$sql .= ");\n";
	return $sql;
}

function backup_import($fname) {
	global $db;
	$sqls = file($fname);
	foreach($sqls as $sql){
        str_replace("\r","",$sql);
        str_replace("\n","",$sql);
		DB::Query($sql);
	}
    return true;
}
?>
