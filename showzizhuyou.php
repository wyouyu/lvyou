<?php
require_once(dirname(__FILE__) . '/app.php');
$pid = $_GET['id'];
$partner = DB::LimitQuery('partner',array(
                'condition'=>array('id'=>$pid),
                'one'=>true
                ));


if(!$partner)
{
    showmessage('您访问的商户不存在');
}

$scenery_list = DB::LimitQuery('zizhuyou',array(
                'condition'=>array('partner_id2'=>$partner['id']),
                'one'=>true
                ));
$partner['tese'] = $scenery_list['tese'];
$scenery_ids = explode(",",$scenery_list['partner_id1']);

//print_r($scenery_ids);

$scenery_arr = array();
$jingqu_ticket = array();
$zhusu_ticket = array();
foreach($scenery_ids as $key=> $id)
{
	$scenery_arr[$key] = DB::LimitQuery('partner',array(
                'condition'=>array('id'=>$id),
                'one'=>true
                ));
	$scenery_arr[$key]['pics'] = explode('|',substr($scenery_arr[$key]['image2'],0,-1));
	//print_r($scenery_arr[$key]['pics']);
	$jingqu_ticket[$key]['ticket'] = DB::LimitQuery('ticket',array(
                'condition'=>array('t_partner'=>$id,'type'=>1),
                //'one'=>true
                ));
	$jingqu_ticket[$key]['sname'] = $scenery_arr[$key]['title'];

	$zhusu_ticket[$key]['ticket'] = DB::LimitQuery('ticket',array(
                'condition'=>array('t_partner'=>$id,'type'=>2),
                //'one'=>true
                ));
	$zhusu_ticket[$key]['sname'] = $scenery_arr[$key]['title'];
	$zhusu_ticket[$key]['pid'] = $scenery_arr[$key]['id'];
	$jingqu_ticket[$key]['pid'] = $scenery_arr[$key]['id'];

}

//print_r($jingqu_ticket);
//同城景点
$same_city_scenery_e = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'city_id' => $partner['city_id'],
                            'type' => 1,
                            'id<>'.$partner['id']
                            ) ,
                            'size'=>6,
                            ));
$same_city_scenery = array();
foreach($same_city_scenery_e as $e)
{
    $e_tickets = DB::LimitQuery('ticket',array(
        'condition'=>array('t_partner'=>$e['id'],
                    't_status'=>1,
        )
    ));
    foreach($e_tickets as $e_t)
    {
        $e_price[] = $e_t['t_price'];
    }
    sort($e_price);
    $e_minprice = $e_price[0];
    $e['minprice'] =$e_minprice;
    $same_city_scenery[] = $e;
}
//同城度假村
$hot_zizhu = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'type' => 4,
                            'id<>'.$partner['id'],
							'hot'=>1
                            ) ,
                            'size'=>5,
                            ));


/*如果是度假村 获取房型*/
//if($_GET['type']==2)
//{
    $rooms = DB::LimitQuery('ticket',array(
                'condition'=>array('t_partner'=>$partner['id'],'type'=>2),
                'order'=>'ORDER BY sort DESC',      
        ));
//}


/*推荐景点*/
$recommends = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'hot' => '1',
                            ) ,
                            'size'=>8,
                            ));;

$comments = DB::LimitQuery('comment', array(
                           'condition' => array( 
                            'partner_id' => $pid,
                            
                            ) ,
                            'order'=>'order by c_time',
                            'size'=>10,
                            ));
$user_ids = Utility::GetColumn($comments,'userid');
$users = Table::Fetch('user',$user_ids);  

$count1 = Table::Count('comment',array('partner_id' => $pid));
      

$city_id = Table::Fetch('category',$partner['city_id']);
             
if($_GET['type']==2)
{
    $comments_tuijian = Table::Count('comment', array(
                            'partner_id' => $pid,
                            'tuijian'=>1
                            ));
    $comments_butuijian = Table::Count('comment', array(
                            'partner_id' => $pid,
                            'tuijian'=>2
                            )
                            );
   
    include template('holiday_village');
}
else
{
    include template('/zizhuyou/subpage');
}

?>
