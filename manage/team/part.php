<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('team');

$key = $_GET["value"];

$condition = array();

if ($key) { 
	$condition[] = "title like '%".mysql_escape_string($key)."%'";
}

$partner = DB::LimitQuery('partner', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
));

if(!$partner) die('0');

$partners = Utility::OptionArray($partner, 'id', 'title');

die (Utility::Option($partners));


