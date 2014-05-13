<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

if ( $_POST ) {
	unset($_POST['commit']);
    $table = new Table('tesecai', $_POST);
    
    $insert_array = array('pid', 'c_name', 'image'
    );
    $update_array =array(
       'pid', 'c_name', 'image'
    );
    if($_POST['id'])
    {
        $partner = Table::Fetch('tesecai', $_POST['id']);

        $table->image = upload_image('upload_image', $partner['image'], 'team', true);


        $table->SetPk('id',$_POST['id']);
        $table->update($update_array);
        Session::Set('notice', '编辑成功');
    }
    else
    {
        
        $table->image = upload_image('upload_image', null, 'team',true);
        $table->insert($insert_array);
        Session::Set('notice', '创建成功');
    } 
    redirect( WEB_ROOT . '/manage/hotel/tesecai.php?id='.$_POST['pid']);
}
else
{
	$pid = $_GET['id'];
	$hid = $_GET['hid'];
    if($hid)
    {
        $cond['id'] = $hid;
        $tesecai = DB::LimitQuery('tesecai', array(
            'condition' => $cond,
            'one' => true,
        ));
		
		
    }

	$all_hui_yi = DB::LimitQuery('tesecai', array(
            'condition' => array('pid'=>$pid),
            //'one' => true,
        ));
    include template('manage_hotel_tesecai_create');
}


