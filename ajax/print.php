<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
//$orderid = $_POST['id'];


	$orderids = $_POST['orderids'];
	$orderid = intval(substr($orderids,0,-1));

	$ids = explode(',',substr($orderids,0,-1));

	$orders= array();
	$partner_for_check =array();
	foreach($ids as $d)
	{
		$result =array();
		$result = Table::Fetch('order',$d);
		$orders[] =$result;
		$partner_for_check[] = $result['partner_id'];
	}


//exit(print_r($orders));
/*检测订单是否属于同一商户*/
if($_POST['action']=='check')
{
    $data['error'] = '0';
    foreach($partner_for_check as $pfc)
    {
        if($pfc!=$partner_for_check[0])
        {
            $data['error'] = '1';
            die(json_encode($data));
        } 
    }
}

//$orders = Table::Fetch('orders', $ids);
//exit(print_r($orders));

$partner_ids = Utility::GetColumn($orders, 'partner_id');
$partners = Table::Fetch('partner', $partner_ids);

$ticket_ids = Utility::GetColumn($orders, 'team_id');

$tickets = Table::Fetch('ticket', $ticket_ids);


foreach($tickets as $k=>$t)
{
	$category= DB::LimitQuery('category', array(
	'condition' => array('id'=>$t['t_cate']),
	
	'one' => true,
));
	$tickets[$k]['cate']  = $category['name'];
}


$order = Table::Fetch('order',$orderid);
//$outtime = date('Y-m-d',$order['outtime']);
$partner = Table::Fetch('partner',$order['partner_id']);
$ticket = Table::Fetch('ticket',$order['team_id']);
$type = array(1=>'特价票',2=>'成人票',3=>'儿童票',4=>'套票',5=>'学生票');
$paytype = array(1=>'景区现付',2=>'在线支付');
$printhtml = '';
$printhtml .='<table cellpadding="0" cellspacing="0"  class="reportTab" width="710px" style="margin: 0 auto; font-size: 14px">
<tr>
                    <td colspan="2" style=" text-align:left;">景区名称:
                        '.$partner['title'].'
                    </td>
                    <td colspan="2" style=" text-align:left;">
                       编号：<strong>'.$order['smsid'].'</strong>
                    </td>
                </tr>
                <tr>
                    <td>至/FROM</td>
                    <td>蜗牛旅途网</td>
                    <td>至/TO</td>
                    <td></td>
                </tr>
                <tr>
                    <td>传真/FAX</td>
                    <td>0531-66717918</td>
                    <td>传真/FAX</td>
                    <td></td>
                </tr>
                <tr>
                    <td>电话/TEL</td>
                    <td>0531-88069703
                        </td>
                    <td>电话/TEL</td>
                    <td></td>
                </tr>
                <tr>
                    <td>发件人/SEND</td>
                    <td>马民</td>
                    <td>收件人/ATTN</td>
                    <td>_________</td>
                </tr>
            </table>
            <table cellpadding="0" cellspacing="0" class="reportTab" width="710px" style="margin: 0 auto; font-size: 14px">
                <thead>
                <tr>
                    <th>客人姓名</th>
                    <th>联系电话</th>
                    <th>订单编号</th>
                    <th>门票类型</th>
                    <th>数量</th>
                    
                    <th>单价</th>
                    <th>结算价</th>
                    <th>游玩日期</th>
                    <th>付款方式</th>
                    <th>实到</th>
                </tr>
            </thead>
                <tbody>';
$total_price = 0;
$num = 0;
foreach($orders as $por)
{
	if($por['partner_id']==39||$por['partner_id']==46)
	{
		$por['mobile'] = '';
	}
	$total_price += $por['money'];
	$num +=intval($por['quantity']);
    $por['outtime'] = date('Y-m-d',$por['outtime']);
    //$danjian = intval($por['price'])- intval($por['fanxian']);
    $printhtml .= '<tr>
                        <td>'.$por['realname'].'</td>
                        <td>'.$por['mobile'].'</td>
                        <td>'.$por['smsid'].'</td>
                        <td>'.$tickets[$por['team_id']]['cate'].'('.$tickets[$por['team_id']]['t_name'].')</td>
                        <td>'.$por['quantity'].'</td>
                        
                        <td>'.$por['price'].'</td>
                        <td>'.$por['money'].'</td>
                       
                        <td>'.$por['outtime'].'</td>
                        <td>'.$paytype[$tickets[$por['team_id']]['t_type']].'</td>
                        <td></td>
                    </tr>';
}
                    
             $printhtml .='   </tbody>
            </table>
            <table  cellpadding="0" cellspacing="0" class="reportTab" width="710px" style="margin: 0 auto; font-size: 14px">
                <tr>
                    <td style="border-right:none; text-align:left;"><input type="checkbox" />此单确认</td>
                    <td style="border-left:none; text-align:left;"><input type="checkbox" />此单不确认</td>
                </tr>
                <tr>
                    <td>（确认号:_____________________）</td>
                    <td>（不确认原因:_____________________）</td>
                </tr>
                <tr>
                    <td style=" text-align:left;">
                        备注信息
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-indent:1em; text-align:left; font-weight:700; font-size:14px;" colspan="2" >
                        说明：1.-----请在<input type="checkbox" />中划勾-----&nbsp;&nbsp;确认后回传<br />
                        <p style="padding-left:50px; padding-top:0px;">2.客人到前台报客人姓名、电话办理取票手续   订票服务热线：0531-66717918</p>
                    </td>
                </tr></div>';
                
