<?php
function current_manageteam($selector='edit', $id=0) {
	$selector = $selector ? $selector : 'edit';
	$a = array(
		"/manage/team/edit.php?id={$id}" => '基本信息',
		"/manage/team/editvoucher.php?id={$id}" => '商户券信息',
		"/manage/team/editzz.php?id={$id}" => '杂项信息',
		"/manage/team/editseo.php?id={$id}" => 'SEO信息',
	);
	$l = "/manage/team/{$selector}.php?id={$id}";
	return current_link($l, $a);
}
