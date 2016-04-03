<?php


$sql = "
CREATE TABLE IF NOT EXISTS  `ims_bj_qmxk_msg_template` (
  `weid` int(10) NOT NULL,
  `template` varchar(5000) NOT NULL,
  `tkey` varchar(10) NOT NULL,
  `tenable` tinyint(4) NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS  `ims_bj_qmxk_follow` (
  `weid` int(10) unsigned NOT NULL,
  `leader` varchar(100) NOT NULL,
  `follower` varchar(100) NOT NULL,
  `channel` int(10) NOT NULL DEFAULT '0' COMMENT '渠道唯一标示符',
  `credit` int(10) NOT NULL DEFAULT '0',
  `createtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`weid`,`leader`,`follower`,`channel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS  `ims_bj_qmxk_qr` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL,
  `qr_url` varchar(1024) NOT NULL,
  `createtime` int(11) NOT NULL,
  `expiretime` int(11) NOT NULL,
  `media_id` varchar(1024) NOT NULL,
  `channel` int(10) NOT NULL DEFAULT '0' COMMENT '渠道唯一标示符',
  `from_user` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_bj_qmxk_channel` (
  `channel` int(10) NOT NULL AUTO_INCREMENT,
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
  `isdel`  int(5)  NOT NULL DEFAULT '0',
  PRIMARY KEY (`channel`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE  IF NOT EXISTS `ims_bj_qmxk_phb_medal` (
  `fans_count` int(11) DEFAULT NULL,
  `medal_name` varchar(50) DEFAULT NULL,
  `weid` int(11) NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";

pdo_run($sql);

if(!pdo_fieldexists('bj_qmxk_goods', 'issendfree')) {
	pdo_query("ALTER TABLE ".tablename('bj_qmxk_goods')." ADD COLUMN `issendfree` int(11) DEFAULT '0' COMMENT '是否包邮';");
}

if(!pdo_fieldexists('bj_qmxk_rules', 'promotercount')) {
	pdo_query("ALTER TABLE ".tablename('bj_qmxk_rules')." ADD COLUMN `promotercount` int(10) DEFAULT '0' COMMENT '成为代理需要成交单数';");
}

if(!pdo_fieldexists('bj_qmxk_rules', 'promotermoney')) {
	pdo_query("ALTER TABLE ".tablename('bj_qmxk_rules')." ADD COLUMN `promotermoney` decimal(10,2) DEFAULT '0.00' COMMENT '成为代理需要成交总金额';");
}

if(!pdo_fieldexists('bj_qmxk_share_history', 'joinway')) {
	pdo_query("ALTER TABLE ".tablename('bj_qmxk_share_history')." ADD COLUMN `joinway` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0默认驱动加入,1二维码加入';");
}

if(!pdo_indexexists('bj_qmxk_member', 'idx_member_from_user')) {
	pdo_query("ALTER TABLE ".tablename('bj_qmxk_member')." ADD INDEX `idx_member_from_user` (`from_user`);");
}
if(!pdo_indexexists('bj_qmxk_member', 'idx_weid')) {
	pdo_query("ALTER TABLE ".tablename('bj_qmxk_member')." ADD INDEX `idx_weid` (`weid`);");
}