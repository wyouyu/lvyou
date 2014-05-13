<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
$action = strval($_GET['action']);
$id = $team_id = abs(intval($_GET['id']));
$team = Table::Fetch('team', $team_id);

if ( $action == 'remove' && $team['user_id'] == $login_user_id ) {
	DB::DelTableRow('team', array('id' => $team_id));
	json("jQuery('#team-list-id-{$team_id}').remove();", 'eval');
}
else if ( $action == 'ask' ) {
	$content = trim($_POST['content']);
	if ( $content ) {
		$table = new Table('ask', $_POST);
		$table->user_id = $login_user_id;
		$table->team_id = $team['id'];
		$table->city_id = $team['city_id'];
		$table->create_time = time();
		$table->content = htmlspecialchars($table->content);
		$table->insert(array('user_id','team_id','city_id','content','create_time', 'type'));
	}
	json(0);
}

json(0);
?>
