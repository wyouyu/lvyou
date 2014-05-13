<?php
require_once(dirname(__FILE__) . '/app.php');
$pid = $_GET['id'];
$partner = DB::LimitQuery('partner',array(
                'condition'=>array('id'=>$pid),
                'one'=>true
                ));
                //print_r($partner);
if(!$partner)
{
    showmessage('您访问的商户不存在');
}
$now = time();
$tickets = DB::LimitQuery('ticket',array(
                'condition'=>array(
							't_partner'=>$partner['id'],
                            't_status'=>1,
							'type'=>1,
							//'book_start_time<'.$now,
							//'book_end_time>'.$now,
                ),
				'order'=>'order by t_sort desc'
                ));

				/*$tickets2 = DB::LimitQuery('ticket',array(
                'condition'=>array('t_partner'=>$partner['id'],
                            't_status'=>1,
							
                ),
				'order'=>'order by t_sort desc'
                ));*/
//获取最低价格
/*foreach($tickets as $t)
{
    switch($t['t_cate'])
    {
        case 1:
        $tejia[] = $t;break;
        case 2:
        $chengren[] = $t;break;
        case 3:
        $ertong[] = $t;break;
        case 4:
        $taopiao[] = $t;break;
        case 5:
        $xuesheng[] = $t;break;
        default:$qita[] = $t; break;
    }
    $price[] = $t['t_price'];
}*/

//print_r($tickets);
$alltickt = array();
$t_cate_array = array();
foreach ($tickets as $key =>$t)
{
	if(!in_array($t['t_cate'],$t_cate_array))
	{
		$alltickt[$t['t_cate']]['name'] = DB::LimitQuery('category', array(
				   'condition' => array( 
					'id' => $t['t_cate'],
					
					) ,
						'one'=>true
					));
		$alltickt[$t['t_cate']]['ticket'][] = $t;
		array_push($t_cate_array,$t['t_cate']);
	}
	else
	{
		$alltickt[$t['t_cate']]['ticket'][] = $t;
	}
	$price[] = $t['t_price'];
	
}

//print_r($tickets);
sort($price);
$minprice = $price[0];




if($partner['image2'])
{
    $partnerImg = explode('|',$partner['image2']);
}
//print_r($partnerImg);
/*获取评论总数*/
$count = Table::Count('comment',array('partner_id'=>$pid));

    
list($long,$lat) = explode(',',$partner['longlat']);

$comments_one = DB::LimitQuery('comment',array(
                'condition'=>array('partner_id'=>$partner['id']),
                'order'=>'ORDER BY c_id DESC',
                'one'=>true
                
        ));
if($comments_one['userid']!=99999)
{
    $user = Table::Fetch('user',$comments_one['userid']);
}
//同城景点
$same_city_scenery_e = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'city_id' => $partner['city_id'],
                            'type' => 1,
                            'id<>'.$partner['id']
                            ) ,
                            'size'=>6,
                            ));
$same_city_scenery = array();
foreach($same_city_scenery_e as $e)
{
    $e_tickets = DB::LimitQuery('ticket',array(
        'condition'=>array('t_partner'=>$e['id'],
                    't_status'=>1,
        )
    ));
    foreach($e_tickets as $e_t)
    {
        $e_price[] = $e_t['t_price'];
    }
    sort($e_price);
    $e_minprice = $e_price[0];
    $e['minprice'] =$e_minprice;
    $same_city_scenery[] = $e;
}
//同城度假村
$same_city_holiday_village_f = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'city_id' => $partner['city_id'],
                            'type' => 2,
                            'id<>'.$partner['id']
                            ) ,
                            'size'=>3,
                            ));

$same_city_holiday_village = array();
foreach($same_city_holiday_village_f as $f)
{
    $f_tickets = DB::LimitQuery('ticket',array(
        'condition'=>array('t_partner'=>$f['id'],
                    't_status'=>1,
        )
    ));
    foreach($f_tickets as $f_t)
    {
        $f_price[] = $f_t['t_price'];
    }
    sort($f_price);
    $f_minprice = $f_price[0];
    $f['minprice'] =$f_minprice;
    $same_city_holiday_village[] = $f;
}

