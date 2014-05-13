<?php
require_once(dirname(__FILE__) . '/app.php');
$order_id = intval($_GET['id']);
include template('feedback_index');

