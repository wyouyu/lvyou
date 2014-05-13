<?php
include_once('sms.php');

$target = "http://cf.lmobile.cn/submitdata/Service.asmx/g_Submit";
//替换成自己的测试账号,参数顺序和wenservice对应
$post_data = "sname=dlceshi5&spwd=YpjS5hg1&scorpid=&sprdid=1012818&sdst=13065029703&smsg=".rawurlencode("这是一条测试信息【蜗牛旅途网】");
//$binarydata = pack("A", $post_data);
echo $gets = Post($post_data, $target);
//请自己解析$gets字符串并实现自己的逻辑
//<State>0</State>表示成功,其它的参考文档
?>