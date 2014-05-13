<?php
require_once(dirname(__FILE__) . '/app.php');

include('./search/config.php');     
$filter_attr_str = isset($_GET['filter_attr']) ? htmlspecialchars(trim($_GET['filter_attr'])) : '0';
$filter_attr = empty($filter_attr_str) ? '' : explode('-', $filter_attr_str);//获取属性列表
$result = array();
foreach($attr as $key=> $value)
{
    
    for($i=0;$i<count($attr);$i++)
    {
        $temp_arrt_url_arr[$i] = !empty($filter_attr[$i]) ? $filter_attr[$i] : 0;
    }
    foreach($value['child'] as $k=>$rr)
    {
        if(isset($filter_attr[$key])&&$filter_attr[$key]==$rr['id'])
        {
            $rr['selected'] = 1;
        }
        else
        {
            $rr['selected'] = 0;
        }
        $temp_arrt_url_arr[$key] = $rr['id'];//给每个 筛选选项赋值
        $im_url = implode('-',$temp_arrt_url_arr);//组成新的url
        $rr['url'] = $im_url;
        $value['child'][$k] = $rr;
    }
    $result[$key] = $value;
}
$condition = array();

if($_POST['keywords'])
{
    $keywords = $_POST['keywords'];
    $condition[] = "title like '%".$keywords."%'";
}
$condition['type']=3;

$count = Table::Count('partner', $condition);
list($pagesize, $offset, $pagestring) = pagestring_search($count, 10);

$partners = DB::LimitQuery('partner', array(
    'condition' => $condition,
    'order' => 'ORDER BY id DESC',
    'size' => $pagesize,
    'offset' => $offset,
));//print_r($partners);
//获取景点城市
$city_ids = Utility::GetColumn($partners, 'city_id');
$citys = Table::Fetch('category',$city_ids);


foreach($partners as $key=> $value)
{
    $zizhuyou = DB::LimitQuery('gentuanyou', array(
        'condition' => array('partner_id'=>$value['id']),
        'order' => 'ORDER BY id',
		'one'=>true
    ));

	$partners[$key]['chufa_city'] = $zizhuyou['chufa_city'];
	$partners[$key]['mudi_city'] = $zizhuyou['mudi_city'];
	$partners[$key]['jiaotong'] = $zizhuyou['jiaotong'];
	$partners[$key]['price'] = $zizhuyou['price'];
	$partners[$key]['hotel_type'] = $zizhuyou['hotel_type'];
}
//print_r($allinfo);
//热销排行
$hotsell_sql ="select count(a.id) as num,a.partner_id,b.title from `order` as a inner join partner as b on a.partner_id=b.id where b.type=1 and a.state='pay' group by partner_id order by num desc limit 0,8";
$re = DB::GetQueryResult($hotsell_sql,false);
$result2 =array();
foreach($re as $r)
{
    $ticket_info = DB::LimitQuery('ticket',array('condition'=>array('t_partner'=>$r['partner_id'],'t_cate'=>2),'one'=>true));
    if(empty($ticket_info))
    {
        $ticket_info = DB::LimitQuery('ticket',array('condition'=>array('t_partner'=>$r['partner_id'])));
        $ticket_info = $ticket_info[0];
    }
    $r['ticket'] = $ticket_info;
    $result2[] = $r;
}
$current = 'gentuanyou';
include template('gentuanyou/gentuan_list');
?>