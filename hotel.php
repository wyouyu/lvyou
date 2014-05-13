<?php
require_once(dirname(__FILE__) . '/app.php');

$recommend = DB::LimitQuery('partner',array(
        'condition'=>array('type'=>2),
        'size'=>8,
        'order'=>'order by head desc'
    ));
$city_ids = Utility::GetColumn($recommend,'city_id');
$citys = Table::Fetch('category',$city_ids);
$recommends =array();
foreach($recommend as $r)
{
    $comment = DB::LimitQuery('comment',array(
        'condition'=>array('partner_id'=>$r['id']),
        'size'=>1,
        'order'=>'order by head desc'
    ));
    $r['comment'] = $comment;
    $recommends[] = $r;
}

/*获得最低价格*/

$current = 'hotel';
include template('hotel');
?>