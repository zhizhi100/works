<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Original Author <author@example.com>                        |
// |          Your Name <you@example.com>                                 |
// +----------------------------------------------------------------------+
//
// $Id:$
$cfg = $this->module['config'];
$weid = $_W['weid'];
$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$members = pdo_fetchall("select id, realname, mobile from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and status = 1");
$member = array();
foreach ($members as $m) {
    $member['realname'][$m['id']] = $m['realname'];
    $member['mobile'][$m['id']] = $m['mobile'];
}
if ($op == 'display') {
    if ($_GPC['opp'] == 'check') {
        $level = $_GPC['level'];
		
        $zhifucommission = $cfg['zhifuCommission'];
        if (!$zhifucommission) {
            message('请先在参数设置，设置佣金打款限额！', $this->createWebUrl('Commission') , 'success');
        }
        $shareid = $_GPC['shareid'];
        $user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $_GPC['shareid']);
        $bankcard = pdo_fetch("select id, bankcard, banktype,alipay from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and from_user = '" . $user['from_user'] . "'");

		$ids = $_GPC['ids'];
		$m_d_ids = $_GPC['m_d_ids'];
		$ogids = explode(',',$ids);
		$commission = 0;
        foreach($ogids as $v){
			$temps = explode('_',$v);
			$level = $temps[0];
			$id = $temps[1];
			if ($level == 1) {
				$conditionCommission = "commission*total as commission,applytime as applytime";
			}
			if ($level == 2) {
				$conditionCommission = "commission2*total as commission,applytime2 as applytime";
			}
			if ($level == 3) {
				$conditionCommission = "commission3*total as commission,applytime3 as applytime";
			}
			$info = pdo_fetch("select " . $conditionCommission . " from " . tablename('bj_qmxk_order_goods') . " where id = " . $id);
			$commission += $info['commission'];
		}
		/*
		 * 勋章分红金额
		 */
		$share_from_user= pdo_fetchcolumn("select from_user from " . tablename('bj_qmxk_member') . " where id=".$shareid);
		$dividend_money=pdo_fetchcolumn("select sum(d.money) as money from " . tablename('bj_qmxk_relation_dividend') ." d
				left join " . tablename('bj_qmxk_order') ." o  on o.id=d.order_id
    		 where d.from_user='".$share_from_user."' and d.d_status=1 and o.status>=3");
		$commission+=$dividend_money;
		
        //$info = pdo_fetch("select og.id,og.orderid, og.total, og.price," . $status . $conditionCommission . ", og.applytime, og.content, g.title from " . tablename('bj_qmxk_order_goods') . " as og left join " . tablename('bj_qmxk_goods') . " as g on og.goodsid = g.id and og.weid = g.weid where og.id = " . $_GPC['id']);
        //$order = pdo_fetch("select * from " . tablename('bj_qmxk_order') . " where id = " . $info['orderid']);
        include $this->template('applying_detail');
        exit;
    }
    if ($_GPC['opp'] == 'checked') {
        $ids = $_GPC['ids'];
		$ogids = explode(',',$ids);
		$shareid = $_GPC['shareid'];
		$fh_order_ids=$_GPC['fh_order_ids'];
        foreach($ogids as $k=>$v){
			$temps = explode('_',$v);
			$level = $temps[0];
			$id = $temps[1];
			$ogids_array[$k]=$temps[1];
			if ($level == 1) {
				$checked = array(
					'status' => $_GPC['status'],
					'checktime' => time()
				);
			}
			if ($level == 2) {
				$checked = array(
					'status2' => $_GPC['status'],
					'checktime2' => time()
				);
			}
			if ($level == 3) {
				$checked = array(
					'status3' => $_GPC['status'],
					'checktime3' => time()
				);
			}
			$temp = pdo_update('bj_qmxk_order_goods', $checked, array(
				'id' => $id
			));
		}
		//修改分红佣金状态
		//获取商品订单id(多个)
// 	$ogids_string=implode(",",$ogids_array);
// 		//获取订单id
// 	$orderids_string= pdo_fetchcolumn("select group_concat(orderid) as  orderid  from " . tablename('bj_qmxk_order_goods') . " where id in($ogids_string)");
	//获取当前订单的from_user
 	$share_from_user= pdo_fetchcolumn("select from_user from " . tablename('bj_qmxk_member') . " where id=".$shareid);
	//修改分红状态
	$sql="update " . tablename('bj_qmxk_relation_dividend') . " set  d_status=".$_GPC['status'].",check_time='".date('Y-m-d H:i:s')."'
			where order_id in($fh_order_ids) and from_user='".$share_from_user."'
			";
		pdo()->query($sql);
			
        if ($_GPC['status'] == 2) {
           
			
			foreach($ogids as $v){
				$temps = explode('_',$v);
				$level = $temps[0];
				$ogid = $temps[1];
				if ($level == 1) {
					$conditionCommission = "commission*total as commission";
				}
				if ($level == 2) {
					$conditionCommission = "commission2*total as commission";
				}
				if ($level == 3) {
					$conditionCommission = "commission3*total as commission";
				}
				$info = pdo_fetch("select " . $conditionCommission . " from " . tablename('bj_qmxk_order_goods') . " where id = " . $ogid);
			
				$commission = array(
					'weid' => $_W['weid'],
					'mid' => $shareid,
					'ogid' => $ogid,
					'commission' => $info['commission'],
					'content' => trim($_GPC['content']) ,
					'isshare' => 0,
					'createtime' => time()
				);
			
				
				if ($info['commission'] > 0) {
					$temp = pdo_insert('bj_qmxk_commission', $commission);
				}
				
			}
            
			/*
			 * 增加分红结算记录
			*/
			//获取当前分红id
			$sql="select * from  " . tablename('bj_qmxk_relation_dividend') . "
			where order_id in($fh_order_ids) and from_user='".$share_from_user."' and d_status=2
			";
			$fh_info=pdo()->fetchall($sql);
			foreach($fh_info as $v){
				$fh_commission = array(
						'weid' => $_W['weid'],
						'from_user' => $share_from_user,
						'fh_id' => $v["id"],
						'fh_commission' => $v['money'],
						'content' => "获得分红" ,
						'isshare' => 0,
						'createtime' => time()
				);
				if ( $v['money'] > 0) {
					 pdo_insert('bj_qmxk_fh_commission', $fh_commission);
				}
			}
			
			
            if ($_GPC['commission'] > 0) {
                $commission = pdo_fetch("select from_user,commission from " . tablename('bj_qmxk_member') . " where id = " . $shareid);
                pdo_update('bj_qmxk_member', array(
                    'commission' => $commission['commission'] + $_GPC['commission']
                ) , array(
                    'id' => $shareid
                ));
                pdo_query("update " . tablename('bj_qmxk_member') . " SET zhifu=zhifu+'" . $_GPC['commission'] . "' WHERE id='" . $shareid . "' AND  weid=" . $_W['weid'] . "  ");
                $paylog = array(
                    'type' => 'zhifu',
                    'weid' => $_W['weid'],
                    'openid' => $commission['from_user'],
                    'tid' => date('Y-m-d H:i:s') ,
                    'fee' => $_GPC['commission'],
                    'module' => 'bj_qmxk',
                    'tag' => ' 后台打款' . $_GPC['commission'] . '元【会员佣金】'
                );
                pdo_insert('paylog', $paylog);
                $this->sendsjytktz($_GPC['commission'], $level, $commission['from_user']);
            }
            message('打款完成！', $this->createWebUrl('commission') , 'success');
        }
        if (empty($temp)) {
            message('审核失败，请重新审核！', $this->createWebUrl('commission', array(
                'opp' => 'check',
                'shareid' => $_GPC['shareid'],
                'id' => $_GPC['id'],
                'm_d_ids'=>$m_d_ids,
                'ids'=>$ids
            )) , 'error');
        } else {
            message('审核成功！', $this->createWebUrl('commission') , 'success');
        }
    }
    
    
    if ($_GPC['opp'] == 'sort') {
        $sort = array(
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile']
        );
        $shareid = "select id from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and realname like '%" . $sort['realname'] . "%' and mobile like '%" . $sort['mobile'] . "%'";
        $list = pdo_fetchall("select 1 as level,o.shareid, o.status, g.id, g.applytime,g.commission*g.total as commission,g.checktime as checktime,o.id as order_id from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status = 1) and (o.shareid in (" . $shareid . ")) union all (select 2 as level,o.shareid2 as shareid, o.status, g.id, g.applytime2 as applytime,g.commission2*g.total as commission,g.checktime2 as checktime,o.id as order_id from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status2 = 1) and (o.shareid2 in (" . $shareid . "))) union all (select 3 as level,o.shareid3 as shareid, o.status, g.id, g.applytime3 as applytime,g.commission3*g.total as commission,g.checktime3 as checktime,o.id as order_id from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status3 = 1) and (o.shareid3 in (" . $shareid . ")))   order by applytime desc");
        $total = sizeof($list);
    } else {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $list = pdo_fetchall("select 1 as level,o.shareid,o.status, g.id, g.applytime,g.commission*g.total as commission,
        		g.checktime as checktime,o.id as order_id  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g
        		 on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status = 1 and o.shareid!=0) " . "
        		 union all (select  2 as level,o.shareid2 as shareid,o.status, g.id, g.applytime2 as applytime,g.commission2*g.total as commission,
        		g.checktime2 as checktime,o.id as order_id  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g 
        		on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status2 = 1 and o.shareid2!=0) )" . " 
        		union all (select 3 as level,o.shareid3 as shareid,o.status, g.id, g.applytime3 as applytime,g.commission3*g.total as commission,
        		g.checktime3 as checktime,o.id as order_id  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g 
        		on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status3 = 1 and o.shareid3!=0) ) 
        		order by applytime desc limit " . ($pindex - 1) * $psize . ',' . $psize);
        
        $total = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (o.shareid!=0 )  and (g.status = 1 )");
        $total2 = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (o.shareid2!=0 )  and (g.status2 = 1 )");
        $total3 = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (o.shareid3!=0 )  and (g.status3 = 1 )");
        $total = $total + $total2 + $total3;
        $pager = pagination1($total, $pindex, $psize);
    }
    if (!empty($list)) {
        foreach ($list as $key => $l) {
            $user = pdo_fetch("select id,shareid from " . tablename('bj_qmxk_member') . " where flag=1
            		 and id = " . $l['shareid']);
            if (empty($user['id'])) {
                $list[$key]['commission'] = 0;
                $list[$key]['commission2'] = 0;
                $list[$key]['commission3'] = 0;
            } else {
                $user2 = pdo_fetch("select id,shareid from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user['shareid']);
                if (empty($user2['id'])) {
                    $list[$key]['commission2'] = 0;
                    $list[$key]['commission3'] = 0;
                } else {
                    $user3 = pdo_fetch("select id from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user2['shareid']);
                    if (empty($user3['id'])) {
                        $list[$key]['commission3'] = 0;
                    }
                }
            }
        }
    }
   // echo count($list)."<br/>";
	//2015/6/12 添加
	$list2 = array();
	if (!empty($list)) {
        foreach ($list as $key => $l) {
			if(array_key_exists($l['shareid'],$list2)){
				$list2[$l['shareid']]['commission'] += $l['commission'];
				$list2[$l['shareid']]['ogid'] .= ','.$l['level'].'_'.$l['id'];
				$list2[$l['shareid']]['ogid_bound'] .= '_'.$l['order_id'];//分红中的订单id
			}else{
				$list2[$l['shareid']]['ogid'] .= $l['level'].'_'.$l['id'];
				$list2[$l['shareid']]['shareid'] = $l['shareid'];
				$list2[$l['shareid']]['applytime'] = $l['applytime'];
				$list2[$l['shareid']]['commission'] = $l['commission'];
				$list2[$l['shareid']]['ogid_bound'] = $l['order_id'];//分红中的订单id
			}
		}
	}
	//echo count($list2);
	/**
	 * 追加勋章的分红
	 */
	$ogid_arr=array();
	foreach ($list2 as $key=>$v){
		$shareid=$v["shareid"];
		$share_from_user= pdo_fetchcolumn("select from_user from " . tablename('bj_qmxk_member') . " where id=".$shareid);
//获取当前申请的佣金中的部分分红id
		$ogid_arr=explode("_",$v["ogid_bound"]);
		sort($ogid_arr);
		$ogid_string=implode(",",$ogid_arr);
	
		$dividend_money=pdo_fetchcolumn("select sum(d.money) as money from " . tablename('bj_qmxk_relation_dividend') ." d
left join " . tablename('bj_qmxk_order') ." o  on o.id=d.order_id
				where d.order_id  in(".$ogid_string.")  and d.d_status=1 and d.from_user='".$share_from_user."'");
		$list2[$shareid]["commission"]=$list2[$shareid]["commission"]+$dividend_money;
		$list2[$shareid]["dividend_money"]=$dividend_money;

		$list2[$shareid]["fh_order_ids"]=$ogid_string;
	}
//	print_R($list2);
    include $this->template('applying');
    exit;
}
if ($op == 'applyed') {
    if ($_GPC['opp'] == 'jieyong') {
        $shareid = $_GPC['shareid'];
        $user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $_GPC['shareid']);
        $bankcard = pdo_fetch("select id, bankcard, banktype,alipay from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and from_user = '" . $user['from_user'] . "'");
        $level = $_GPC['level'];
        if (empty($level)) {
            message('error');
        }
        if ($level == 1) {
            $conditionCommission = "(og.commission*og.total) as commission, og.status as status, og.applytime as applytime";
        }
        if ($level == 2) {
            $conditionCommission = "(og.commission2*og.total) as commission, og.status2 as status, og.applytime2 as applytime";
        }
        if ($level == 3) {
            $conditionCommission = "(og.commission3*og.total)  as commission, og.status3 as status, og.applytime3 as applytime";
        }
        $info = pdo_fetch("select og.id,og.orderid, og.total, og.price, " . $conditionCommission . ", 
        		og.content, g.title 
        		from " . tablename('bj_qmxk_order_goods') . " as og 
        		left join " . tablename('bj_qmxk_goods') . " as g on og.goodsid = g.id and og.weid = g.weid 
        		where og.id = " . $_GPC['id']);
        $order = pdo_fetch("select * from " . tablename('bj_qmxk_order') . " where id = " . $info['orderid']);
        include $this->template('applyed_detail');
        exit;
    }
    if ($_GPC['opp'] == 'sort') {
        $sort = array(
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile']
        );
        $shareid = "select id from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and realname like '%" . $sort['realname'] . "%' and mobile like '%" . $sort['mobile'] . "%'";
        $list = pdo_fetchall("select 1 as level,o.shareid, o.status, g.id, g.applytime,g.commission*g.total as commission,g.checktime as checktime from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status = 2) and (o.shareid in (" . $shareid . ")) union all (select 2 as level,o.shareid2 as shareid, o.status, g.id, g.applytime2 as applytime,g.commission2*g.total as commission,g.checktime2 as checktime from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status2 = 2) and (o.shareid2 in (" . $shareid . "))) union all (select 3 as level,o.shareid3 as shareid, o.status, g.id, g.applytime3 as applytime,g.commission3*g.total as commission,g.checktime3 as checktime from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status3 = 2) and (o.shareid3 in (" . $shareid . "))) order by applytime desc ");
        $total = sizeof($list);
    } else {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $list = pdo_fetchall("select 1 as level,o.shareid,o.status, g.id, g.applytime,g.commission*g.total as commission,g.checktime as checktime  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status = 2 and o.shareid!=0)  union all (select  2 as level,o.shareid2 as shareid,o.status, g.id, g.applytime2 as applytime,g.commission2*g.total as commission,g.checktime2 as checktime  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status2 = 2 and o.shareid2!=0) )union all (select 3 as level,o.shareid3 as shareid,o.status, g.id, g.applytime3 as applytime,g.commission3*g.total as commission,g.checktime3 as checktime  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status3 = 2 and o.shareid3!=0) ) order by applytime desc limit " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (o.shareid!=0 )  and (g.status = 2 )");
        $total2 = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (o.shareid2!=0 )  and (g.status2 = 2 )");
        $total3 = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (o.shareid3!=0 )  and (g.status3 = 2 )");
        $total = $total + $total2 + $total3;
        $pager = pagination1($total, $pindex, $psize);
    }
    if (!empty($list)) {
        foreach ($list as $key => $l) {
            $user = pdo_fetch("select id,shareid from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $l['shareid']);
            if (empty($user['id'])) {
                $list[$key]['commission'] = 0;
                $list[$key]['commission2'] = 0;
                $list[$key]['commission3'] = 0;
            } else {
                $user2 = pdo_fetch("select id,shareid from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user['shareid']);
                if (empty($user2['id'])) {
                    $list[$key]['commission2'] = 0;
                    $list[$key]['commission3'] = 0;
                } else {
                    $user3 = pdo_fetch("select id from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user2['shareid']);
                    if (empty($user3['id'])) {
                        $list[$key]['commission3'] = 0;
                    }
                }
            }
        }
    }
    include $this->template('applyed');
    exit;
}
if ($op == 'fhapplyed') {
	if ($_GPC['opp'] == 'jieyong') {
		$shareid = $_GPC['shareid'];
		$id = $_GPC['id'];
		$user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $_GPC['shareid']);
		$bankcard = pdo_fetch("select id, bankcard, banktype,alipay from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and from_user = '" . $user['from_user'] . "'");
		$info = pdo_fetch("
		select *  from " . tablename('bj_qmxk_fh_commission') . " as fh_c  left join
				" . tablename('bj_qmxk_relation_dividend') . " as rd on fh_c.fh_id=rd.id and fh_c.weid=rd.weid
				left join " . tablename('bj_qmxk_member') . " as m on m.from_user=  fh_c.from_user  and rd.from_user=m.from_user and rd.weid=m.weid
				left join " . tablename('bj_qmxk_order') . " o on o.id= rd.order_id
				where m.id =".$shareid."   and fh_c.weid = " . $_W['weid']." and fh_c.id=".$id
		 );
		include $this->template('fhapplyed_detail');
		exit;
	}
	if ($_GPC['opp'] == 'sort') {
		$sort = array(
				'realname' => $_GPC['realname'],
				'mobile' => $_GPC['mobile']
		);
		$member_id = "select id from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and realname like '%" . $sort['realname'] . "%' and mobile like '%" . $sort['mobile'] . "%'";
		$list = pdo_fetchall("
		select m.id ,rd.d_status,fh_c.id as fh_c_id,fh_c.createtime,fh_c.fh_commission  from " . tablename('bj_qmxk_fh_commission') . " as fh_c  left join 
				" . tablename('bj_qmxk_relation_dividend') . " as rd on fh_c.fh_id=rd.id and fh_c.weid=rd.weid
				left join " . tablename('bj_qmxk_member') . " as m on m.from_user=  fh_c.from_user  and rd.from_user=m.from_user and rd.weid=m.weid
				where m.id in (".$member_id.") and rd.d_status=2 and fh_c.weid = " . $_W['weid']." 
				 order by createtime desc
		 ");
		$total = sizeof($list);
	} else {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("select m.id ,rd.d_status,fh_c.id as fh_c_id,fh_c.createtime,fh_c.fh_commission  from" . tablename('bj_qmxk_fh_commission') . " as fh_c  left join 
				" . tablename('bj_qmxk_relation_dividend') . " as rd on fh_c.fh_id=rd.id and fh_c.weid=rd.weid
				left join " . tablename('bj_qmxk_member') . " as m on m.from_user=  fh_c.from_user  and rd.from_user=m.from_user and rd.weid=m.weid
				where rd.d_status=2 and fh_c.weid = " . $_W['weid']." 
				 order by createtime desc  limit " . ($pindex - 1) * $psize . ',' . $psize);
		$total= pdo_fetchcolumn("select count(*) as num from" . tablename('bj_qmxk_fh_commission') . " as fh_c  left join 
				" . tablename('bj_qmxk_relation_dividend') . " as rd on fh_c.fh_id=rd.id and fh_c.weid=rd.weid
				left join " . tablename('bj_qmxk_member') . " as m on m.from_user=  fh_c.from_user 
				 and rd.from_user=m.from_user and rd.weid=m.weid
				where rd.d_status=2 and fh_c.weid = ".$_W['weid']);
		$pager = pagination($total, $pindex, $psize);
	}
	include $this->template('fhapplyed');
	exit;
}


if ($op == 'invalid') {
    if ($_GPC['opp'] == 'delete') {
        $level = $_GPC['level'];
        if (empty($level)) {
            message('error');
        }
        if ($level == 1) {
            $delete = array(
                'status' => - 2,
                'checktime' => time()
            );
        }
        if ($level == 2) {
            $delete = array(
                'status2' => - 2,
                'checktime2' => time()
            );
        }
        if ($level == 3) {
            $delete = array(
                'status3' => - 2,
                'checktime3' => time()
            );
        }
        $temp = pdo_update('bj_qmxk_order_goods', $delete, array(
            'id' => $_GPC['id']
        ));
        if (empty($temp)) {
            message('删除失败，请重新删除！', $this->createWebUrl('commission', array(
                'op' => 'invalid'
            )) , 'error');
        } else {
            message('删除成功！', $this->createWebUrl('commission', array(
                'op' => 'invalid'
            )) , 'success');
        }
    }
    if ($_GPC['opp'] == 'sort') {
        $sort = array(
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile']
        );
        $shareid = "select id from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and realname like '%" . $sort['realname'] . "%' and mobile like '%" . $sort['mobile'] . "%'";
        $list = pdo_fetchall("select 1 as level,o.shareid, o.status, g.id, g.applytime,g.commission*g.total as commission,g.checktime from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status = -1) and (o.shareid in (" . $shareid . ")) union all (select 2 as level,o.shareid2 as shareid, o.status, g.id, g.applytime2 as applytime,g.commission2*g.total as commission,g.checktime2 as checktime from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status2 = -1) and (o.shareid2 in (" . $shareid . "))) union all (select 3 as level,o.shareid3 as shareid, o.status, g.id, g.applytime3 as applytime,g.commission3*g.total as commission,g.checktime3 as checktime from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status3 = -1) and (o.shareid3 in (" . $shareid . ")))  order by applytime desc ");
        $total = sizeof($list);
    } else {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $list = pdo_fetchall("select 1 as level,o.shareid,o.status, g.id, g.applytime,g.commission*g.total as commission,g.checktime  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status = -1 and o.shareid!=0)  union all (select  2 as level,o.shareid2 as shareid,o.status, g.id, g.applytime2 as applytime,g.commission2*g.total as commission,g.checktime2 as checktime  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status2 = -1 and o.shareid2!=0) )union all (select 3 as level,o.shareid3 as shareid,o.status, g.id, g.applytime3 as applytime,g.commission3*g.total as commission,g.checktime3 as checktime  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (g.status3 = -1 and o.shareid3!=0) ) order by applytime desc limit " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (o.shareid!=0 )  and (g.status = -1 )");
        $total2 = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (o.shareid2!=0 )  and (g.status2 = -1 )");
        $total3 = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and (o.shareid3!=0 )  and (g.status3 = -1 )");
        $total = $total + $total2 + $total3;
        $pager = pagination1($total, $pindex, $psize);
    }
    include $this->template('invalid');
    exit;
} 
?>