$excelhtml = '<table width="600" border="1" cellpadding="2" cellspacing="0" id="tab1" style="font-size: 12px;  color: Black;">
            
            <tr>
                <td colspan="11" align="center" valign="middle">
                    <h3>
                        <strong>
                            蜗牛旅途网（www.wnltu.com
                            ）&mdash;网上订票统计表</strong></h3>
                </td>
            </tr>
            
            <tr>
                <td colspan="5">
                    网站联系人：马民
                    0531-88069703 传真：0531-66717918
                </td>
                <td colspan="6" align="right" valign="middle">
                    商户联系人： 13065029703 传真：
                </td>
            </tr>
            <tr>
                <td colspan="11" align="right" valign="middle">
                    订票数量总数：'.$num.'
                    订票总价格：'.$total_price.'
                </td>
            </tr>
            <tr>
                <td align="center" bgcolor="#CDEFFC">
                    <strong>序号</strong>
                </td>
                <td align="center" bgcolor="#CDEFFC">
                    <strong>订单编号</strong>
                </td>
                <td align="center" bgcolor="#CDEFFC">
                    <strong>姓名</strong>
                </td>
                <td align="center" bgcolor="#CDEFFC">
                    <strong>联系电话</strong>
                </td>
                <td align="center" bgcolor="#CDEFFC">
                    <strong>订票类型</strong>
                </td>
                <td align="center" bgcolor="#CDEFFC">
                    <strong>团/散</strong>
                </td>
                <td align="center" bgcolor="#CDEFFC">
                    <strong>预定</strong>
                </td>
                <td align="center" bgcolor="#CDEFFC">
                    <strong>优惠价</strong>
                </td>
                <td align="center" bgcolor="#CDEFFC">
                    <strong>游玩日期</strong>
                </td>
                
                <td align="center" bgcolor="#CDEFFC">
                    <strong>商户名称</strong>
                </td>
                
                <td align="center" bgcolor="#CDEFFC">
                    <strong>实到</strong>
                </td>
            </tr>
            ';
foreach($orders as $ky=>$vv)
{
    $k = $ky+1;
    $vv['outtime'] = date('Y-m-d',$vv['outtime']);
    //$danjian = intval($vv['price'])- intval($vv['fanxian']);
    $excelhtml .= ' <tr>
                        <td align="center">
                            '.$k.'
                        </td>
                        <td align="center">
                            '.$vv['pay_id'].'
                        </td>
                        <td align="center">
                            '.$vv['realname'].'
                        </td>
                        <td align="center">
                            '.$vv['mobile'].'
                        </td>
                        <td align="center">
                            ('.$tickets[$vv['team_id']]['cate'].'('.$tickets[$vv['team_id']]['t_name'].')
                        </td>
                        <td align="center">
                            散
                        </td>
                        <td align="center">
                            '.$vv['quantity'].'
                        </td>
                        <td align="right">
                            '.$vv['price'].'
                        </td>
                        <td align="center">
                            '.$vv['outtime'].'
                        </td>
                        
                        <td>
                            '.$partners[$vv['partner_id']]['title'].'
                        </td>
                        
                        <td>
                            '.$vv['shidao'].'
                        </td>
                    </tr>';
}
                   
                
                    
                
        $excelhtml .='    <tr>
                <td colspan="11" align="right" valign="middle">
                    订票数量总数：'.$num.'
                    订票总价格：'.$total_price.'
                </td>
            </tr>
            <tr>
                <td colspan="11">
                    请准确填写&ldquo;实到&rdquo;数量，可以有两种方式回传给我们：
                </td>
            </tr>
            <tr>
                <td colspan="11">
                   1、发送传真，传真号码：0531-66717918 2、电子邮箱：2317893367@qq.com
                </td>
            </tr>
            <tr>
                <td colspan="11">
                    如有疑问，请联系票务专员：马民 0531-88069703 
                </td>
            </tr>
        </table>';
		//exit(print_r($excelhtml));
$data['print'] = $printhtml;
$data['excel'] = $excelhtml;
$data['test'] = $orderids;
die(json_encode($data));
?>
