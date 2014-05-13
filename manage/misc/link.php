<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$count = Table::Count('friendlink');
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$links = DB::LimitQuery('friendlink', array(
			'order' => 'order by sort_order desc',
			'offset' => $offset,
			'size' => $pagesize,
			));

include template('manage_misc_link');
