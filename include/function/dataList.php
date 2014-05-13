<?php
function getListByType($limit = 6,$type = 0)
{
    $data = array();
    $cityPartners = array();
    $city_ids = getRcommendCitys($limit,$type);
    foreach($city_ids as $value)
    {
        if($value['id']==0)//全部景区
        {
            $partners = DB::LimitQuery('partner', array(
                                'condition' => array( 
                                    'shouye' => '1',
                                    'type'=>$type,
                                ) ,
                                'size'=>10,
                                'order'=>'ORDER BY head DESC'
                                ));
        }
        else//某个城市的景区
        {
            $partners = DB::LimitQuery('partner', array(
                               'condition' => array( 
                                    'shouye' => '1',
                                    'type'=>$type,
                                    'city_id' =>$value['id']
                                ) ,
                                'size'=>10,
                                'order'=>'ORDER BY head DESC'
                                ));
        }
        foreach($partners as $key=>$p)
        {
            if($type==4) //自助游的价格 直接在 商户里面定义 
            {
                $ziZhuInfo = DB::LimitQuery('zizhuyou', array(
                               'condition' => array( 
                                    'partner_id2' => $p['id'],
                                ) ,
                                'one'=>true,
                    ));
                $partners[$key]['price'] = $ziZhuInfo['price'];
            }
            else
            {
                $partners[$key]['price'] = GetMinPrice($p['id']);
            }
            
            
            //获取 出售份数 出售份数=实际订单数量+虚拟数量
            $count = Table::Count('order',array('partner_id'=>$p['id']));
	    $partners[$key]['sellnum'] = $count+$p['xuni'];
        }
        $cityPartners[$value['id']] =$partners; 
    }
    $data['city_ids'] = $city_ids;
    $data['partners'] = $cityPartners;
    return $data;
}


/*
return Type 0,1,2,3,4...
 *  */
function getRcommendCitys($limit=6,$type = 0)
{
        //按category 的降序排列 选择前6个城市
        $recommendCitys = DB::LimitQuery('category', array(
                                'condition' => array( 'zone' => 'city','display'=>'Y',) ,
                                 'order' =>'order by sort_order desc',
                                 'size'=>$limit,
                            ));
        $cityIds = array(array('id'=>'0','name'=>'全部'));//0 带表全部 把0 放入城市id数组中
        foreach($recommendCitys as $citys)
        {
            $cityIds[] = array('id'=>$citys['id'],'name'=>$citys['name']);
        }
        return $cityIds;
}

