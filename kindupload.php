<?php
require_once(dirname(__FILE__) . '/app.php');
need_login();
list($year,$day) = explode('-', date('Y-md'));
$save_path = IMG_ROOT . "/team/{$year}/{$day}/";
RecursiveMkdir($save_path);
$save_url = WWW_ROOT ."/static/team/{$year}/{$day}/";
$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
$max_size = 4096000; //4M File
if (empty($_FILES) === false) {
	$file_name = $_FILES['imgFile']['name'];
	$tmp_name = $_FILES['imgFile']['tmp_name'];
	$file_size = $_FILES['imgFile']['size'];
    $info = Image::getImageType($tmp_name);
	if(empty($info)) {alert("请上传合法文件");}
	if(!$file_name) { alert("请选择文件"); }
	if(!@is_dir($save_path)) { alert("上传目录不存在"); }
	if(!@is_writable($save_path)) { alert("上传目录没有写权限"); }
	if(!@is_uploaded_file($tmp_name)) { alert("临时文件不是上传文件"); }
	if($file_size > $max_size) { alert("上传文件大小超过限制"); }
	$file_ext = end(explode('.', $file_name));
	$file_ext = trim($file_ext);
	$file_ext = strtolower($file_ext);
	if (!in_array($file_ext,$ext_arr)) {alert("上传文件类型不允许"); }
	$new_file_name = date("YmdHis").rand(10000,99999).'.'.$file_ext;
	$file_path = $save_path . $new_file_name;
	if (!move_uploaded_file($tmp_name, $file_path)) {
		alert("上传文件失败");
	}
	Image::convert($file_path, $file_path, 440);
	@chmod($file_path, 0644);

	$file_url = $INI['system']['imgprefix'] . "/static/team/{$year}/{$day}/". $new_file_name;
	
	header('Content-type: text/html; charset=UTF-8');
	die(json_encode(array('error' => 0, 'url' => $file_url)));
}

function alert($msg) {
	header('Content-type: text/html; charset=UTF-8');
	die(json_encode(array('error' => 1, 'message' => $msg)));
}
