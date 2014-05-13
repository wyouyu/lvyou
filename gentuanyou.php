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

$tehui_partners = getgentuantehui();

$xihuan = getxihuan();

$zhoubian = getzhoubian();

$guonei = getguonei();

$chujing = getchujing();

$current = 'gentuanyou';
include template('/gentuanyou/gentuan');


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
