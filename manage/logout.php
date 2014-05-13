<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if (isset($_SESSION['admin_id'])) {
	unset($_SESSION['admin_id']);
}

redirect( WEB_ROOT . '/manage/index.php');
