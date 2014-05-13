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
        'location', 'other', 'contact', 'mobile', 'phone','address','image','shouye','hot','notice','jibie','type','head'
    );
    $update_array =array(
        'city_id', 'title', 'group_id',
        'location', 'other', 'contact', 'mobile', 'phone','address','image','shouye','hot','notice','jibie','type','head'
    );
    $jingqu_array = array(
        'chufa_city','mudi_city','tianshu','fatuan_day','jiaotong','hotel_type','feiyong','notice','price','partner_id','leixing','tehui'
    );
    $jingqu_update_array = array('chufa_city','mudi_city','tianshu','fatuan_day','jiaotong','hotel_type','feiyong','notice','price','leixing','tehui');
    if($_POST['id'])
    {
        $partner = Table::Fetch('partner', $_POST['id']);
       
        $table->image = upload_image('main_page', $partner['image'], 'team', true);
        $table->SetPk('id',$_POST['id']);
        $table->update($update_array);
        //exit(print_r($_POST['jingqu']));
        $_POST2['chufa_city'] = $_POST['chufa_city'];
        $_POST2['mudi_city'] = $_POST['mudi_city'];
        $_POST2['tianshu'] = $_POST['tianshu'];
        $_POST2['fatuan_day'] = $_POST['fatuan_day'];
        $_POST['jiaotong'] = implode(',',$_POST['jiaotong']);
        $_POST2['jiaotong'] = $_POST['jiaotong'];
        $_POST2['hotel_type'] = $_POST['hotel_type'];
        $_POST2['feiyong'] = $_POST['feiyong'];
        $_POST2['notice'] = $_POST['notice'];
        $_POST2['price'] = $_POST['price'];
		$_POST2['leixing'] = $_POST['leixing'];
		$_POST2['tehui'] = $_POST['tehui'];
       
        $table2 =  new Table('gentuanyou', $_POST2);
        $table2->SetPk('id',$_POST['tid']);
        $table2->update($jingqu_update_array);
        Session::Set('notice', '编辑成功');
    }
    else
    {
        
        $table->image = upload_image('main_page', null, 'team', true);
        
        $insert_id = $table->insert($insert_array);
		
        $_POST2['chufa_city'] = $_POST['chufa_city'];
        $_POST2['mudi_city'] = $_POST['mudi_city'];
        $_POST2['tianshu'] = $_POST['tianshu'];
        $_POST2['fatuan_day'] = $_POST['fatuan_day'];
        $_POST['jiaotong'] = implode(',',$_POST['jiaotong']);
        $_POST2['jiaotong'] = $_POST['jiaotong'];
        $_POST2['hotel_type'] = $_POST['hotel_type'];
        $_POST2['feiyong'] = $_POST['feiyong'];
        $_POST2['notice'] = $_POST['notice'];
        $_POST2['price'] = $_POST['price'];

		$_POST2['leixing'] = $_POST['leixing'];
		$_POST2['tehui'] = $_POST['tehui'];
        $_POST2['partner_id']  = $insert_id;
        //exit(print_r($_POST2));
        $table2 =  new Table('gentuanyou', $_POST2);
        $table2->insert($jingqu_array);
        Session::Set('notice', '创建成功');
    } 
    redirect( WEB_ROOT . '/manage/gentuan/index.php');
}
else
{
    if($_GET['id'])
    {
        $cond['partner_id'] = $_GET['id'];
        $partner_teams = DB::LimitQuery('gentuanyou', array(
            'condition' => $cond,
            'one' => true,
        ));
        $partner_teams['jiaotong_arr'] = explode(',',$partner_teams['jiaotong']);
        $cond2['id'] = $_GET['id'];
        $partner = DB::LimitQuery('partner', array(
            'condition' => $cond2,
            'one' => true,
        ));
    }

    include template('manage_gentuanyou_create');
}


