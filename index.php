<?php
require_once(dirname(__FILE__) . '/app.php');

if(!$INI['db']['host']) redirect( WEB_ROOT . '/install.php' );
if($city&&option_yes('rewritecity')){
	redirect(WEB_ROOT."/{$city['ename']}");
}

/*精选 主题*/
$themes = DB::LimitQuery('category', array(
                           'condition' => array( 
                            'zone' => 'group',
                            'fid' => '0',
                            'jingxuan'=> 'Y',
                            ) ,
                            'size'=>4,
                            ));
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


//景区
$sceneryData = getListByType($limit = 6,$type = 1);
//自助游
$ziZhuYouData = getListByType($limit = 6,$type = 4);
//度假村
$hotelData = getListByType($limit = 6,$type = 2);


/*热点新闻*/
$news = DB::LimitQuery('news', array(
                           'condition' => '',
                            'order'=>'ORDER BY id DESC'
                            ));
$links = DB::LimitQuery('friendlink', array(
                           'condition' => '',
                            'order'=>'ORDER BY id DESC'
                            ));
                            
$request_uri = 'index';
$current = 'index';
include template('index');


