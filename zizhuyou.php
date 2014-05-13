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
                            'size'=>3,
                            ));
/*热销景点*/
$sellpartners = get_hot_scenery_ticket();





/*门票*/
$cityids = array(1,15,2,11,7);
$tickets =array();

foreach($cityids as $value)
{
    $ticket = array();
    $t_partners = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'shouye' => '1',
                            'city_id' =>$value,
                            'type'=>4
                            ) ,
                            'size'=>5,
                            'order'=>'ORDER BY id'
                            ));
    
    foreach($t_partners as $key=>$p)
    {
       
        $t_ticketPrice = DB::LimitQuery('zizhuyou', array(
                           'condition' => array( 
                            'partner_id2' => $p['id'],
                            ) ,
                            'one'=>true
                            ));

        $p['price'] = $t_ticketPrice['price'];
        $p['tese'] = strip_tags($t_ticketPrice['tese']);
       
        $ticket[] = $p;
    }
    $tickets[] = $ticket;
}

$tehui_partners = getPartnerByCondition();

//print_r($tehui_partners);
foreach($tehui_partners as $key=>$v)
{
	$tehui_partners[$key]['price'] = get_priced($v['id']);
    $tehui_partners[$key]['city'] = get_cityd($v['city_id']);
    if($p = intval($tehui_partners[$key]['price']))
    {
        $tehui_partners[$key]['youhui'] = intval($p*0.15);
    }
}
//print_r($tehui_partners);
$current = 'zizhuyou';
include template('/zizhuyou/zizhu');


function get_priced($id)
{
	$result = DB::LimitQuery('zizhuyou', array(
                    'condition'=>array('partner_id2'=>$id),
                    'size' => 1,
                    ));
    return $result[0]['price'];
}

function get_cityd($cid)
{
    $result = DB::LimitQuery('category', array(
                    'condition'=>array('zone'=>'city','id'=>$cid),
                    'size' => 1,
                    ));
    return $result[0]['name'];
}
?>
