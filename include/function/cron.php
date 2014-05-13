<?php
function run_cron() {
	global $INI;
	if (option_yes('cronsubscribe')) run_cron_subscribe();
}


function run_cron_subscribe() {
}
