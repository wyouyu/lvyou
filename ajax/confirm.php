<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
need_partner();


$id = abs(intval($_GET['id']));

Table::UpdateCache('order',$id,array(
            'is_confirm'=>'1',
    ));
redirect( WEB_ROOT . '/biz/index.php');
