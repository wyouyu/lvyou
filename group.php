<?php
require_once(dirname(__FILE__) . '/app.php');

$count = Table::Count('team');
list($pagesize, $offset, $pagestring) = pagestring($count, $size);
$teams = DB::LimitQuery('team', array(
            'condition' => '',
            'order' => 'ORDER BY `sort_order` DESC, `id` DESC',
            'size' => $pagesize,
            'offset' => $offset,
            ));
if (!$teams) { redirect( WEB_ROOT . '/team/index.php'); }

$now = time();
$detail = array();

foreach($teams AS $index => $team) {

	if($team['end_time']<$team['begin_time']){$team['end_time']=$team['begin_time'];}
	$diff_time = $left_time = $team['end_time']-$now;
	if ( $team['team_type'] == 'seconds' && $team['begin_time'] >= $now ) {
		$diff_time = $left_time = $team['begin_time']-$now;
	}

	/* progress bar size */
	$detail[$team['id']]['bar_size'] = ceil(190*($team['now_number']/$team['min_number']));
	$detail[$team['id']]['bar_offset'] = ceil(5*($team['now_number']/$team['min_number']));

	$left_day = floor($diff_time/86400);
	$left_time = $left_time % 86400;
	$left_hour = floor($left_time/3600);
	$left_time = $left_time % 3600;
	$left_minute = floor($left_time/60);
	$left_time = $left_time % 60;

	$detail[$team['id']]['diff_time'] = $diff_time;
	$detail[$team['id']]['left_day'] = $left_day;
	$detail[$team['id']]['left_hour'] = $left_hour;
	$detail[$team['id']]['left_minute'] = $left_minute;
	$detail[$team['id']]['left_time'] = $left_time;
	$detail[$team['id']]['is_today'] = $team['begin_time'] + 3600*24 > time() ? 1:0;

	/* state */
	$team['state'] = team_state($team);
	$teams[$index] = $team;
}

$miaosha = getItemsByCondition(array('is_miaosha'=>1));
//print_r($miaosha);
$is_chaozhi = getItemsByCondition(array('is_chaozhi'=>1));
$is_renqi = getItemsByCondition(array('is_renqi'=>1));
$today_list = getTodayList();
require_once(dirname(__FILE__) . '/search/groupsearch.php');
$filter_attr_str = isset($_GET['filter_attrr']) ? htmlspecialchars(trim($_GET['filter_attrr'])) : '0-0-0';
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

include template('team_meituan');
