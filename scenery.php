<?php
require_once(dirname(__FILE__) . '/app.php');

$province_id = intval($_GET['province_id']);
$city_id = intval($_GET['city_id']);
$theme_id = intval($_GET['theme_id']);
$price_id = intval($_GET['price_id']);
$jibie_id = intval($_GET['jibie_id']);

if($province_id!=0)
{
    $hotcities = option_child_category('city', false, true,$province_id);
}
include template('menpiao/menpiao_index');

?>
