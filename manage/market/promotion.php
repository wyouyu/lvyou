<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

if ( $_POST ) {
	$table = new Table('page', $_POST);
	$value = stripslashes($_POST['value']);
	$title = stripslashes($_POST['title']);
	$table->value = Utility::ExtraEncode(array(
		'value' => $value,
		'title' => $title,
	));
	$table->SetStrip('value');
	if ( $n ) {
		$table->SetPk('id', $id);
		$table->update( array('id', 'value') );
	} else {
		$table->insert( array('id', 'value') );
	}
}

include template('manage_market_promotion');
