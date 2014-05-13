<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

$condition = array();

/* filter */
$ptitle = strval($_GET['ptitle']);
if ($ptitle ) {
    $condition[] = "title LIKE '%".mysql_escape_string($ptitle)."%'";
}

$city_id = strval($_GET['city_id']);
if ($city_id) {
    $condition['city_id'] = $city_id;
}
$open = strval($_GET['open']);
if ($open) {
    $condition['open'] = $open;
}
/* filter end */
$condition['type']=3;
$count = Table::Count('partner', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$partners = DB::LimitQuery('partner', array(
    'condition' => $condition,
    'order' => 'ORDER BY  id DESC',
    'size' => $pagesize,
    'offset' => $offset,
));
foreach($partners as $key=>$v)
{
    $gentuan = DB::LimitQuery('gentuanyou', array(
        'condition' => array('partner_id'=>$v['id']),
        'one' => true,
    ));
    $partners[$key]['chufa'] = $gentuan['chufa_city'];
    $partners[$key]['mudi'] = $gentuan['mudi_city'];
    $partners[$key]['tianshu'] = $gentuan['tianshu'];
    $partners[$key]['riqi'] = $gentuan['fatuan_day'];
    $partners[$key]['price'] = $gentuan['price'];
}

include template('manage_gentuanyou_index');
