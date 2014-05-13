<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

if ( $_POST ) {
    //exit(print_r($_FILES));
    $table = new Table('partner', $_POST);

    $table->create_time = time();
    $table->city_id = abs(intval($table->city_id));
    $table->shouye = intval($table->shouye);
    $table->password = ZPartner::GenPassword($table->password);
    
    $insert_array = array('city_id', 'title', 'group_id','create_time',
        'location', 'other', 'contact', 'mobile', 'phone','address','image','shouye','hot','notice','jibie','type','head','xuni'
    );
    $update_array =array(
        'city_id', 'title', 'group_id',
        'location', 'other', 'contact', 'mobile', 'phone','address','image','shouye','hot','notice','jibie','type','head','xuni'
    );
    $jingqu_array = array(
        'partner_id1','partner_id2','price','tese'
    );
    $jingqu_update_array = array('partner_id1','price','tese');
    if($_POST['id'])
    {
        $partner = Table::Fetch('partner', $_POST['id']);
       
        $table->image = upload_image('main_page', $partner['image'], 'team', true);
       // $table->image2 = edit_upload_images('upload_image', $partner['image2'], 'team',false,$imgarr);
        $table->SetPk('id',$_POST['id']);
        $table->update($update_array);
        //exit(print_r($_POST['jingqu']));
        $_POST2['partner_id1'] = implode(',',$_POST['jingqu']);
        $_POST2['price'] = $_POST['price'];
        $_POST2['tese']  = $_POST['tese'];
       
        $table2 =  new Table('zizhuyou', $_POST2);
        $table2->SetPk('id',$_POST['tid']);
        $table2->update($jingqu_update_array);
        Session::Set('notice', '编辑成功');
    }
    else
    {
        
        $table->image = upload_image('main_page', null, 'team', true);
        
        $insert_id = $table->insert($insert_array);
		
        $_POST2['partner_id1'] = implode(',',$_POST['jingqu']);
        $_POST2['partner_id2'] = $insert_id;
        $_POST2['price'] = $_POST['price'];
        $_POST2['tese']  = $_POST['tese'];
        $table2 =  new Table('zizhuyou', $_POST2);
        $table2->insert($jingqu_array);
        Session::Set('notice', '创建成功');
    } 
    redirect( WEB_ROOT . '/manage/zizhuyou/index.php');
}
else
{
    if($_GET['id'])
    {
        $cond['partner_id2'] = $_GET['id'];
        $partner_teams = DB::LimitQuery('zizhuyou', array(
            'condition' => $cond,
            'one' => true,
        ));
       // print_r($partner_teams);
        $cond2['id'] = $_GET['id'];
        $partner = DB::LimitQuery('partner', array(
            'condition' => $cond2,
            'one' => true,
        ));
        $partner['teams'] = explode(',',$partner_teams['partner_id1']);
    }
    //print_r($partner['teams']);
    $jd = DB::LimitQuery('partner', array(
        'condition' => array( 'type' => 1, ),
        'order' => 'ORDER BY id DESC',
    ));
    $option = '<option value="" selected>-选择途经景区-</option>';
    foreach($jd as $key=>$v)
    {
        $option .= '<option value="'.$key.'">'.$v['title']."</option>";
    }
    //print_r($option);
    include template('manage_zizhuyou_create');
}


