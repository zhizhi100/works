<?php
defined('IN_IA') or exit('Access Denied');


$sql = "
CREATE TABLE `ims_attachment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `filename` varchar(255) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT '1为图片',
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_address` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL,
  `realname` varchar(20) NOT NULL,
  `mobile` varchar(11) NOT NULL,
  `province` varchar(30) NOT NULL,
  `city` varchar(30) NOT NULL,
  `area` varchar(30) NOT NULL,
  `address` varchar(300) NOT NULL,
  `isdefault` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_adv` (
  `id` int(11) NOT NULL auto_increment,
  `weid` int(11) NULL DEFAULT '0',
  `advname` varchar(50) NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `thumb` varchar(255) NULL DEFAULT '',
  `displayorder` int(11) NULL DEFAULT '0',
  `enabled` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `indx_displayorder`(`displayorder`),
  KEY `indx_enabled`(`enabled`),
  KEY `indx_weid`(`weid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_article` (
  `id` int(11) NOT NULL auto_increment,
  `column_id` int(11) unsigned NULL COMMENT '所属栏目',
  `title` varchar(255) NULL COMMENT '标题',
  `content` text NULL COMMENT '内容',
  `describe` text NULL COMMENT '描述',
  `recommend` tinyint(3) NOT NULL DEFAULT -1 COMMENT '推广 1 是；-1 否',
  `add_time` datetime NULL COMMENT '添加时间',
  `operator` varchar(50) NULL COMMENT '添加人',
  `share_num` int(11) NULL DEFAULT '0' COMMENT '推广次数',
  `read_num` int(11) NULL DEFAULT '0' COMMENT '阅读次数',
  `weid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='文章表';

