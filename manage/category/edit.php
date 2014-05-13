<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/search/config.php');
need_manager();
need_auth('admin');

$id = abs(intval($_REQUEST['id']));
$category = Table::Fetch('category', $id);
$table = new Table('category', $_POST);
$table->letter = strtoupper($table->letter);
$uarray = array( 'zone', 'ename', 'letter', 'name', 'czone','fid', 'display', 'sort_order', 'relate_data','jingxuan','resou'); 
$table->display = strtoupper($table->display)=='Y' ? 'Y' : 'N';
$table->jingxuan = strtoupper($table->jingxuan)=='Y' ? 'Y' : 'N';
$table->resou = strtoupper($table->resou)=='Y' ? 'Y' : 'N';
if (!$_POST['name'] || !$_POST['ename'] || !$_POST['letter']) {
	Session::Set('error', '中文名称、英文名称、首字母均不能为空');
	redirect(null);
}

if ( ! preg_match('/^([a-z]+)$/i', $_POST['ename']) 
		|| ! preg_match('/^([a-z]+)$/i', $_POST['letter']) ) {
	Session::Set('error', '英文名称及首字母均需应为字母');
	redirect(null);
}

if ( $category ) {
	if ( $flag = $table->update( $uarray ) ) {
		Session::Set('notice', '编辑分类成功');
        $categories = DB::LimitQuery('category', array(
            'condition' => "zone='city'",
            'order' => 'ORDER BY display ASC, sort_order DESC, id DESC',
        ));
        $attr_cate1 = array();
        foreach($categories as $key=>$c)
        {
            $attr_cate1[$key]['id'] = $c['id'];
            $attr_cate1[$key]['cname'] = $c['name'];
            $attr_cate1[$key]['sort'] = $c['sort_order'];
        }
        $categories2 = DB::LimitQuery('category', array(
            'condition' => "zone='group'",
            'order' => 'ORDER BY display ASC, sort_order DESC, id DESC',
        ));
        $attr_cate2 = array();
        foreach($categories2 as $key2=>$c2)
        {
            $attr_cate2[$key2]['id'] = $c2['id'];
            $attr_cate2[$key2]['cname'] = $c2['name'];
            $attr_cate2[$key2]['sort'] = $c2['sort_order'];
        }
        $attr[0]['child'] = $attr_cate1;
        $attr[1]['child'] = $attr_cate2;
        _configure_save2($attr);
        //exit(print_r($attr));
	} else {
		Session::Set('error', '编辑分类失败');
	}
	option_category($category['zone'], true);
} else {
	if ( $flag = $table->insert( $uarray ) ) {
		Session::Set('notice', '新建分类成功');
	} else {
		Session::Set('error', '新建分类失败');
	}
}

option_category($table->zone, true);
redirect(null);

function _configure_save2($value) {
    $php = dirname(dirname(dirname(__FILE__))) . '/search/config.php';
    $v = "<?php\r\n\$attr = ";
    $v .= var_export($value, true);
    $v .=";\r\n?>";
    return file_put_contents($php, $v);
}
