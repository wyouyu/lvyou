<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

if ( $_POST ) {
    //exit(print_r($_FILES));
    $table = new Table('partner', $_POST);
    $table->SetStrip('location', 'other');
    $table->create_time = time();
    $table->city_id = abs(intval($table->city_id));
    $table->shouye = intval($table->shouye);
    $table->type = intval($table->type);
    $insert_array = array('city_id', 'title', 'group_id','create_time','head',
        'location', 'other', 'contact', 'mobile', 'phone','address','image',  'image2', 'longlat','shouye','hot','notice','type'
    );
    $update_array =array(
        'city_id', 'title', 'group_id','head',
        'location', 'other', 'contact', 'mobile', 'phone','address','image',  'image2', 'longlat','shouye','hot','notice','type'
    );
    if($_POST['id'])
    {
        $partner = Table::Fetch('partner', $_POST['id']);
        $imgarr = explode('|',substr($partner['image2'],0,-1));
        //exit(print_r($imgarr));
        $table->image = upload_image('main_page', $partner['image'], 'team', true);
        $table->image2 = edit_upload_images('upload_image', $partner['image2'], 'team',false,$imgarr);
        $table->SetPk('id',$_POST['t_id']);
        $table->update($update_array);
        Session::Set('notice', '编辑成功');
    }
    else
    {
        
        $table->image = upload_image('main_page', null, 'team', true);
        $table->image2 = upload_images('upload_image', null, 'team');
        
        $table->insert($insert_array);
        Session::Set('notice', '创建成功');
    } 
    redirect( WEB_ROOT . '/manage/hotel/index.php');
}
else
{
    if($_GET['id'])
    {
        $cond['id'] = $_GET['id'];
        $partner = DB::LimitQuery('partner', array(
            'condition' => $cond,
            'one' => true,
        ));
        $partner['image2'] = explode('|',substr($partner['image2'],0,-1));
    }
    include template('manage_hotel_create');
}