CREATE TABLE `ims_bj_qmxk_article_column` (
  `id` int(11) NOT NULL auto_increment,
  `column_name` varchar(50) NULL COMMENT '名称',
  `pic` varchar(255) NULL COMMENT '图片',
  `isopen` tinyint(3) NULL DEFAULT -1 COMMENT '是否开启 1是；-1 否',
  `displayorder` tinyint(3) unsigned NULL DEFAULT 0 COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='文章栏目表';

CREATE TABLE `ims_bj_qmxk_article_share` (
  `id` int(11) NOT NULL auto_increment,
  `article_id` int(11) NULL COMMENT '文章id',
  `share_id` int(11) NOT NULL DEFAULT '0' COMMENT '分享者id',
  `share_time` datetime NULL COMMENT '分享时间',
  `weid` int(11) NOT NULL DEFAULT '0',
  `mid` int(11) NOT NULL DEFAULT '0' COMMENT '阅读者',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='文章分享记录表';

CREATE TABLE `ims_bj_qmxk_balance_logs` (
  `id` int(11) NOT NULL auto_increment,
  `flag` char(30) NULL COMMENT '属性：积分、自由余额、购物余额、贡献余额',
  `type` tinyint(3) NOT NULL DEFAULT 1 COMMENT '1:返佣；2:转账;3:转换;4：充值；5：分红；6：购买交易；7：关注分钱;8：提现；9：新版推广薪资',
  `operate` tinyint(3) NOT NULL DEFAULT 1 COMMENT '1收入  -1支出',
  `createtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `orderid` int(11) NULL COMMENT '订单ID',
  `weid` int(11) NOT NULL DEFAULT '0',
  `from_user` varchar(255) NOT NULL DEFAULT '',
  `remark` varchar(255) NULL,
  `fee` double(11,2) unsigned NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='收支记录表';

CREATE TABLE `ims_bj_qmxk_cart` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `goodsid` int(11) NOT NULL,
  `goodstype` tinyint(1) NOT NULL DEFAULT 1,
  `from_user` varchar(50) NOT NULL,
  `total` int(10) unsigned NOT NULL,
  `optionid` int(10) NULL DEFAULT '0',
  `marketprice` decimal(10,2) NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_openid`(`from_user`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属帐号',
  `commission` int(10) unsigned NULL DEFAULT '0' COMMENT '推荐该类商品所能获得的佣金',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `thumb` varchar(255) NOT NULL COMMENT '分类图片',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID,0为第一级',
  `isrecommand` int(10) NULL DEFAULT '0',
  `description` varchar(500) NOT NULL COMMENT '分类介绍',
  `displayorder` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '是否开启',
  `cateindexadvpic` varchar(300) NOT NULL,
  `cateindexadvlink` varchar(300) NOT NULL,
  `cateindexpic` varchar(300) NOT NULL,
  `cateindexlink` varchar(300) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_channel` (
  `channel` int(10) NOT NULL auto_increment,
  `active` int(10) unsigned NOT NULL DEFAULT '0',
  `bg` varchar(1024) NOT NULL,
  `title` varchar(1024) NOT NULL,
  `bgparam` varchar(10240) NOT NULL,
  `notice` varchar(1024) NOT NULL,
  `click_credit` int(10) NOT NULL COMMENT '未关注的用户关注,送分享者积分',
  `sub_click_credit` int(10) NOT NULL COMMENT '未关注的用户关注,送上线积分',
  `newbie_credit` int(10) NOT NULL COMMENT '通过本渠道关注微信号，送新用户大礼包积分',
  `weid` int(10) unsigned NOT NULL,
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  `msgtype` int(10) unsigned NOT NULL DEFAULT '1',
  `isdel` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`channel`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_commission` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `mid` int(10) unsigned NOT NULL COMMENT '粉丝ID',
  `ogid` int(10) unsigned NULL COMMENT '订单商品ID',
  `commission` decimal(10,2) unsigned NOT NULL COMMENT '佣金',
  `content` text NULL,
  `flag` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0为账户充值记录，1为提现记录',
  `isout` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0为未导出，1为已导出',
  `isshare` int(11) NULL,
  `createtime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_credit_award` (
  `award_id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT '0',
  `deadline` datetime NOT NULL,
  `credit_cost` int(11) NOT NULL DEFAULT '0',
  `price` int(11) NOT NULL DEFAULT '100',
  `content` text NOT NULL,
  `createtime` int(10) NOT NULL,
  PRIMARY KEY (`award_id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_credit_request` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `from_user` varchar(50) NOT NULL,
  `award_id` int(10) unsigned NOT NULL,
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_dispatch` (
  `id` int(11) NOT NULL auto_increment,
  `weid` int(11) NULL DEFAULT '0',
  `dispatchname` varchar(50) NULL DEFAULT '',
  `dispatchtype` int(11) NULL DEFAULT '0',
  `displayorder` int(11) NULL DEFAULT '0',
  `firstprice` decimal(10,2) NULL DEFAULT 0.00,
  `secondprice` decimal(10,2) NULL DEFAULT 0.00,
  `firstweight` int(11) NULL DEFAULT '0',
  `secondweight` int(11) NULL DEFAULT '0',
  `express` int(11) NULL DEFAULT '0',
  `description` text NULL,
  `pinkage_money` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '满多少元  包邮',
  PRIMARY KEY (`id`),
  KEY `indx_displayorder`(`displayorder`),
  KEY `indx_weid`(`weid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_express` (
  `id` int(11) NOT NULL auto_increment,
  `weid` int(11) NULL DEFAULT '0',
  `express_name` varchar(50) NULL DEFAULT '',
  `displayorder` int(11) NULL DEFAULT '0',
  `express_price` varchar(10) NULL DEFAULT '',
  `express_area` varchar(100) NULL DEFAULT '',
  `express_url` varchar(255) NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `indx_displayorder`(`displayorder`),
  KEY `indx_weid`(`weid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_feedback` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '1为维权，2为告擎',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态0未解决，1用户同意，2用户拒绝',
  `feedbackid` varchar(30) NOT NULL COMMENT '投诉单号',
  `transid` varchar(30) NOT NULL COMMENT '订单号',
  `reason` varchar(1000) NOT NULL COMMENT '理由',
  `solution` varchar(1000) NOT NULL COMMENT '期待解决方案',
  `remark` varchar(1000) NOT NULL COMMENT '备注',
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_createtime`(`createtime`),
  KEY `idx_feedbackid`(`feedbackid`),
  KEY `idx_transid`(`transid`),
  KEY `idx_weid`(`weid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_fh_commission` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `from_user` varchar(60) NOT NULL DEFAULT '0' COMMENT '粉丝',
  `fh_id` int(10) unsigned NULL COMMENT '分红id',
  `fh_commission` decimal(10,2) unsigned NULL COMMENT '佣金',
  `content` text NULL,
  `isout` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0为未导出，1为已导出',
  `isshare` int(11) NULL,
  `createtime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_follow` (
  `weid` int(10) unsigned NOT NULL,
  `leader` varchar(100) NOT NULL,
  `follower` varchar(100) NOT NULL,
  `channel` int(10) NOT NULL DEFAULT '0' COMMENT '渠道唯一标示符',
  `credit` int(10) NOT NULL DEFAULT '0',
  `createtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`weid`,`leader`,`follower`,`channel`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_goods` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `pcate` int(10) unsigned NOT NULL DEFAULT '0',
  `ccate` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '1为实体，2为虚拟',
  `status` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `displayorder` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `thumb` varchar(255) NULL DEFAULT '',
  `thumb1` varchar(255) NULL,
  `xsthumb` varchar(255) NULL DEFAULT '',
  `unit` varchar(5) NOT NULL DEFAULT '',
  `description` varchar(1000) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `goodssn` varchar(50) NOT NULL DEFAULT '',
  `productsn` varchar(50) NOT NULL DEFAULT '',
  `marketprice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `productprice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `profit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `costprice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` int(10) NOT NULL DEFAULT '0',
  `totalcnf` int(11) NULL DEFAULT '0' COMMENT '0 拍下减库存 1 付款减库存 2 永久不减',
  `sales` int(10) unsigned NOT NULL DEFAULT '0',
  `spec` varchar(5000) NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  `weight` decimal(10,2) NOT NULL DEFAULT 0.00,
  `credit` int(11) NULL DEFAULT '0',
  `maxbuy` int(11) NULL DEFAULT '0',
  `hasoption` int(11) NULL DEFAULT '0',
  `dispatch` int(11) NULL DEFAULT '0',
  `thumb_url` text NULL,
  `isnew` int(11) NULL DEFAULT '0',
  `ishot` int(11) NULL DEFAULT '0',
  `issendfree` int(11) NULL DEFAULT '0',
  `isdiscount` int(11) NULL DEFAULT '0',
  `isrecommand` int(11) NULL DEFAULT '0',
  `istime` int(11) NULL DEFAULT '0',
  `timestart` int(11) NULL DEFAULT '0',
  `timeend` int(11) NULL DEFAULT '0',
  `viewcount` int(11) NULL DEFAULT '0',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `commission2` int(3) NULL,
  `commission3` int(3) NULL,
  `commission` int(3) NOT NULL,
  `timelinetitle` varchar(50) NOT NULL,
  `timelinedesc` varchar(1000) NOT NULL,
  `timelinethumb` varchar(1000) NOT NULL DEFAULT '',
  `killenable` tinyint(2) NULL DEFAULT 1 COMMENT '砍价开关',
  `killdiscount` decimal(10,2) NULL DEFAULT 0.00 COMMENT '最高单次折扣',
  `killmindiscount` decimal(10,2) NULL DEFAULT 0.00 COMMENT '最低单次折扣',
  `killmaxtime` int(10) NULL DEFAULT '0' COMMENT '最多砍价次数',
  `killtotaldiscount` decimal(10,2) NULL DEFAULT 0.00 COMMENT '最大折扣,达到此值后不能再砍',
  `share_num` int(11) NULL DEFAULT '0' COMMENT '推广次数',
  `isrecommandcategory` tinyint(3) NOT NULL DEFAULT 0 COMMENT '推荐至分类列表 0否  1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_goods_comment` (
  `id` int(11) NOT NULL auto_increment,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `content` varchar(5000) NOT NULL DEFAULT '' COMMENT '内容',
  `pics` varchar(255) NULL COMMENT '图片（评论内容）',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级评论id',
  `weid` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT 1 COMMENT '1审核中 2审核通过 -1驳回',
  `remark` varchar(255) NULL COMMENT '审核备注',
  `createtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '评论时间',
  `audittime` datetime NULL COMMENT '审核时间',
  `operator` varchar(60) NULL COMMENT '审核人',
  `from_user` varchar(255) NOT NULL DEFAULT '' COMMENT '用户',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='商品评论';

CREATE TABLE `ims_bj_qmxk_goods_option` (
  `id` int(11) NOT NULL auto_increment,
  `goodsid` int(10) NULL DEFAULT '0',
  `title` varchar(50) NULL DEFAULT '',
  `thumb` varchar(60) NULL DEFAULT '',
  `productprice` decimal(10,2) NULL DEFAULT 0.00,
  `marketprice` decimal(10,2) NULL DEFAULT 0.00,
  `profit` decimal(10,2) NULL DEFAULT 0.00,
  `costprice` decimal(10,2) NULL DEFAULT 0.00,
  `stock` int(11) NULL DEFAULT '0',
  `weight` decimal(10,2) NULL DEFAULT 0.00,
  `displayorder` int(11) NULL DEFAULT '0',
  `specs` text NULL,
  PRIMARY KEY (`id`),
  KEY `indx_displayorder`(`displayorder`),
  KEY `indx_goodsid`(`goodsid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_goods_param` (
  `id` int(11) NOT NULL auto_increment,
  `goodsid` int(10) NULL DEFAULT '0',
  `title` varchar(50) NULL DEFAULT '',
  `value` text NULL,
  `displayorder` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `indx_displayorder`(`displayorder`),
  KEY `indx_goodsid`(`goodsid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_integral_conversion` (
  `id` int(11) NOT NULL auto_increment,
  `from_user` varchar(255) NOT NULL DEFAULT '' COMMENT '付款人openid',
  `mid` int(11) NOT NULL DEFAULT '0' COMMENT '收款方',
  `credit1` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `credit3` double(11,2) unsigned NOT NULL DEFAULT 0.00 COMMENT '自由余额',
  `createtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `realname` varchar(50) NULL,
  `type` tinyint(3) unsigned NULL COMMENT '1兑换、2积分转自由余额',
  `weid` int(11) unsigned NOT NULL DEFAULT '0',
  `to_realname` varchar(50) NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='积分转换';

CREATE TABLE `ims_bj_qmxk_iptable` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `ip` varchar(64) NOT NULL,
  `goodsid` int(10) unsigned NOT NULL,
  `discount` decimal(10,2) NULL DEFAULT 0.00,
  `title` varchar(128) NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  `begger` varchar(50) NOT NULL DEFAULT '' COMMENT '请求杀价者',
  `giver` varchar(50) NOT NULL DEFAULT '' COMMENT '帮忙杀价着',
  `givername` varchar(50) NOT NULL DEFAULT '' COMMENT '帮忙杀价着',
  `exchangetime` int(10) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_medal` (
  `rules_type` varchar(50) NULL DEFAULT '' COMMENT '1规则1 2:规则2 3:规则3',
  `id` int(11) NOT NULL auto_increment,
  `avatar` varchar(255) NULL COMMENT '头像',
  `medal_name` varchar(50) NULL COMMENT '勋章名称',
  `globalDividend` double(11,2) NULL COMMENT '全球分红比例（相对于每种勋章比例不同）',
  `one_num` int(10) NOT NULL DEFAULT '0' COMMENT '一级代理的个数',
  `two_num` int(10) NOT NULL DEFAULT '0' COMMENT '2级代理的个数',
  `three_num` int(10) NOT NULL DEFAULT '0' COMMENT '3级代理的个数',
  `weid` int(11) NOT NULL DEFAULT '0' COMMENT '微信公众号id',
  `medal_status` tinyint(3) NULL DEFAULT 1 COMMENT '1开启   -1关闭',
  `one_num_sum` int(11) NULL DEFAULT '0',
  `one_two_num_sum` int(11) NULL DEFAULT '0',
  `one_two_three_num_sum` int(11) NULL DEFAULT '0',
  `monetary` double(11,2) NULL COMMENT '购买金额',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='勋章';

CREATE TABLE `ims_bj_qmxk_member` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `shareid` int(11) NULL,
  `from_user` varchar(50) NOT NULL,
  `realname` varchar(20) NOT NULL,
  `mobile` varchar(11) NOT NULL,
  `pwd` varchar(20) NOT NULL,
  `bankcard` varchar(20) NULL,
  `bankprovince` varchar(50) NULL,
  `bankcity` varchar(50) NULL,
  `bankcounty` varchar(50) NULL,
  `bankperson` varchar(50) NULL,
  `bankbelong` varchar(100) NULL,
  `banktype` varchar(20) NULL,
  `alipay` varchar(100) NULL,
  `wxhao` varchar(100) NULL,
  `commission` decimal(10,2) unsigned NULL DEFAULT 0.00 COMMENT '已结佣佣金',
  `zhifu` decimal(10,2) unsigned NULL DEFAULT 0.00 COMMENT '已打款佣金',
  `content` text NULL,
  `createtime` int(10) NOT NULL,
  `flagtime` int(10) NULL COMMENT '为成推广人的时间',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '0为禁用，1为可用',
  `flag` tinyint(1) NULL DEFAULT 0 COMMENT '0为会推广人，1为推广人',
  `clickcount` int(11) NOT NULL DEFAULT '0' COMMENT '点击次数',
  `memberlevel` int(2) NULL DEFAULT '0' COMMENT '会员等级',
  `ischeck` tinyint(1) NULL DEFAULT 0,
  `salesmantime` int(10) NULL,
  `issalesman` tinyint(1) NULL DEFAULT 0,
  `isprofit` tinyint(2) NULL DEFAULT 0 COMMENT '0：不可以分红，1：可审核为分红，2：可分红',
  `salesmanbz` varchar(1000) NULL,
  `openid` varchar(60) NULL COMMENT '微信身份标识',
  `qq` char(30) NULL,
  `work_unit` varchar(100) NULL COMMENT '工作单位',
  `occupation` varchar(50) NULL COMMENT '职业',
  `remark` varchar(255) NULL COMMENT '备注',
  `province` varchar(50) NULL COMMENT '所在地（省）',
  `city` varchar(50) NULL COMMENT '所在地（市）',
  `county` varchar(50) NULL COMMENT '所在地（区）',
  PRIMARY KEY (`id`),
  KEY `idx_member_from_user`(`from_user`),
  KEY `idx_weid`(`weid`)
) ENGINE=MyISAM row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_memberlevel` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属帐号',
  `levelname` varchar(50) NOT NULL COMMENT '会员等级名称',
  `discount` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '折扣率',
  `czje` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '一次性充值或交易金额',
  `tx` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许提现',
  `czmoney` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '充值多少',
  `zsmoney` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '赠送多少',
  `description` text NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_message` (
  `id` int(11) NOT NULL auto_increment,
  `content` text NULL,
  `from_user` varchar(255) NOT NULL DEFAULT '',
  `weid` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `createtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pics` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='互动内容表';

CREATE TABLE `ims_bj_qmxk_message_send` (
  `id` int(11) NOT NULL auto_increment,
  `to_openid` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(3) NOT NULL DEFAULT -1 COMMENT '-1 未读 1已读',
  `messageid` int(11) NOT NULL DEFAULT '0',
  `sendtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='发送表';

CREATE TABLE `ims_bj_qmxk_msg_template` (
  `weid` int(10) NOT NULL,
  `template` varchar(5000) NOT NULL,
  `tkey` varchar(10) NOT NULL,
  `tenable` tinyint(4) NOT NULL DEFAULT 0,
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_order` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `from_user` varchar(50) NOT NULL,
  `shareid` int(10) unsigned NULL DEFAULT '0' COMMENT '推荐人ID',
  `ordersn` varchar(20) NOT NULL,
  `price` varchar(10) NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT 0 COMMENT '-1取消状态，0普通状态，1为已付款，2为已发货，3为成功 ，100为已收货',
  `sendtype` tinyint(1) unsigned NOT NULL COMMENT '1为快递，2为自提',
  `paytype` tinyint(1) unsigned NOT NULL COMMENT '1为余额，2为在线，3为到付',
  `transid` varchar(30) NOT NULL DEFAULT '0' COMMENT '微信支付单号',
  `goodstype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `remark` varchar(1000) NOT NULL DEFAULT '',
  `addressid` int(10) unsigned NOT NULL,
  `expresscom` varchar(30) NOT NULL DEFAULT '',
  `expresssn` varchar(50) NOT NULL DEFAULT '',
  `express` varchar(200) NOT NULL DEFAULT '',
  `goodsprice` decimal(10,2) NULL DEFAULT 0.00,
  `dispatchprice` decimal(10,2) NULL DEFAULT 0.00,
  `dispatch` int(10) NULL DEFAULT '0',
  `discount` decimal(10,2) unsigned NULL DEFAULT 0.00 COMMENT '折扣',
  `createtime` int(10) unsigned NOT NULL,
  `profit` decimal(10,2) NULL,
  `shareid2` int(10) unsigned NULL DEFAULT '0' COMMENT '推荐人ID',
  `shareid3` int(10) unsigned NULL DEFAULT '0' COMMENT '推荐人ID',
  `ismessage` int(1) NOT NULL DEFAULT '0',
  `rsreson` text NULL,
  `used_credit2` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '已使用可购物余额',
  `used_credit3` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '已使用可提现购物余额',
  `non_payment` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '需在线支付的余款',
  `vochuer_money` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '代金券金额',
  `voucher_record_ids` varchar(255) NULL DEFAULT '' COMMENT '代金券记录ID',
  `rebates_money` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '返点金额',
  `special` tinyint(3) NOT NULL DEFAULT 0 COMMENT '0 不是特殊订单   1特殊订单',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_order_goods` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `orderid` int(10) unsigned NOT NULL,
  `goodsid` int(10) unsigned NOT NULL,
  `commission` decimal(10,2) unsigned NULL DEFAULT 0.00 COMMENT '该订单的推荐佣金',
  `commission2` decimal(10,2) unsigned NULL DEFAULT 0.00,
  `commission3` decimal(10,2) unsigned NULL DEFAULT 0.00,
  `applytime` int(10) unsigned NULL COMMENT '申请时间',
  `checktime` int(10) unsigned NULL COMMENT '审核时间',
  `status` tinyint(3) NULL DEFAULT 0 COMMENT '申请状态，-2为标志删除，-1为审核无效，0为未申请，1为正在申请，2为审核通过',
  `content` text NULL,
  `price` decimal(10,2) NULL DEFAULT 0.00,
  `total` int(10) unsigned NOT NULL DEFAULT '1',
  `optionid` int(10) NULL DEFAULT '0',
  `createtime` int(10) unsigned NOT NULL,
  `optionname` text NULL,
  `status2` int(11) NOT NULL DEFAULT '0',
  `status3` int(11) NOT NULL DEFAULT '0',
  `applytime2` int(10) NULL DEFAULT '0' COMMENT '2级申请时间',
  `checktime2` int(10) NULL DEFAULT '0' COMMENT '2级审核时间',
  `applytime3` int(10) NULL DEFAULT '0' COMMENT '3级申请时间',
  `checktime3` int(10) NULL DEFAULT '0' COMMENT '3级审核时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_order_refund` (
  `id` int(11) NOT NULL auto_increment,
  `credit2` double(11,2) unsigned NOT NULL DEFAULT 0.00,
  `credit3` double(11,2) unsigned NOT NULL DEFAULT 0.00,
  `wechat_pay` double(11,2) unsigned NOT NULL DEFAULT 0.00,
  `type` tinyint(3) unsigned NOT NULL DEFAULT 1 COMMENT '1 退款',
  `ordernum` varchar(255) NOT NULL DEFAULT '' COMMENT '订单号',
  `createtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='订单退款记录';

CREATE TABLE `ims_bj_qmxk_phb_medal` (
  `fans_count` int(11) NULL,
  `medal_name` varchar(50) NULL,
  `weid` int(11) NOT NULL,
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_points_record` (
  `id` int(11) NOT NULL auto_increment,
  `points` int(11) NULL COMMENT '积分',
  `type` tinyint(3) NULL COMMENT '类型 1 分享，3 今日签到，4 扫码',
  `from_user` varchar(255) NULL COMMENT '用户 from_user',
  `balance` tinyint(3) NULL COMMENT '收支 1 收入，-1 支出',
  `addtime` datetime NULL COMMENT '添加时间',
  `remark` varchar(255) NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='积分记录表';

CREATE TABLE `ims_bj_qmxk_product` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `goodsid` int(11) NOT NULL,
  `productsn` varchar(50) NOT NULL,
  `title` varchar(1000) NOT NULL,
  `marketprice` decimal(10,0) unsigned NOT NULL,
  `productprice` decimal(10,0) unsigned NOT NULL,
  `total` int(11) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `spec` varchar(5000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_goodsid`(`goodsid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_profit` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `months` varchar(50) NOT NULL,
  `allprofit` decimal(20,2) NOT NULL,
  `modify` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_profit_member` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `from_user` varchar(50) NOT NULL,
  `months` varchar(50) NOT NULL,
  `ispay` int(1) unsigned NOT NULL DEFAULT '0',
  `profit` decimal(10,2) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_qr` (
  `id` int(10) NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `qr_url` varchar(1024) NOT NULL,
  `createtime` int(11) NOT NULL,
  `expiretime` int(11) NOT NULL,
  `media_id` varchar(1024) NOT NULL,
  `channel` int(10) NOT NULL DEFAULT '0' COMMENT '渠道唯一标示符',
  `from_user` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=COMPACT;

CREATE TABLE `ims_bj_qmxk_relation_dividend` (
  `id` int(11) NOT NULL auto_increment,
  `from_user` varchar(60) NULL,
  `money` double(11,2) NULL COMMENT '金额',
  `add_time` datetime NULL COMMENT '添加时间',
  `medal_id` int(11) NULL,
  `d_status` tinyint(3) NULL DEFAULT 0 COMMENT '0未申请 1申请中 -1失效 2打款',
  `weid` int(11) NULL,
  `order_money` double(11,2) NULL COMMENT '订单金额',
  `order_id` int(11) NULL COMMENT '订单id',
  `person_num` int(11) NULL COMMENT '该笔分红时获得该勋章的人数',
  `check_time` datetime NULL COMMENT '审核时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC COMMENT='分红记录表';

CREATE TABLE `ims_bj_qmxk_relation_medal` (
  `id` int(11) NOT NULL auto_increment,
  `from_user` varchar(60) NULL,
  `medal_id` int(11) NULL,
  `get_time` datetime NULL COMMENT '获取时间',
  `status` tinyint(3) NULL DEFAULT 1 COMMENT '1开启  -1禁用',
  `weid` int(11) NULL COMMENT '微信id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC COMMENT='联系表（member与勋章）';

CREATE TABLE `ims_bj_qmxk_rule` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `weid` int(10) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT '',
  `rule` text NULL,
  `terms` text NULL,
  `createtime` int(10) NOT NULL,
  `gzurl` varchar(255) NOT NULL,
  `teamfy` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_rules` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `weid` int(10) NOT NULL,
  `rule` text NULL,
  `terms` text NULL,
  `createtime` int(10) NOT NULL,
  `commtime` int(5) NOT NULL DEFAULT '15' COMMENT '默认15天',
  `promotertimes` int(10) NOT NULL DEFAULT '1' COMMENT '1购买一单 成为会员 0无条件成为会员 2达到单数 3达到金额',
  `promotercount` int(10) NOT NULL DEFAULT '0' COMMENT '成为代理需要成交单数',
  `promotermoney` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '成为代理需要成交总金额',
  `ischeck` tinyint(1) NULL DEFAULT 1 COMMENT '0为未审核，1为审核',
  `clickcredit` int(10) NOT NULL DEFAULT '0' COMMENT '点击获取积分',
  `logincredit` varchar(255) NULL COMMENT '登录积分规则',
  `sharecredit` varchar(255) NULL COMMENT '分享链接规则',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_salary` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `mid` int(10) unsigned NOT NULL COMMENT '粉丝ID',
  `commission` decimal(10,2) unsigned NOT NULL COMMENT '佣金',
  `content` text NULL,
  `isout` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0为未导出，1为已导出',
  `createtime` int(10) NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT 0 COMMENT '状态  0未审核   1已审核   -1失效  2打款',
  `from_user` varchar(255) NOT NULL DEFAULT '',
  `checktime` int(11) NOT NULL DEFAULT '0' COMMENT '审核日期',
  `paymenttime` int(11) NOT NULL DEFAULT '0' COMMENT '打款时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM row_format=COMPACT;

CREATE TABLE `ims_bj_qmxk_salesman` (
  `weid` int(10) NOT NULL,
  `money` decimal(10,2) NULL,
  `member` int(10) NULL
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_scanlog` (
  `id` int(11) NOT NULL auto_increment,
  `weid` int(11) NULL,
  `from_user` varchar(50) NOT NULL,
  `shareid` int(11) NULL,
  `scantime` int(10) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_share_history` (
  `sharemid` int(11) NULL,
  `weid` int(11) NULL,
  `from_user` varchar(50) NULL,
  `id` int(11) NOT NULL auto_increment,
  `joinway` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0默认驱动加入,1二维码加入',
  `history_create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_spec` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `displaytype` tinyint(3) unsigned NOT NULL,
  `content` text NOT NULL,
  `goodsid` int(11) NULL DEFAULT '0',
  `displayorder` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_spec_item` (
  `id` int(11) NOT NULL auto_increment,
  `weid` int(11) NULL DEFAULT '0',
  `specid` int(11) NULL DEFAULT '0',
  `title` varchar(255) NULL DEFAULT '',
  `thumb` varchar(255) NULL DEFAULT '',
  `show` int(11) NULL DEFAULT '0',
  `displayorder` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `indx_displayorder`(`displayorder`),
  KEY `indx_show`(`show`),
  KEY `indx_specid`(`specid`),
  KEY `indx_weid`(`weid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_bj_qmxk_subscribe_dividend` (
  `id` int(11) NOT NULL auto_increment,
  `from_user` varchar(60) NULL,
  `money` double(11,2) NULL COMMENT '金额',
  `add_time` datetime NULL COMMENT '添加时间',
  `d_status` tinyint(3) NULL DEFAULT 0 COMMENT '0未申请 1申请中 -1失效 2打款',
  `weid` int(11) NULL,
  `person_num` int(11) NULL COMMENT '该笔分红时获得该勋章的人数',
  `check_time` datetime NULL COMMENT '审核时间',
  `order_money` double(11,2) NULL COMMENT '订单金额',
  `order_id` int(11) NULL COMMENT '订单id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=COMPACT COMMENT='关注分红记录表';

CREATE TABLE `ims_bj_qmxk_transfer` (
  `id` int(11) NOT NULL auto_increment,
  `from_user` varchar(255) NOT NULL DEFAULT '' COMMENT '付款人openid',
  `to_mid` int(11) NOT NULL DEFAULT '0' COMMENT '收款人ID',
  `money` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '转账金额',
  `createtime` datetime NULL COMMENT '创建时间',
  `remark` varchar(1000) NULL,
  `type` tinyint(3) NOT NULL DEFAULT 1 COMMENT '1自由余额  2购物余额 3购物积分',
  `weid` int(11) NOT NULL DEFAULT '0',
  `from_realname` varchar(50) NULL COMMENT '付款人姓名',
  `to_realname` varchar(50) NULL COMMENT '收款人姓名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='自由余额转账';

CREATE TABLE `ims_bj_qmxk_transpond` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NULL DEFAULT '' COMMENT '转发名称',
  `description` varchar(255) NULL COMMENT '描述',
  `weid` int(11) NOT NULL DEFAULT '0',
  `from_user` varchar(255) NOT NULL DEFAULT '',
  `avatar` varchar(255) NULL,
  `mobile` varchar(11) NULL COMMENT '手机号码',
  `nickname` varchar(50) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='转发设置';

CREATE TABLE `ims_bj_qmxk_voucher` (
  `id` int(11) NOT NULL auto_increment,
  `voucher_name` varchar(50) NULL COMMENT '代金券名称',
  `money` decimal(10,2) NULL DEFAULT 0.00 COMMENT '金额',
  `pic` varchar(255) NULL COMMENT '图片',
  `start_validity` datetime NULL COMMENT '有效期开始（默认当天）',
  `end_validity` datetime NULL COMMENT '有效期结束（默认当天推迟一月）',
  `add_time` datetime NULL COMMENT '添加时间',
  `operator` varchar(50) NULL COMMENT '添加账号',
  `remark` varchar(255) NULL DEFAULT '',
  `flag` tinyint(3) NOT NULL DEFAULT 1 COMMENT '1 普通2 关注',
  `weid` int(11) NOT NULL DEFAULT '0',
  `days` int(11) NOT NULL DEFAULT '0' COMMENT '有效天数',
  `setmodel` tinyint(3) NOT NULL DEFAULT 1 COMMENT '有效时间设置 1模式1  2模式二 有效天数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='代金券表';

CREATE TABLE `ims_bj_qmxk_voucher_receive` (
  `id` int(11) NOT NULL auto_increment,
  `from_user` varchar(50) NULL COMMENT '用户from_user',
  `voucher_id` int(11) NULL COMMENT '代金券id',
  `state` tinyint(3) NULL COMMENT '状态 1 已领取 2 已使用',
  `receive_time` datetime NULL COMMENT '领取时间',
  `use_time` datetime NULL COMMENT '使用时间',
  `voucher_name` varchar(50) NULL COMMENT '代金券名称(冗余)',
  `money` decimal(10,2) NULL DEFAULT 0.00 COMMENT '金额(冗余)',
  `pic` varchar(255) NULL COMMENT '图片(冗余)',
  `start_validity` datetime NULL COMMENT '有效期开始（默认当天）(冗余)',
  `end_validity` datetime NULL COMMENT '有效期结束（默认当天推迟一月）(冗余)',
  `remark` varchar(255) NULL DEFAULT '' COMMENT '备注(冗余)',
  `flag` tinyint(3) NOT NULL DEFAULT 2 COMMENT '1 普通 1 关注(冗余)',
  `days` int(11) NOT NULL DEFAULT '0' COMMENT '有效天数(冗余)',
  `setmodel` tinyint(3) NOT NULL DEFAULT 1 COMMENT '有效时间设置 1模式1 2模式二 有效天数(冗余)',
  `weid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='代金券领取记录表';

CREATE TABLE `ims_bj_qmxk_zdadv` (
  `id` int(11) NOT NULL auto_increment,
  `weid` int(11) NULL DEFAULT '0',
  `advname` varchar(50) NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `thumb` varchar(255) NULL DEFAULT '',
  `displayorder` int(11) NULL DEFAULT '0',
  `enabled` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `indx_displayorder`(`displayorder`),
  KEY `indx_enabled`(`enabled`),
  KEY `indx_weid`(`weid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_cache` (
  `key` varchar(50) NOT NULL COMMENT '缓存键名',
  `value` mediumtext NOT NULL COMMENT '缓存内容',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB row_format=DYNAMIC COMMENT='缓存表';

CREATE TABLE `ims_card` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `color` varchar(255) NOT NULL DEFAULT '',
  `background` varchar(255) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL DEFAULT '',
  `format` varchar(50) NOT NULL DEFAULT '',
  `fields` varchar(1000) NOT NULL DEFAULT '',
  `snpos` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_card_coupon` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `starttime` int(10) NOT NULL DEFAULT '0',
  `endtime` int(10) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL,
  `pretotal` int(11) NOT NULL DEFAULT '1',
  `total` int(11) NOT NULL DEFAULT '1',
  `content` text NOT NULL,
  `displayorder` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `createtime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_card_log` (
  `id` int(11) NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `from_user` varchar(50) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '1积分，2金额，3优惠券',
  `content` varchar(255) NOT NULL DEFAULT '',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_card_members` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `from_user` varchar(50) NOT NULL DEFAULT '',
  `cardsn` varchar(20) NOT NULL DEFAULT '',
  `credit1` varchar(15) NOT NULL DEFAULT '0',
  `credit2` varchar(15) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_card_members_coupon` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `couponid` int(10) unsigned NOT NULL,
  `from_user` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1为正常状态，2为已使用',
  `receiver` varchar(50) NOT NULL,
  `consumetime` int(10) unsigned NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_card_password` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_default_reply_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(11) NOT NULL COMMENT '微信号ID，关联wechats表',
  `from_user` varchar(50) NOT NULL COMMENT '用户的唯一身份ID',
  `lastupdate` int(10) unsigned NOT NULL COMMENT '用户最后发送信息时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_fans` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL COMMENT '公众号ID',
  `from_user` varchar(50) NOT NULL COMMENT '用户的唯一身份ID',
  `salt` char(8) NOT NULL DEFAULT '' COMMENT '加密盐',
  `follow` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否订阅',
  `credit1` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `credit2` double unsigned NOT NULL DEFAULT 0 COMMENT '余额（可购物）',
  `credit3` double(11,2) unsigned NOT NULL DEFAULT 0.00 COMMENT '自由余额  （可提现可购物）',
  `credit4` double(11,2) unsigned NOT NULL DEFAULT 0.00 COMMENT '不可支配余额（贡献给平台发展基金或者慈善基金）',
  `createtime` int(10) unsigned NOT NULL COMMENT '加入时间',
  `realname` varchar(50) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(200) NOT NULL DEFAULT '' COMMENT '头像',
  `qq` varchar(15) NOT NULL DEFAULT '' COMMENT 'QQ号',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `fakeid` varchar(30) NOT NULL DEFAULT '',
  `vip` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'VIP级别,0为普通会员',
  `gender` tinyint(1) NOT NULL DEFAULT 0 COMMENT '性别(0:保密 1:男 2:女)',
  `birthyear` smallint(6) unsigned NOT NULL DEFAULT 0 COMMENT '生日年',
  `birthmonth` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '生日月',
  `birthday` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '生日',
  `constellation` varchar(10) NOT NULL DEFAULT '' COMMENT '星座',
  `zodiac` varchar(5) NOT NULL DEFAULT '' COMMENT '生肖',
  `telephone` varchar(15) NOT NULL DEFAULT '' COMMENT '固定电话',
  `idcard` varchar(30) NOT NULL DEFAULT '' COMMENT '证件号码',
  `studentid` varchar(50) NOT NULL DEFAULT '' COMMENT '学号',
  `grade` varchar(10) NOT NULL DEFAULT '' COMMENT '班级',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '邮寄地址',
  `zipcode` varchar(10) NOT NULL DEFAULT '' COMMENT '邮编',
  `nationality` varchar(30) NOT NULL DEFAULT '' COMMENT '国籍',
  `resideprovince` varchar(30) NOT NULL DEFAULT '' COMMENT '居住省份',
  `residecity` varchar(30) NOT NULL DEFAULT '' COMMENT '居住城市',
  `residedist` varchar(30) NOT NULL DEFAULT '' COMMENT '居住行政区/县',
  `graduateschool` varchar(50) NOT NULL DEFAULT '' COMMENT '毕业学校',
  `company` varchar(50) NOT NULL DEFAULT '' COMMENT '公司',
  `education` varchar(10) NOT NULL DEFAULT '' COMMENT '学历',
  `occupation` varchar(30) NOT NULL DEFAULT '' COMMENT '职业',
  `position` varchar(30) NOT NULL DEFAULT '' COMMENT '职位',
  `revenue` varchar(10) NOT NULL DEFAULT '' COMMENT '年收入',
  `affectivestatus` varchar(30) NOT NULL DEFAULT '' COMMENT '情感状态',
  `lookingfor` varchar(255) NOT NULL DEFAULT '' COMMENT ' 交友目的',
  `bloodtype` varchar(5) NOT NULL DEFAULT '' COMMENT '血型',
  `height` varchar(5) NOT NULL DEFAULT '' COMMENT '身高',
  `weight` varchar(5) NOT NULL DEFAULT '' COMMENT '体重',
  `alipay` varchar(30) NOT NULL DEFAULT '' COMMENT '支付宝帐号',
  `msn` varchar(30) NOT NULL DEFAULT '' COMMENT 'MSN',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `taobao` varchar(30) NOT NULL DEFAULT '' COMMENT '阿里旺旺',
  `site` varchar(30) NOT NULL DEFAULT '' COMMENT '主页',
  `bio` text NOT NULL COMMENT '自我介绍',
  `interest` text NOT NULL COMMENT '兴趣爱好',
  `is_children` tinyint(1) NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_from_user`(`from_user`),
  KEY `weid`(`weid`)
) ENGINE=MyISAM row_format=DYNAMIC;

CREATE TABLE `ims_log` (
  `id` int(11) NOT NULL auto_increment,
  `from_user` varchar(255) NOT NULL DEFAULT '',
  `weid` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(3) NOT NULL DEFAULT 1 COMMENT '1登录 2其他',
  `remark` varchar(255) NULL,
  `lastdate` date NOT NULL DEFAULT '0000-00-00',
  `lasttime` datetime NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='系统日志表';

CREATE TABLE `ims_members` (
  `uid` int(10) unsigned NOT NULL auto_increment COMMENT '用户编号',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL COMMENT '用户名',
  `password` varchar(200) NOT NULL COMMENT '用户密码',
  `salt` varchar(10) NOT NULL COMMENT '加密盐',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '会员状态，0正常，-1禁用',
  `joindate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `joinip` varchar(15) NOT NULL DEFAULT '',
  `lastvisit` int(10) unsigned NOT NULL DEFAULT '0',
  `lastip` varchar(15) NOT NULL DEFAULT '',
  `remark` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username`(`username`)
) ENGINE=InnoDB row_format=DYNAMIC COMMENT='用户表';

CREATE TABLE `ims_members_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `modules` varchar(5000) NOT NULL DEFAULT '',
  `templates` varchar(5000) NOT NULL DEFAULT '',
  `maxaccount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0为不限制',
  `maxsubaccount` int(10) unsigned NOT NULL COMMENT '子公号最多添加数量，为0为不可以添加',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_members_permission` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `resourceid` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1为模块,2为模板',
  PRIMARY KEY (`id`),
  KEY `idx_uid`(`uid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_members_profile` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL,
  `createtime` int(10) unsigned NOT NULL COMMENT '加入时间',
  `realname` varchar(10) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `nickname` varchar(20) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(100) NOT NULL DEFAULT '' COMMENT '头像',
  `qq` varchar(15) NOT NULL DEFAULT '' COMMENT 'QQ号',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `fakeid` varchar(30) NOT NULL,
  `vip` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'VIP级别,0为普通会员',
  `gender` tinyint(1) NOT NULL DEFAULT 0 COMMENT '性别(0:保密 1:男 2:女)',
  `birthyear` smallint(6) unsigned NOT NULL DEFAULT 0 COMMENT '生日年',
  `birthmonth` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '生日月',
  `birthday` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '生日',
  `constellation` varchar(10) NOT NULL DEFAULT '' COMMENT '星座',
  `zodiac` varchar(5) NOT NULL DEFAULT '' COMMENT '生肖',
  `telephone` varchar(15) NOT NULL DEFAULT '' COMMENT '固定电话',
  `idcard` varchar(30) NOT NULL DEFAULT '' COMMENT '证件号码',
  `studentid` varchar(50) NOT NULL DEFAULT '' COMMENT '学号',
  `grade` varchar(10) NOT NULL DEFAULT '' COMMENT '班级',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '邮寄地址',
  `zipcode` varchar(10) NOT NULL DEFAULT '' COMMENT '邮编',
  `nationality` varchar(30) NOT NULL DEFAULT '' COMMENT '国籍',
  `resideprovince` varchar(30) NOT NULL DEFAULT '' COMMENT '居住省份',
  `residecity` varchar(30) NOT NULL DEFAULT '' COMMENT '居住城市',
  `residedist` varchar(30) NOT NULL DEFAULT '' COMMENT '居住行政区/县',
  `graduateschool` varchar(50) NOT NULL DEFAULT '' COMMENT '毕业学校',
  `company` varchar(50) NOT NULL DEFAULT '' COMMENT '公司',
  `education` varchar(10) NOT NULL DEFAULT '' COMMENT '学历',
  `occupation` varchar(30) NOT NULL DEFAULT '' COMMENT '职业',
  `position` varchar(30) NOT NULL DEFAULT '' COMMENT '职位',
  `revenue` varchar(10) NOT NULL DEFAULT '' COMMENT '年收入',
  `affectivestatus` varchar(30) NOT NULL DEFAULT '' COMMENT '情感状态',
  `lookingfor` varchar(255) NOT NULL DEFAULT '' COMMENT ' 交友目的',
  `bloodtype` varchar(5) NOT NULL DEFAULT '' COMMENT '血型',
  `height` varchar(5) NOT NULL DEFAULT '' COMMENT '身高',
  `weight` varchar(5) NOT NULL DEFAULT '' COMMENT '体重',
  `alipay` varchar(30) NOT NULL DEFAULT '' COMMENT '支付宝帐号',
  `msn` varchar(30) NOT NULL DEFAULT '' COMMENT 'MSN',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `taobao` varchar(30) NOT NULL DEFAULT '' COMMENT '阿里旺旺',
  `site` varchar(30) NOT NULL DEFAULT '' COMMENT '主页',
  `bio` text NOT NULL COMMENT '自我介绍',
  `interest` text NOT NULL COMMENT '兴趣爱好',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_owen_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx`(`last_activity`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_paylog` (
  `plid` bigint(20) unsigned NOT NULL auto_increment,
  `type` varchar(20) NOT NULL DEFAULT '',
  `weid` int(11) NOT NULL,
  `openid` varchar(40) NOT NULL DEFAULT '',
  `tid` varchar(64) NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `module` varchar(50) NOT NULL DEFAULT '',
  `tag` varchar(2000) NOT NULL DEFAULT '',
  `used_credit2` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '已使用可购物余额',
  `used_credit3` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '已使用可提现购物余额',
  `non_payment` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '需在线支付的余款',
  `vochuer_money` double(11,2) NOT NULL DEFAULT 0.00 COMMENT '代金券金额',
  `voucher_record_ids` varchar(255) NULL DEFAULT '' COMMENT '代金券记录ID',
  PRIMARY KEY (`plid`),
  KEY `idx_openid`(`openid`),
  KEY `idx_tid`(`tid`),
  KEY `idx_weid`(`weid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_sessions` (
  `sid` char(32) NOT NULL DEFAULT '' COMMENT 'sessionid唯一标识',
  `weid` int(10) unsigned NOT NULL COMMENT '所属公众号',
  `from_user` varchar(50) NOT NULL COMMENT '用户唯一标识',
  `data` varchar(500) NOT NULL,
  `expiretime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '超时时间',
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_settings` (
  `key` varchar(200) NOT NULL COMMENT '设置键名',
  `value` text NOT NULL COMMENT '设置内容，大量数据将序列化',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_share_record` (
  `id` int(11) NOT NULL auto_increment,
  `share_type` varchar(50) NULL COMMENT '分享类型 1朋友圈 2微信好友 3qq 4微博',
  `model` varchar(30) NULL COMMENT '模型',
  `record_id` int(11) NULL COMMENT '数据id',
  `share_id` int(11) NULL COMMENT '分享者id',
  `from_user` varchar(255) NULL COMMENT '分享者openid',
  `weid` int(11) NULL,
  `sharetime` datetime NULL COMMENT '分享时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM COMMENT='分享记录';

CREATE TABLE `ims_wechats` (
  `weid` int(10) unsigned NOT NULL auto_increment,
  `hash` char(5) NOT NULL COMMENT '用户标识. 随机生成保持不重复',
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '公众号类型，1微信，2易信',
  `uid` varchar(255) NOT NULL DEFAULT '0' COMMENT '关联的用户',
  `token` varchar(32) NOT NULL COMMENT '随机生成密钥',
  `EncodingAESKey` varchar(43) NOT NULL,
  `access_token` varchar(1000) NOT NULL DEFAULT '' COMMENT '存取凭证结构',
  `level` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '接口权限级别, 0 普通订阅号, 1 认证订阅号|普通服务号, 2认证服务号',
  `name` varchar(30) NOT NULL COMMENT '公众号名称',
  `account` varchar(30) NOT NULL COMMENT '微信帐号',
  `original` varchar(50) NOT NULL,
  `signature` varchar(100) NOT NULL COMMENT '功能介绍',
  `country` varchar(10) NOT NULL,
  `province` varchar(3) NOT NULL,
  `city` varchar(15) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  `welcome` varchar(1000) NOT NULL,
  `default` varchar(1000) NOT NULL,
  `default_message` varchar(500) NOT NULL DEFAULT '' COMMENT '其他消息类型默认处理器',
  `default_period` tinyint(3) unsigned NOT NULL COMMENT '回复周期时间',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  `key` varchar(50) NOT NULL,
  `secret` varchar(50) NOT NULL,
  `styleid` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '风格ID',
  `payment` varchar(5000) NOT NULL DEFAULT '',
  `shortcuts` varchar(2000) NOT NULL DEFAULT '',
  `quickmenu` varchar(2000) NOT NULL DEFAULT '',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0',
  `subwechats` varchar(1000) NOT NULL DEFAULT '',
  `siteinfo` varchar(1000) NOT NULL DEFAULT '',
  `menuset` text NOT NULL COMMENT '自定义菜单历史',
  `jsapi_ticket` varchar(1000) NOT NULL,
  UNIQUE KEY `hash`(`hash`),
  PRIMARY KEY (`weid`),
  KEY `idx_key`(`key`),
  KEY `idx_parentid`(`parentid`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `ims_wechats_modules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `weid` int(10) unsigned NOT NULL,
  `mid` int(10) unsigned NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB row_format=DYNAMIC;

CREATE TABLE `seq` (
  `name` varchar(50) NOT NULL,
  `val` int(10) unsigned NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB;

INSERT INTO `seq` (`name`, `val`) VALUES
('guest_seq', 10);

CREATE FUNCTION `seq`(seq_name char(50)) RETURNS int(11)
begin
 UPDATE seq SET val=last_insert_id(val+1) WHERE name=seq_name;
 RETURN last_insert_id();
end;

";

pdo_run($sql);