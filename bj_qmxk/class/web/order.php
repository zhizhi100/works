<?php
$cfg = $this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $status = !isset($_GPC['status']) ? 1 : $_GPC['status'];
    $sendtype = !isset($_GPC['sendtype']) ? 0 : $_GPC['sendtype'];
    $condition = '';
    if (!empty($_GPC['keyword'])) {
        $condition.= " AND title LIKE '%{$_GPC['keyword']}%'";
    }
    $param_ordersn = $_GPC['ordersn'];
    if (!empty($_GPC['ordersn'])) {
        $condition.= " AND ordersn LIKE '%{$_GPC['ordersn']}%'";
    }
    if (!empty($_GPC['cate_2'])) {
        $cid = intval($_GPC['cate_2']);
        $condition.= " AND ccate = '{$cid}'";
    } elseif (!empty($_GPC['cate_1'])) {
        $cid = intval($_GPC['cate_1']);
        $condition.= " AND pcate = '{$cid}'";
    }
    if ($status < 4) {
        $condition.= " AND status = '" . intval($status) . "'";
    }
    if (!empty($sendtype)) {
    	$condition.= " AND sendtype = '" . intval($sendtype) . "' AND status != '3'";
    }
    if (!empty($_GPC['start_time']) && !empty($_GPC['end_time'])) {
    	$start_time = strtotime($_GPC['start_time'] . " 00:00:01");
    	$end_time = strtotime($_GPC['end_time'] . " 23:59:59");
    } else {
    	$start_time = strtotime(date('Y-m-01 00:00:01', time()));
    	$end_time = strtotime(date('Y-m-t 23:59:59', time()));
    }
    $condition.= " and createtime>=" . $start_time . " and createtime<=" . $end_time;
    

  
    $list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}' $condition ORDER BY status ASC, createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}' $condition");
    $pager = pagination($total, $pindex, $psize);
    if (!empty($list)) {
        foreach ($list as $key => $l) {
            $commission = pdo_fetch("select total,sum(commission) as commission, sum(commission2) as commission2, sum(commission3) as commission3 from " . tablename('bj_qmxk_order_goods') . " where orderid = " . $l['id']);
            $list[$key]['commission'] = $commission['commission'] * $commission['total'];
            if ($cfg['globalCommissionLevel'] >= 2) {
                $list[$key]['commission2'] = $commission['commission2'] * $commission['total'];
            } else {
                $list[$key]['commission2'] = 0;
            }
            if ($cfg['globalCommissionLevel'] >= 3) {
                $list[$key]['commission3'] = $commission['commission3'] * $commission['total'];
            } else {
                $list[$key]['commission3'] = 0;
            }
        }
    }
    if (!empty($list)) {
        foreach ($list as & $row) {
            !empty($row['addressid']) && $addressids[$row['addressid']] = $row['addressid'];
            $row['dispatch'] = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_dispatch') . " WHERE id = :id", array(
                ':id' => $row['dispatch']
            ));
        }
        unset($row);
    }
    if (!empty($addressids)) {
        $address = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id IN ('" . implode("','", $addressids) . "')", array() , 'id');
    }
    //导出excel
    $status = !isset($_GPC['status']) ? 1 : $_GPC['status'];
    $term = '';
    $param_ordersn = $_GPC['ordersn'];
    if (!empty($_GPC['ordersn'])) {
        $term.= " AND a.ordersn LIKE '%{$_GPC['ordersn']}%'";
    }
    if ($status < 4) {
        $term.= " AND a.status = '" . intval($status) . "'";
    }
    if (!empty($_GPC['start_time']) && !empty($_GPC['end_time'])) {
        $start_time = strtotime($_GPC['start_time'] . " 00:00:01");
        $end_time = strtotime($_GPC['end_time'] . " 23:59:59");
    } else {
        $start_time = strtotime(date('Y-m-01 00:00:01', time()));
        $end_time = strtotime(date('Y-m-t 23:59:59', time()));
    }
    $term.= " and a.createtime>=" . $start_time . " and a.createtime<=" . $end_time;
    $order_list = pdo_fetchall("SELECT a.ordersn,a.createtime,b.total,b.optionname,c.title,c.goodssn,c.productsn,d.realname,d.mobile,
        d.province,d.city,d.area,d.address FROM " . tablename('bj_qmxk_order') . " a LEFT JOIN " . tablename('bj_qmxk_order_goods') . " b 
        ON a.id = b.orderid LEFT JOIN " . tablename('bj_qmxk_goods') . " c ON b.goodsid = c.id LEFT JOIN " . tablename('bj_qmxk_address') . " d 
        ON a.addressid = d.id WHERE a.weid = '{$_W['weid']}' $term ORDER BY a.createtime ASC");
    if (!empty($_GPC['orderEXP01'])) {
        $report = "order";
        require_once 'report.php';
        exit;
    }
} elseif ($operation == 'detail') {
    $members = pdo_fetchall("select id, realname,from_user from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and status = 1");
    $member = array();
    foreach ($members as $m) {
        $member[$m['id']] = $m['realname'];
        $member[$m['from_user']] = $m['realname'];
    }
    $id = intval($_GPC['id']);
    $item = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(
        ':id' => $id
    ));
    if (empty($item)) {
        message("抱歉，订单不存在!", referer() , "error");
    }
    if (checksubmit('confirmsend')) {
        if (!empty($_GPC['isexpress']) && empty($_GPC['expresssn'])) {
            message('请输入快递单号！');
        }
        $item = pdo_fetch("SELECT transid FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(
            ':id' => $id
        ));
        if (!empty($item['transid'])) {
            $this->changeWechatSend($id, 1);
        }

        pdo_update('bj_qmxk_order', array(
            'status' => 2,
            'remark' => $_GPC['remark'],
            'express' => $_GPC['express'],
            'expresscom' => $_GPC['expresscom'],
            'expresssn' => $_GPC['expresssn'],
        ) , array(
            'id' => $id
        ));
        message('发货操作成功！', referer() , 'success');
    }
    if (checksubmit('cancelsend')) {
        $item = pdo_fetch("SELECT transid FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(
            ':id' => $id
        ));
        if (!empty($item['transid'])) {
            $this->changeWechatSend($id, 0, $_GPC['cancelreson']);
        }
        pdo_update('bj_qmxk_order', array(
            'status' => 1,
            'remark' => $_GPC['remark'],
        ) , array(
            'id' => $id
        ));
        message('取消发货操作成功！', referer() , 'success');
    }
    if (checksubmit('finish')) {
		
        $this->setOrderCredit($id, $_W['weid']);
        /**
         * 订单完成进行余额操作
         */
        $order = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(
        		':id' => $id
        ));
        if($order["status"] !=3){
        	$this->buyDealCredit($id);  //添加余额（订单完成时）避免重复提交
        };
        pdo_update('bj_qmxk_order', array(
            'status' => 3,
            'remark' => $_GPC['remark']
        ) , array(
            'id' => $id
        ));
		$this->getOrderNum($id);
		
	
        message('订单操作成功！', referer() , 'success');
    }
	//$this->getOrderNum($id);
    if (checksubmit('cancelpay')) {
        pdo_update('bj_qmxk_order', array(
            'status' => 0,
            'remark' => $_GPC['remark']
        ) , array(
            'id' => $id
        ));
        $this->setOrderStock($id, false);
        message('取消订单付款操作成功！', referer() , 'success');
    }
    if (checksubmit('confrimpay')) {
        pdo_update('bj_qmxk_order', array(
            'status' => 1,
            'paytype' => 2,
            'remark' => $_GPC['remark']
        ) , array(
            'id' => $id
        ));
        message('确认订单付款操作成功！', referer() , 'success');
    }
    if (checksubmit('close')) {
        $item = pdo_fetch("SELECT transid FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(
            ':id' => $id
        ));
        if (!empty($item['transid'])) {
            $this->changeWechatSend($id, 0, $_GPC['reson']);
        }
        pdo_update('bj_qmxk_order', array(
            'status' => - 1,
            'remark' => $_GPC['remark']
        ) , array(
            'id' => $id
        ));
        message('订单关闭操作成功！', referer() , 'success');
    }
    if (checksubmit('open')) {
        pdo_update('bj_qmxk_order', array(
            'status' => 0,
            'remark' => $_GPC['remark']
        ) , array(
            'id' => $id
        ));
        message('开启订单操作成功！', referer() , 'success');
    }
    
    if (checksubmit('cancelreturn')) {
        $item = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_order') . ' WHERE id = :id', array(':id' => $id));
        $ostatus = 3;
        if ($item['status'] == -2) {
            $ostatus = 1;
        }
        if ($item['status'] == -3) {
            $ostatus = 3;
        }
        if ($item['status'] == -4) {
            $ostatus = 3;
        }
        pdo_update('bj_qmxk_order', array('status' => $ostatus), array('id' => $id));
        message('退回操作成功！', referer(), 'success');
    }
    if (checksubmit('returnpay')) {
        $item = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_order') . ' WHERE id = :id', array(':id' => $id));
        if($item['special']){
        	message('抱歉，特殊订单不能退款！',  referer(), 'error');
        }
        if ($item['paytype'] == 3) {
            message('货到付款订单不能进行退款操作!', referer(), 'error');
        }
        //:TODO支付金额原路返回
        if( $this->returnMoney($id)){
        	pdo_update('bj_qmxk_order', array('status' => -6), array('id' => $id));
        	$this->setOrderStock($id, false);
        	message('退款操作成功！', referer(), 'success');
        }else{
        	message('退款操作失败！', referer(), 'error');
        }
     
    
     
    }
    if (checksubmit('returngood')) {
    	//退货
        $item = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_order') . ' WHERE id = :id', array(':id' => $id));
        if($item['special']){
        	message('抱歉，特殊订单不能退货！',  referer(), 'error');
        }
        pdo_update('bj_qmxk_order', array('status' => -5), array('id' => $id));
        $this->setOrderStock($id, false);
      //  $this->setOrderCredit($item['openid'], $id, false, '订单:' . $item['ordersn'] . '退货扣除积分');
        //$this->setMemberCredit2($item['from_user'], $item['price'], 'addgold', '订单:' . $item['ordersn'] . '退货返还余额');
        message('退货操作成功！', referer(), 'success');
    }
    
    $dispatch = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_dispatch') . " WHERE id = :id", array(
        ':id' => $item['dispatch']
    ));
     /*if (!empty($dispatch) && !empty($dispatch['express'])) {
        $express = pdo_fetch("select * from " . tablename('bj_qmxk_express') . " WHERE id=:id limit 1", array(
            ":id" => $dispatch['express']
        ));
    }*/
	$express = pdo_fetchall("select * from " . tablename('bj_qmxk_express') . " WHERE weid=:weid ", array(
        ":weid" => $_W['weid']
    ));
    $item['user'] = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = {$item['addressid']}");
    $goods = pdo_fetchall("SELECT g.id, g.title, g.status,g.thumb, g.unit,g.goodssn,g.productsn,g.marketprice,o.total,g.type,o.optionname,o.optionid,o.price as orderprice FROM " . tablename('bj_qmxk_order_goods') . " o left join " . tablename('bj_qmxk_goods') . " g on o.goodsid=g.id " . " WHERE o.orderid='{$id}'");
    $item['goods'] = $goods;
    
    $order_id= $_GPC['id'];
    $list_fx=pdo_fetchall("
			select 1 as level,o.ordersn,og.commission as commission,og.status as status,m.realname,m.id from  " . tablename('bj_qmxk_order') . " o left join   " . tablename('bj_qmxk_order_goods') . " og
			on o.id=og.orderid left join " . tablename('bj_qmxk_member') . " m on  m.id=o.shareid
			where o.weid=".$_W['weid']." and o.id=".$order_id."
			 union all
			select 2 as level,o.ordersn,og.commission2 as commission,og.status2 as status,m.realname,m.id from  " . tablename('bj_qmxk_order') . " o left join   " . tablename('bj_qmxk_order_goods') . " og
			on o.id=og.orderid left join " . tablename('bj_qmxk_member') . " m on  m.id=o.shareid2
			where o.weid=".$_W['weid']." and o.id=".$order_id."
			 union all
			select 3 as level,o.ordersn,og.commission3 as commission,og.status3 as status,m.realname,m.id  from  " . tablename('bj_qmxk_order') . " o left join   " . tablename('bj_qmxk_order_goods') . " og
			on o.id=og.orderid left join " . tablename('bj_qmxk_member') . " m on  m.id=o.shareid3
			where o.weid=".$_W['weid']." and o.id=".$order_id."");
    
    
    $list_fh=pdo_fetchall("
		select d.d_status, d.money,o.ordersn,m.realname,d.person_num,me.medal_name,m.id from 
    		" . tablename('bj_qmxk_relation_dividend') . " d  left join
			" . tablename('bj_qmxk_order') . " o on o.id=d.order_id
			 left join " . tablename('bj_qmxk_member') . " m on  m.from_user=d.from_user
			 left join " . tablename('bj_qmxk_medal') . " me on  me.id=d.medal_id
			where o.weid=".$_W['weid']." and o.id=".$order_id." and me.medal_status=1");

    
}elseif ($operation == 'post') {
	if (checksubmit('submit')) {
        if (! empty ( $_FILES ['file'] ['name'] ))
        {
            $tmp_file = $_FILES ['file'] ['tmp_name'];
            $file_types = explode ( ".", $_FILES ['file'] ['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];
            /*设置上传路径*/
            $savePath = './source/modules/bj_qmxk/recouse/file/';
            /*以时间来命名上传的文件*/
            $str = date ( 'Ymdhis' );
            $file_name = $str . "." . $file_type;
            /*是否上传成功*/
            if (! copy ( $tmp_file, $savePath . $file_name ))
            {
                message('上传失败', $this->createWebUrl('order', array('op' => 'post')));
            }
            /*
             注意：这里调用执行了read函数，把Excel转化为数组并返回给$res,再进行数据库写入
             */
            $path = dirname(dirname(dirname(__FILE__))).'/recouse/file/';
            $path = preg_replace('/\\\\/', '/', $path);
            
            $res = $this->read_excel ( $path . $file_name,$file_type);
         
            /*对生成的数组进行数据库的写入*/
            foreach ( $res as $k => $v ){
                if ($k != 0){
                    $ordersn = $v[0];
                    if (is_numeric($ordersn)) {
                        pdo_query("update ".tablename('bj_qmxk_order')." SET status = '".$_GPC['status']."' WHERE ordersn='".$ordersn."' AND  weid = ".$_W['weid']."");
                    }
                }
            }
            message('批量导入数据修改成功！', $this->createWebUrl('order', array('op' => 'post')));
        }
	}
}

include $this->template('order'); 
?>