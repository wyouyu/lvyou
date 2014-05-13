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
/*门票*/
$cityids = array(0,9,1,2,10,6,15);
$tickets =array();
//exit(print_r($tickets));
foreach($cityids as $value)
{
    $ticket = array();
    if($value==0)
    {
        $partners = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'shouye' => '1',
                            'type'=>'1',
                            ) ,
                            'size'=>10,
                            'order'=>'ORDER BY head DESC'
                            ));
    }
    else
    {
        $partners = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'shouye' => '1',
                            'type'=>'1',
                            'city_id' =>$value
                            ) ,
                            'size'=>10,
                            'order'=>'ORDER BY head DESC'
                            ));
    }
    
    foreach($partners as $key=>$p)
    {
        $ticketPrice = DB::LimitQuery('ticket', array(
                           'condition' => array( 
                            't_partner' => $p['id'],
                            't_status' =>1,
                            't_cate'=>2,
                            ) ,
                            'one'=>true
                            ));
        if($ticketPrice['t_price'])
        {
            $p['ticketprice'] = $ticketPrice['t_price'];
        }
        else
        {
            $ticketPrice = DB::LimitQuery('ticket', array(
                           'condition' => array( 
                            't_partner' => $p['id'],
                            't_status' =>1,
                            ) ,
                            //'order'=>'ORDER BY id'
                            ));
            $p['ticketprice'] = $ticketPrice[0]['t_price'];
        }
        $ticket[] = $p;
    }
    $tickets[] = $ticket;
	
}
/*自助游*/
$zizhuyou =array();
foreach($cityids as $value)
{
    $ticket = array();
    if($value===0)
    {
        $zzy_partners = DB::LimitQuery('partner', array(
                               'condition' => array( 
                                'shouye' => '1',
                                'type'=>'4',
                                ) ,
                                'size'=>4,
                                'order'=>'ORDER BY head desc'
                                ));
    }
    else
    {
        $zzy_partners = DB::LimitQuery('partner', array(
                               'condition' => array( 
                                'shouye' => '1',
                                'type'=>'4',
                                'city_id' =>$value
                                ) ,
                                'size'=>4,
                                'order'=>'ORDER BY head DESC'
                                ));
    }
    foreach($zzy_partners as $key=>$p)
    {
        $zzy_ticketPrice = DB::LimitQuery('zizhuyou', array(
                           'condition' => array( 
                            'partner_id2' => $p['id'],
                          
                            ) ,
                            'one'=>true
                            ));
        $p['ticketprice'] = $zzy_ticketPrice['price'];
       // $p['tese'] = $zzy_ticketPrice['tese'];
	   $count = Table::Count('order',array('partner_id'=>$p['id']));
	   $p['count'] = $count+$p['xuni'];
        $ticket[] = $p;
    }
    $zizhuyou[] = $ticket;
    
}
//print_r($zizhuyou);
/*度假村*/

$holiday_village =array();
foreach($cityids as $value)
{
    $ticket = array();
    if($value==0)
    {
        $ho_partners = DB::LimitQuery('partner', array(
                               'condition' => array( 
                                'shouye' => '1',
                                'type'=>'2',
                                ) ,
                                'size'=>7,
                                'order'=>'ORDER BY head desc'
                                ));
    }
    else
    {
        $ho_partners = DB::LimitQuery('partner', array(
                               'condition' => array( 
                                'shouye' => '1',
                                'type'=>'2',
                                'city_id' =>$value
                                ) ,
                                'size'=>7,
                                'order'=>'ORDER BY head DESC'
                                ));
    }
    foreach($ho_partners as $key=>$p)
    {
        $ho_ticketPrice = DB::LimitQuery('ticket', array(
                           'condition' => array( 
                            't_partner' => $p['id'],
                            't_status' =>1,
                            'type'=>2,
                            ) ,
                            'one'=>true
                            ));
        $p['ticketprice'] = $ho_ticketPrice['t_price'];
        
        $ticket[] = $p;
    }
    $holiday_village[] = $ticket;
    
}

//print_r($holiday_village);

/*热点新闻*/
$news = DB::LimitQuery('news', array(
                           'condition' => '',
                            'order'=>'ORDER BY id DESC'
                            ));
                            
$request_uri = 'index';
$current = 'index';
include template('index');

