<?php
require_once(dirname(__FILE__) . '/app.php');

include('./search/hotelsearch.php');     
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
if($_GET['hotelCityId']) $condition['city_id'] = $_GET['hotelCityId'];

if($_GET['txtHotelName'])
{
    $keywords = $_GET['txtHotelName'];
    $condition[] = "title like '%".$keywords."%'";
}
$condition['type']=2;
$count = Table::Count('partner', $condition);
list($pagesize, $offset, $pagestring) = pagestring_search($count, 4);
$partners = DB::LimitQuery('partner', array(
    'condition' => $condition,
    'order' => 'ORDER BY id DESC',
    //'size' => $pagesize,
    //'offset' => $offset,
));
$city_ids = Utility::GetColumn($partners, 'city_id');
$citys = Table::Fetch('category',$city_ids);
$allinfo =array();
foreach($partners as $key=> $value)
{
    
    /*获取最新评论*/
    $comment = DB::LimitQuery('comment',array(
        'condition'=>array('partner_id'=>$value['id']),
        'order'=>'order by c_id desc',
        'one'=>true
    ));
    /*判断是否为匿名用户评论*/
    if($comment['user_id']==99999)
    {
        $value['commentuser'] = '匿名用户';
    }
    else
    {
        $user = Table::Fetch('user',$comment['user_id']);
        $value['commentuser'] = $user['username'];
    }
    $value['comment'] = $comment;
    /*end*/
    /*获取评论总数*/
    $count = Table::Count('comment',array('partner_id'=>$value['id']));
    $value['commentnum'] = $count;
    /*end*/
    $price = array();
    $cond = array();
    $cond['t_partner'] = $value['id'];
    $tickets = DB::LimitQuery('ticket', array(
        'condition' => $cond,
        'order' => 'ORDER BY id',
    ));
    
    $tejia = array();$chengren =array();$ertong = array();$taopiao = array();$xuesheng = array();
    foreach($tickets as $t)
    {
        
        switch($t['t_cate'])
        {
            case 1:
            $tejia[] = $t;break;
            case 2:
            $chengren[] = $t;break;
            case 3:
            $ertong[] = $t;break;
            case 4:
            $taopiao[] = $t;break;
            case 5:
            $xuesheng[] = $t;break;
            default:$qita[] = $t; break;
        }
        $price[] = $t['t_price'];
    }
    sort($price);
    $value['minprice'] = $price[0];
    
    if($_GET['price'])
    {
        if($_GET['price']==1)
        {
            if(floor($value['minprice'])<0||floor($value['minprice'])>100)
            {
                continute;
            }
        }
        if($_GET['price']==2)
        {
            if(floor($value['minprice'])<100||floor($value['minprice'])>200)
            {
                continue;
            }
        }
        if($_GET['price']==3)
        {
            if(floor($value['minprice'])<200||floor($value['minprice'])>500)
            {
                continute;
            }
        }
        if($_GET['price']==4)
        {
            if(floor($value['minprice'])<500)
            {
                continute;
            }
        }
    }
    
    $value['tickets'] = $tickets;
    $value['chengren'] = $chengren;
    $value['ertong'] = $ertong;
    $value['taopiao'] = $taopiao;
    $value['xuesheng'] = $xuesheng;
    $value['tejia'] = $tejia;
    
    $allinfo[] =$value; 
}
//热销排行
$hotsell_sql ="select count(a.id) as num,a.partner_id,b.title from `order` as a inner join partner as b on a.partner_id=b.id where b.type=2 and a.state='pay' group by partner_id order by num desc limit 0,8";
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
$current = 'hotel';
include template('hotel_list');
?>