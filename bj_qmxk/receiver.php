<?php
defined('IN_IA') or exit('Access Denied');
require_once 'class/core.php';
class bj_qmxkModuleReceiver extends WeModuleReceiver {
	public function receive() {		
		if ($this->message['msgtype'] == 'event') {
			if ($this->message['event'] == 'SCAN' || $this->message['event'] == 'subscribe' && !empty($this->message['ticket'])) {
				
				$day_cookies = 15;
				$shareid = 'BAIJIA_COOKIE_SID' . $_W['weid'];
				$sceneid = $this->message['eventkey'];
				$row = pdo_fetch("SELECT weid, from_user FROM ".tablename('bj_qmxk_qr')." WHERE id = ".$sceneid);
				$profile = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE weid = :weid  AND from_user = :from_user AND flag = 1", array(
					':weid' => $row['weid'],
					':from_user' => $row['from_user']
				));
				$mid = $profile['id']; //上级id
				if (($mid != $_COOKIE[$shareid]) && !empty($mid)) {
					$core = new bj_qmxkModuleCore();
					$core->shareClick1($this->message['from'], $mid, 1);
					setcookie($shareid, $mid , time() + 3600 * 24 * $day_cookies);
				}

			}
		}
	}
}
