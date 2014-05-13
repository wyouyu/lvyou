<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('ask');

$id = abs(intval($_GET['id']));
Table::Delete('ask', $id);
Session::Set('notice', "删除团购咨询({$id})记录成功");
redirect(udecode($_GET['r']));
