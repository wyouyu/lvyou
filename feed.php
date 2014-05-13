<?php
require_once(dirname(__FILE__) . '/app.php');
header('Content-Type: application/rss+xml; charset=UTF-8');

$ename = strval($_GET['ename']);
if ($ename!='none') {
	$city = DB::LimitQuery('category', array(
		'condition' => array(
			'zone' => 'city',
			'ename' => $ename,
		),
		'one' => true,
	));
}

$rss = new UniversalFeedCreator();
$rss->useCached();
$rss->title = "{$INI['system']['sitename']} 今日团购";
$rss->description = "{$INI['system']['sitename']} 每天团购一次";
$rss->link = "{$INI['system']['wwwprefix']}";
$rss->syndicationURL = $INI['system']['wwwprefix'] . '/' . $PHP_SELF;

$image = new FeedImage();
$image->title = $INI['system']['sitename'];
$image->url = "{$INI['system']['imgprefix']}/static/css/i/logo.gif";
$image->link = "{$INI['system']['sitename']}";
$image->description = "Feed provided by zuitu.com";
$rss->image = $image;

$team = $teams = index_get_team($city['id']);
if($team['id']) { $teams = array(); $teams[] = $team; }

foreach($teams AS $team) {
	$item = new FeedItem();
	$item->title = $team['title'];
	$item->link = $INI['system']['wwwprefix'].'/team.php?id='.$team['id'];
	$item->description = $team['summary']  . "<br /><img src='".team_image($team['image'])."'/>" . $team['systemreview'];
	$item->date = $team['create_time'];
	$item->source = $INI['system']['wwwprefix'];
	$item->author = $city['name'];
	$rss->addItem($item);
}

$rss->outputFeed("ATOM2.0");
