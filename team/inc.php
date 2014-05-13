<?php
function current_ask($selector='ask', $id=0) {
	$a = array(
			"/team/ask.php?id={$id}" => '团购答疑',
			"/team/transfer.php?id={$id}" => '求购转让',
			);
	$l = "/team/{$selector}.php?id={$id}";
	return current_link($l, $a, true);
}
