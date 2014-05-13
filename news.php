<?php
require_once(dirname(__FILE__) . '/app.php');

$hotsell_sql ="select count(a.id) as num,a.partner_id,b.title from `order` as a inner join partner as b on a.partner_id=b.id where b.type=1 and a.state='pay' group by partner_id order by num desc limit 0,8";
$re = DB::GetQueryResult($hotsell_sql,false);
$result2 =array();
foreach($re as $r)
{
    $ticket_info = DB::LimitQuery('ticket',array('condition'=>array('t_partner'=>$r['partner_id'],'t_cate'=>2),'one'=>true));
    if(empty($ticket_info))
    {
        $ticket_info = DB::LimitQuery('ticket',array('condition'=>array('t_partner'=>$r['partner_id'])));
        $ticket_info = $ticket_info[0];
    }
    $r['ticket'] = $ticket_info;
    $result2[] = $r;
}

$id = abs(intval($_GET['id']));
if($id)
{
    if(!$news = Table::FetchForce('news', $id) ) {
        redirect( WEB_ROOT . '/index.php');
    }
    
    $news['begin_time'] = date('Y-m-d H:i:s',$news['begin_time']);

    $prenews = DB::LimitQuery('news', array(
                               'condition' => array( 
                                'id' => ($id-1),
                                ) ,
                                'one'=>true,
                                ));

    $nextnews = DB::LimitQuery('news', array(
                               'condition' => array( 
                                'id' => ($id+1),
                                ) ,
                                'one'=>true,
                                ));
    include template('view_news');
}
else
{
    $count = Table::Count('news');
    list($pagesize, $offset, $pagestring) = pagestring_search($count, 4);
    $news_lists = DB::LimitQuery('news', array(
                                'order'=>'order by begin_time DESC',
                                'size' => $pagesize,
                                'offset' => $offset,
                                ));
    include template('news_list');
}

