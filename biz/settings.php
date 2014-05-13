<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_partner();
$partner_id = abs(intval($_SESSION['partner_id']));
$login_partner = $partner = Table::Fetch('partner', $partner_id);

if ( $_POST ) {
	$table = new Table('partner', $_POST);
	$table->SetStrip('location', 'other');
	$table->SetPk('id', $partner_id);
	$update = array(
		'title', 'bank_name', 'bank_user', 'bank_no',
		'location', 'other', 'homepage', 'contact', 'mobile', 'phone',
		'address',
	);
	if ( $table->password == $table->password2 && $table->password ) {
		$update[] = 'password';
		$table->password = ZPartner::GenPassword($table->password);
	}
	$flag = $table->update($update);
	if ( $flag ) {
		Session::Set('notice', '修改商户信息成功');
		redirect( WEB_ROOT . "/biz/settings.php");
	}
	Session::Set('error', '修改商户信息失败');
	$partner = $_POST;
}

include template('biz_settings');
