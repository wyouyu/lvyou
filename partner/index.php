<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$daytime = time();
$condition = array( 
		'open' => 'Y',
		);
$group_id = abs(intval($_GET['gid']));
if ($group_id) $condition['group_id'] = $group_id;

if (option_yes('citypartner') && ($cid=abs(intval($city['id']))) ) {
	$condition['city_id'] = $cid;
}

$count = Table::Count('partner', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 10);
$partners = DB::LimitQuery('partner', array(
	'condition' => $condition,
	'order' => 'ORDER BY head DESC, id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));
foreach($partners AS $id=>$one){
	team_state($one);
	if ($one['state']=='none') $one['picclass'] = 'isopen';
	if ($one['state']=='soldout') $one['picclass'] = 'soldout';
	$one['comment_num'] = ($one['comment_good']+$one['comment_bad']+$one['comment_none']);
	$one['reputation'] = ($one['comment_num']>0)? moneyit(number_format(100*($one['comment_good']/$one['comment_num']), 2)) : null;
	$partners[$id] = $one;
}

/* now_comments */
$now_cc = array(
	'state' => 'pay',
	'comment_display' => 'Y',
	'comment_time > 0',
	'partner_id > 0',
);
$now_comments = DB::LimitQuery('order', array(
	'condition' => $now_cc,
	'order' => 'ORDER BY comment_time DESC',
));
foreach($now_comments AS $k=>$v) {
		$v['grade'] = 'A';
		$v['grade'] = $v['comment_grade']=='none' ? 'P' : $v['grade'];
		$v['grade'] = $v['comment_grade']=='bad' ? 'F' : $v['grade'];
		$v['comment'] = htmlspecialchars($v['comment_content']);
		$v['timespan'] = $daytime - $v['comment_time'];
		$now_comments[$k] = $v;
}

$partner_ids = Utility::GetColumn($now_comments, 'partner_id');
$cpartners = Table::Fetch('partner', $partner_ids);

$user_ids = Utility::GetColumn($now_comments, 'user_id');
$users = Table::Fetch('partner', $user_ids);
/* end */

$category = Table::Fetch('category', $group_id);
$pagetitle = '品牌商户';
include template('partner_index');
