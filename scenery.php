<?php
require_once(dirname(__FILE__) . '/app.php');

/*热搜城市*/
$hotcitys = DB::LimitQuery('category', array(
                           'condition' => array( 
                            'zone' => 'city',
                            'fid' => '0',
                            'resou'=> 'Y',
                            ) ,
                            'size'=>6,
                            ));
/*热搜景点*/
$hotscenery = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'hot' => '1',
                            ) ,
							'order'=>'order by head desc',//越大 越显示在前面 方便手动控制
                            'size'=>3,
                            ));
/*热销景点*/
$sellpartners = get_hot_scenery_ticket();
//print_r($sellpartners);
/*旅游主题开始*/
$themes = DB::LimitQuery('category',array('condition'=>
                    array('zone'=>group),
                ));

/*最新订单*/

$neworders = DB::LimitQuery('order',array('condition'=>
                    array('state'=>'pay','type'=>1),
                    'order'=>'ORDER by create_time DESC',
                    'size'=>'5'
                ));
$user_ids = Utility::GetColumn($neworders,'user_id');
$users = Table::Fetch('user',$user_ids);

$partner_ids = Utility::GetColumn($neworders,'partner_id');
$partners = Table::Fetch('partner',$partner_ids);

//echo 123;
/*门票*/
$cityids = array(9,1,2,10,6,15,11,4);
$tickets =array();

foreach($cityids as $value)
{
    $ticket = array();
    $t_partners = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'shouye' => '1',
                            'city_id' =>$value,
							'type'=>1
                            ) ,
                            'size'=>8,
                            'order'=>'ORDER BY id'
                            ));
    
    //print_r($t_partners['sellpartners']);
    foreach($t_partners as $key=>$p)
    {
        $p['sellpartners'] = get_hot_scenery_ticket($value);
        $t_ticketPrice = DB::LimitQuery('ticket', array(
                           'condition' => array( 
                            't_partner' => $p['id'],
                            't_status' =>1,
                            't_cate'=>2,
                            ) ,
                            //'size'=>8,
                            //'order'=>'ORDER BY id'
                            'one'=>true
                            ));
        if($t_ticketPrice['t_price'])
        {
            $p['ticketprice'] = $t_ticketPrice['t_price'];
            $p['originalprice'] = $t_ticketPrice['t_origin'];
        }
        else
        {
            $t_ticketPrice = DB::LimitQuery('ticket', array(
                           'condition' => array( 
                            't_partner' => $p['id'],
                            't_status' =>1,
                            ) ,
                            //'order'=>'ORDER BY id'
                            ));
            $p['ticketprice'] = $t_ticketPrice[0]['t_price'];
            $p['originalprice'] = $t_ticketPrice[0]['t_origin'];
        }
        $ticket[] = $p;
    }
    $tickets[] = $ticket;
}
//print_r($tickets);
/*最新点评*/
$comments_arr =DB::LimitQuery('comment',array('condition'=>array('tuijian'=>NULL),'size'=>3,'order'=>'order by c_time'));
$comments = array();
foreach($comments_arr as $c)
{
    if((intval($c['fuwunum'])+intval($c['bianjienum'])+intval($c['youhuinum']))/3>4)
    {
        $c['haoping']=1;
    }
    else
    {
        $c['haoping']=0;
    }
    $comments[] = $c;
}
$partner_ids = Utility::GetColumn($comments,'partner_id');
$partnerss = Table::Fetch('partner',$partner_ids);
$current = 'scenery';
$month = date('m',time());

/*今日景点特卖会*/
$today_hot_sell = DB::LimitQuery('ticket', array(
                           'condition' => array( 
                            'temaihui' => '1',
                            ) ,
                            'size'=>3,
                            ));
foreach($today_hot_sell as $key=> $t)
{
	$sell_partner = Table::Fetch('partner',$t['t_partner']);
	
	$today_hot_sell[$key]['partner'] = $sell_partner;
	$order_num = Table::Count('order',array('team_id'=>$t));
	$today_hot_sell[$key]['order_num'] = $order_num;
}
//print_r($today_hot_sell);
include template('scenery2');

?>
