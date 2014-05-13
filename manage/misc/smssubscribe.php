<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

$like = strval($_GET['like']);
$cs = strval($_GET['cs']);

/* build condition */
$condition = array('enable' => 'Y',);
if ($like) { 
	$condition[] = "mobile like '%".mysql_escape_string($like)."%'";
}
if ( $cs ) {
	$cscity = DB::LimitQuery('category', array(
				'condition' => array(
					'zone' => 'city',
					'name' => $cs,
					),
				'one' => true,
				));
	if ($cscity) $condition['city_id'] = $cscity['id'];
	else $cs = null;
}
/* end */

$count = Table::Count('smssubscribe', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 50);

$subscribes = DB::LimitQuery('smssubscribe', array(
			'condition' => $condition,
			'order' => 'ORDER BY id DESC',
			'size' => $pagesize,
			'offset' => $offset,
));

$city_ids = Utility::GetColumn($subscribes, 'city_id');

$cities = Table::Fetch('category', $city_ids);

include template('manage_misc_smssubscribe');
