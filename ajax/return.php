<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
$state = $_POST['state'];
$id =$_POST['id'];
$data =array();
if($state==1)
{
    $data = array('str'=>"<img src='/static/css/img/false.gif'>",'state'=>'2');
    DB::Update('order',$id,array('fadan'=>2));
    die(json_encode($data));
}
else
{
    $data = array('str'=>"<img src='/static/css/img/true.gif'>",'state'=>'1');
    DB::Update('order',$id,array('fadan'=>1));
    die(json_encode($data));
}
?>
