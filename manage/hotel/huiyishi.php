<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

if ( $_POST ) {
	unset($_POST['commit']);
    $table = new Table('huiyishi', $_POST);
    
    $insert_array = array('pid', 'sort', 'm_name','image','m_num',
        'm_squre', 'qita', 'shebei', 'half_or_prrice', 'half_curr_prrice','full_or_prrice','full_curr_prrice'
    );
    $update_array =array(
       'pid', 'sort', 'm_name','image','m_num',
        'm_squre', 'qita', 'shebei', 'half_or_prrice', 'half_curr_prrice','full_or_prrice','full_curr_prrice'
    );
    if($_POST['id'])
    {
        $partner = Table::Fetch('huiyishi', $_POST['id']);

        $table->image = upload_image('upload_image', $partner['image'], 'team', true);
		
		$shebei_str = implode(',',$_POST['shebei']);
		$table->shebei = $shebei_str;

        $table->SetPk('id',$_POST['id']);
        $table->update($update_array);
        Session::Set('notice', '编辑成功');
    }
    else
    {
        $shebei_str = implode(',',$_POST['shebei']);
		$table->shebei = $shebei_str;
        $table->image = upload_image('upload_image', null, 'team',true);
        //exit(print_r($table->image));
        $table->insert($insert_array);
        Session::Set('notice', '创建成功');
    } 
    redirect( WEB_ROOT . '/manage/hotel/huiyishi.php?id='.$_POST['pid']);
}
else
{
	$pid = $_GET['id'];
	$hid = $_GET['hid'];
    if($hid)
    {
        $cond['id'] = $hid;
        $metting = DB::LimitQuery('huiyishi', array(
            'condition' => $cond,
            'one' => true,
        ));
		//foreach($metting as $key=>$m)
		//{
			$metting['shebei'] = explode(',',$metting['shebei']);
		//}
		
    }

	$all_hui_yi = DB::LimitQuery('huiyishi', array(
            'condition' => array('pid'=>$pid),
            //'one' => true,
        ));
    include template('manage_hotel_huiyishi_create');
}


