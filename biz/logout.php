<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if (isset($_SESSION['partner_id'])) {
	unset($_SESSION['partner_id']);
}

redirect( WEB_ROOT . '/biz/login.php');