/*如果是度假村 获取房型*/
//if($_GET['type']==2)
//{
    $rooms = DB::LimitQuery('ticket',array(
                'condition'=>array('t_partner'=>$partner['id'],'type'=>2,'t_status'=>1,/*'book_start_time<{$now}','book_end_time>{$now}',*/),
                'order'=>'ORDER BY sort DESC',      
        ));

	$allroom = array();
	$t_room_array = array();
	foreach ($rooms as $key =>$r)
	{
		if(!in_array($r['t_cate'],$t_room_array))
		{
			$allroom[$r['t_cate']]['name'] = DB::LimitQuery('category', array(
					   'condition' => array( 
						'id' => $r['t_cate'],
					
						) ,
							'one'=>true
						));
			$allroom[$r['t_cate']]['room'][] = $r;
			array_push($t_room_array,$r['t_cate']);
		}
		else
		{
			$allroom[$r['t_cate']]['room'][] = $r;
		}
		$price[] = $r['t_price'];
		
	}
if(!$minprice)
{
	sort($price);
	$minprice = $price[0];
}
//}
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

/*推荐景点*/
$condition['city_id'] = $partner['city_id'];
$condition['hot'] = 1;
$recommends = DB::LimitQuery('partner', array(
                           'condition' => $condition,
                            'size'=>8,
                            ));;

$comments = DB::LimitQuery('comment', array(
                           'condition' => array( 
                            'partner_id' => $pid,
                            
                            ) ,
                            'order'=>'order by c_time',
                            'size'=>10,
                            ));
$user_ids = Utility::GetColumn($comments,'userid');
$users = Table::Fetch('user',$user_ids);  

$count1 = Table::Count('comment',array('partner_id' => $pid));
      
$comments2 = DB::LimitQuery('comment', array(
                           'condition' => array( 
                            'partner_id' => $pid,
                            
                            ) ,
                            'order'=>'order by c_time',
                           
                            ));
$manyi = 0;
$bumanyi = 0;
$i=0;
$manyidu = 0;
$baifenbi = 0;
foreach($comments2 as $comm)
{
    $fenshu = intval($comm['fuwunum'])+intval($comm['bianjienum'])+intval($comm['youhuinum']);
    $fuwunum += intval($comm['fuwunum']);
    $bianjienum +=intval($comm['bianjienum']);
    $youhuinum +=intval($comm['youhuinum']);
    if(($fenshu/3)>=4)
    {
        $manyi++;
        
    }
    else
    {
        $bumanyi++;
        
    }
    $i++;
}
if($i!=0)
{
    $baifenbi = ($manyi/$i)*100;
    $fuwunumavg = sprintf("%.1f",$fuwunum/$i);
    $numcss1 = str_replace('.','d',strval($fuwunumavg));
    $bianjienumvg = sprintf("%.1f",$bianjienum/$i);
    $numcss2 = str_replace('.','d',strval($bianjienumvg));
    $youhuinumavg = sprintf("%.1f",$youhuinum/$i);
    $numcss3 = str_replace('.','d',strval($youhuinumavg));
}

$city_id = Table::Fetch('category',$partner['city_id']);
//获取会议室
$huiyishi = DB::LimitQuery('huiyishi', array(
                           'condition' => array( 
                            'pid' => $pid,
                            
                            ) ,
                            'order'=>'order by sort desc',
                            ));
//特色菜
$tesecai = DB::LimitQuery('tesecai', array(
                           'condition' => array( 
                            'pid' => $pid,
                            
                            ) ,
                            //'order'=>'order by sort desc',
                            ));
/*右侧2个推荐位*/
$two_recomment = DB::LimitQuery('partner', array(
                           'condition' => array( 
                            'right_recomment' => 1,
                            
                            ) ,
                            'order'=>'order by head desc',
                            'size'=>2,
                            ));

if($_GET['type']==2)
{
    $comments_tuijian = Table::Count('comment', array(
                            'partner_id' => $pid,
                            'tuijian'=>1
                            ));
    $comments_butuijian = Table::Count('comment', array(
                            'partner_id' => $pid,
                            'tuijian'=>2
                            )
                            );
   
    include template('holiday_village');
}
else
{
    include template('subpage');
}

?>
