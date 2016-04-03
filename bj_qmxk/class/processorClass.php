<?php

defined('IN_IA') or exit('Access Denied');
require_once  IA_ROOT . '/source/class/account.class.php';
class bj_qmxkModuleProcessorCore extends WeModuleProcessor {
    public function respond() {
        if ($this->message['content'] == '分销专属二维码') {
            $account = $GLOBALS['_W']['account'];
            $express = pdo_fetch("select * from " . tablename('bj_qmxk_channel') . " WHERE weid=:weid and active=1 and isdel=0 limit 1", array(
                ':weid' => $GLOBALS['_W']['weid']
            ));
            $profile = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE weid = :weid  AND from_user = :from_user AND flag = 1", array(
                ':weid' => $GLOBALS['_W']['weid'],
                ':from_user' => $this->message['from']
            ));
            if (empty($profile['id'])) {
                $regurl = $GLOBALS['_W']['siteroot'] . "mobile.php?act=module&name=bj_qmxk&do=fansIndex&weid=" . $GLOBALS['_W']['weid'];
                return $this->respText("请先注册成代理！ <a href='" . $regurl . "'>马上注册成为分销员</a>");
            }
            if (!empty($express['channel'])) {
                $homeurl = $GLOBALS['_W']['siteroot'] . "mobile.php?act=module&name=bj_qmxk&do=list&weid=" . $GLOBALS['_W']['weid'];
                $follow = pdo_fetch("select * from " . tablename('bj_qmxk_follow') . " WHERE weid=:weid and follower=:from_user  limit 1", array(
                    ':weid' => $GLOBALS['_W']['weid'],
                    ':from_user' => $this->message['from']
                ));
                if (!empty($follow['follower'])) {
                    if ($follow['createtime'] > TIMESTAMP) {
                        exit;
                    } else {
                        pdo_update('bj_qmxk_follow', array(
                            'createtime' => TIMESTAMP + (3)
                        ) , array(
                            'weid' => $GLOBALS['_W']['weid'],
                            'follower' => $this->message['from']
                        ));
                    }
                } else {
                    $insert = array(
                        'weid' => $GLOBALS['_W']['weid'],
                        'follower' => $this->message['from'],
                        'leader' => $this->message['from'],
                        'channel' => '',
                        'credit' => 0,
                        'createtime' => TIMESTAMP + (3)
                    );
                    pdo_insert('bj_qmxk_follow', $insert);
                }
                $qmjf_qr = pdo_fetch("select * from " . tablename('bj_qmxk_qr') . " WHERE weid=:weid and from_user=:from_user and channel=:channel  limit 1", array(
                    ':weid' => $GLOBALS['_W']['weid'],
                    ':from_user' => $this->message['from'],
                    ':channel' => $express['channel']
                ));
                if ((empty($qmjf_qr['id']) || empty($qmjf_qr['qr_url']) || empty($qmjf_qr['media_id']) || empty($qmjf_qr['id'])) || (!empty($qmjf_qr['expiretime']) && $qmjf_qr['expiretime'] < TIMESTAMP)) {
                    pdo_update('bj_qmxk_follow', array(
                        'createtime' => TIMESTAMP + (6)
                    ) , array(
                        'weid' => $GLOBALS['_W']['weid'],
                        'follower' => $this->message['from']
                    ));
                    if (!empty($express['notice'])) {
                        $this->sendcustomMsg($account, $this->message['from'], $express['notice']);
                    }
                    if (empty($qmjf_qr['id'])) {
                        $insert = array(
                            'weid' => $GLOBALS['_W']['weid'],
                            'from_user' => $this->message['from'],
                            'channel' => $express['channel'],
                            'qr_url' => '',
                            'media_id' => 0,
                            'expiretime' => TIMESTAMP + (60 * 24 * 6) ,
                            'createtime' => TIMESTAMP
                        );
                        pdo_insert('bj_qmxk_qr', $insert);
                    }
                    $tqmjf_qr = pdo_fetch("select * from " . tablename('bj_qmxk_qr') . " WHERE weid=:weid and from_user=:from_user and channel=:channel limit 1", array(
                        ':weid' => $GLOBALS['_W']['weid'],
                        ':from_user' => $this->message['from'],
                        ':channel' => $express['channel']
                    ));
					$newid = $tqmjf_qr['id'];
					//选择是跳转到商城首页（引导素材）还是关注界面
                    if ($express['msgtype'] == 2) {
                    	//关注界面
                        $qrfile = $this->getLimitQR($account, $newid, $this->message['from']);
                    } else {
						if (!empty($profile['id'])) {
							$qrfile = $this->getURLQR($profile['id'], $this->message['from'], $homeurl);
						} else {
							$qrfile = $this->getURLQR(0, $this->message['from'], $homeurl);
						}
                    }
                    for ($a = 0; $a < 3; $a++) {
                        $qrpic = $this->genImage($express['bg'], $qrfile, $GLOBALS['_W']['weid'], $this->message['from']);
                        if (!empty($qrpic)) {
                            $a = 4;
                        }
                    }
                    $media_id = $this->uploadImage($account, IA_ROOT . $qrpic);
                    $content = @json_decode($media_id, true);
                    pdo_update('bj_qmxk_qr', array(
                        'expiretime' => TIMESTAMP + (86400 * 2) ,
                        'media_id' => $content['media_id'],
                        'qr_url' => $qrpic
                    ) , array(
                        'id' => $newid
                    ));
                    $this->sendcustomIMG($account, $this->message['from'], $content['media_id']);
                    exit();
                } else {
                    return $this->respImage($qmjf_qr['media_id']);
                }
            } else {
                return $this->respText('商家未设置二维码生成');
            }
        }
    }
	
	public function aaPost($from_user, $newid) {
		global $_W,$_GPC;
		
		$barcode = array(
				'expire_seconds' => '',
				'action_name' => '',
				'action_info' => array(
						'scene' => array('scene_id' => ''),
				),
		);
		$uniacccount = WeAccount::create($_W['weid']);
			
		$barcode['action_info']['scene']['scene_id'] = $newid;
		$barcode['action_name'] = 'QR_LIMIT_SCENE';
		$result = $uniacccount->barCodeCreateFixed($barcode);
			
		if(!is_error($result)) {
			$insert = array(
				'weid' => $_W['weid'],
				'qrcid' => $barcode['action_info']['scene']['scene_id'],
				'name' => $from_user,
				'keyword' => $barcode['action_info']['scene']['scene_id'],
				'ticket' => $result['ticket'],
				'model' => 2,
				'expire' => 0,
				'createtime' => TIMESTAMP,
				'status' => '1',
			);
			pdo_insert('qrcode', $insert);
		} 
		$uniacccount = WeAccount::create($_W['weid']);
		$qrcodeurl = $uniacccount->apis['barcode']['display'];
		return sprintf($qrcodeurl, urlencode($result['ticket']));
	}
	
    public function getLimitQR($account, $scene_id, $from_user)
    {
        $qr_url = null;
        $data = array('action_name' => 'QR_LIMIT_SCENE', 'action_info' => array('scene' => array('scene_id' => $scene_id)));
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$this->get_weixin_token($account)}";
        $ret = $this->curlPost($url, json_encode($data));
        $content = @json_decode($ret, true);
		$qr_url = $this->getQRImage($content['ticket']);
		$insert = array(
			'weid' => $GLOBALS['_W']['weid'],
			'qrcid' => $scene_id,
			'name' => $from_user,
			'keyword' => $scene_id,
			'ticket' => $content['ticket'],
			'model' => 2,
			'expire' => 0,
			'createtime' => TIMESTAMP,
			'status' => '1',
		);
		pdo_insert('qrcode', $insert);
		file_put_contents(IA_ROOT . BJ_QMXK_BASE . "/common/text.txt", var_export($content,TRUE),FILE_APPEND); 
		return $qr_url;
    }
    public function getQRImage($ticket)
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
        return $url;
    }	
    public function uploadImage($account, $img) {
        return $this->uploadRes($this->get_weixin_token($account) , $img, 'image');
    }
    public function sendcustomMsg($account, $from_user, $msg) {
        $access_token = $this->get_weixin_token($account);
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        $post = '{"touser":"' . $from_user . '","msgtype":"text","text":{"content":"' . $msg . '"}}';
        $this->curlPost($url, $post);
    }
    public function sendcustomIMG($account, $from_user, $msg) {
        $access_token = $this->get_weixin_token($account);
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        $post = '{"touser":"' . $from_user . '","msgtype":"image","image":{"media_id":"' . $msg . '"}}';
        $this->curlPost($url, $post);
    }
    public function getURLQR($mid, $from_user, $homeurl) {
        include IA_ROOT . BJ_QMXK_BASE . "/common/phpqrcode.php";
        $value = $homeurl . '&mid=' . $mid;
        $errorCorrectionLevel = "L";
        $matrixPointSize = "4";
        $rand_file = $from_user . '.png';
        $att_target_file = 'qr-' . $rand_file;
        $target_file = IA_ROOT . BJ_QMXK_BASE . '/tmppic/' . $att_target_file;
        QRcode::png($value, $target_file, $errorCorrectionLevel, $matrixPointSize);
        return $target_file;
    }
    public function uploadRes($access_token, $img, $type) {
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type={$type}";
        $post = array(
            'media' => '@' . $img
        );
        $ret = $this->curlPost($url, $post);
        return $ret;
    }
    public function curlPost($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $info = curl_exec($ch);
        curl_close($ch);
        return $info;
    }
    private function genImage($bg, $qr_file, $weid, $from_user) {
        $express = pdo_fetch("select * from " . tablename('bj_qmxk_channel') . " WHERE weid=:weid and active=1 limit 1", array(
            ':weid' => $weid
        ));
        if (!empty($express['channel'])) {
            $rand_file = $from_user . '.jpg';
            $att_target_file = 'qr-image-' . $rand_file;
            $att_head_cache_file = 'head-image-' . $rand_file;
            $target_file = IA_ROOT . BJ_QMXK_BASE . '/tmppic/' . $att_target_file;
            $head_cache_file = IA_ROOT . BJ_QMXK_BASE . '/tmppic/' . $att_head_cache_file;
            $bg_file = IA_ROOT . "/resource/attachment/" . $bg;
            $ch = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_channel') . " WHERE channel=:channel", array(
                ":channel" => $express['channel']
            ));
            $ch = $this->decode_channel_param($ch, $ch['bgparam']);
            $this->mergeImage($bg_file, $qr_file, $target_file, array(
                'left' => $ch['qrleft'],
                'top' => $ch['qrtop'],
                'width' => $ch['qrwidth'],
                'height' => $ch['qrheight']
            ));
            $enableHead = $ch['avatarenable'];
            $enableName = $ch['nameenable'];
            $myheadimg = pdo_fetch('SELECT avatar FROM ' . tablename('fans') . " WHERE  weid=:weid and from_user = :from_user LIMIT 1", array(
                ':weid' => $weid,
                ':from_user' => $from_user
            ));
            $fans = fans_search($from_user, array(
                'nickname',
                'avatar'
            ));
            if (!empty($fans)) {
                if ($enableName) {
                    if (strlen($fans['nickname']) > 0) {
                        $this->writeText($target_file, $target_file, '我是' . $fans['nickname'], array(
                            'size' => $ch['namesize'],
                            'left' => $ch['nameleft'],
                            'top' => $ch['nametop']
                        ));
                    }
                }
                if ($enableHead) {
                    if (strlen($fans['avatar']) > 10) {
                        $head_file = $myheadimg['avatar'];
                        $bild = $head_cache_file;
                        $url = $this->curl_file_get_contents($head_file);
                        $fp = fopen($bild, 'w');
                        $ws = fwrite($fp, $url);
                        fclose($fp);
                        if ($ws == false || $ws == 0 || empty($ws)) {
                            return '';
                        }
                        $this->mergeImage($target_file, $head_cache_file, $target_file, array(
                            'left' => $ch['avatarleft'],
                            'top' => $ch['avatartop'],
                            'width' => $ch['avatarwidth'],
                            'height' => $ch['avatarheight']
                        ));
                    }
                }
            }
            return BJ_QMXK_BASE . '/tmppic/' . $att_target_file;
        }
        return '';
    }
    public function curl_file_get_contents($durl) {
        $r = null;
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $durl);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $r = curl_exec($ch);
            curl_close($ch);
        }
        return $r;
    }
    public function writeText($bg, $out, $text, $param = array()) {
        list($bgWidth, $bgHeight) = getimagesize($bg);
        extract($param);
        $im = imagecreatefromjpeg($bg);
        $black = imagecolorallocate($im, 0, 0, 0);
        $font = IA_ROOT . BJ_QMXK_BASE . '/font/msyhbd.ttf';
        $white = imagecolorallocate($im, 255, 255, 255);
        imagettftext($im, $size, 0, $left, $top + $size / 2, $white, $font, $text);
        ob_start();
        imagejpeg($im, NULL, 100);
        $contents = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);
        $fh = fopen($out, "w+");
        fwrite($fh, $contents);
        fclose($fh);
    }
    public static function decode_channel_param($item, $p) {
        $gpc = unserialize($p);
        $item['qrleft'] = intval($gpc['qrleft']) ? intval($gpc['qrleft']) : 145;
        $item['qrtop'] = intval($gpc['qrtop']) ? intval($gpc['qrtop']) : 475;
        $item['qrwidth'] = intval($gpc['qrwidth']) ? intval($gpc['qrwidth']) : 240;
        $item['qrheight'] = intval($gpc['qrheight']) ? intval($gpc['qrheight']) : 240;
        $item['avatarleft'] = intval($gpc['avatarleft']) ? intval($gpc['avatarleft']) : 111;
        $item['avatartop'] = intval($gpc['avatartop']) ? intval($gpc['avatartop']) : 10;
        $item['avatarwidth'] = intval($gpc['avatarwidth']) ? intval($gpc['avatarwidth']) : 86;
        $item['avatarheight'] = intval($gpc['avatarheight']) ? intval($gpc['avatarheight']) : 86;
        $item['avatarenable'] = intval($gpc['avatarenable']);
        $item['nameleft'] = intval($gpc['nameleft']) ? intval($gpc['nameleft']) : 211;
        $item['nametop'] = intval($gpc['nametop']) ? intval($gpc['nametop']) : 68;
        $item['namesize'] = intval($gpc['namesize']) ? intval($gpc['namesize']) : 16;
        $item['namecolor'] = $gpc['namecolor'];
        $item['nameenable'] = intval($gpc['nameenable']);
        return $item;
    }
    public function mergeImage($bg, $qr, $out, $param) {
        list($bgWidth, $bgHeight) = getimagesize($bg);
        list($qrWidth, $qrHeight) = getimagesize($qr);
        extract($param);
        $bgImg = $this->imagecreate($bg);
        $qrImg = $this->imagecreate($qr);
        imagecopyresized($bgImg, $qrImg, $left, $top, 0, 0, $width, $height, $qrWidth, $qrHeight);
        ob_start();
        imagejpeg($bgImg, NULL, 100);
        $contents = ob_get_contents();
        ob_end_clean();
        imagedestroy($bgImg);
        imagedestroy($qrImg);
        $fh = fopen($out, "w+");
        fwrite($fh, $contents);
        fclose($fh);
    }
    public function imagecreate($bg) {
        $bgImg = @imagecreatefromjpeg($bg);
        if (FALSE == $bgImg) {
            $bgImg = @imagecreatefrompng($bg);
        }
        if (FALSE == $bgImg) {
            $bgImg = @imagecreatefromgif($bg);
        }
        return $bgImg;
    }
    public function get_weixin_token($account) {
        if (is_array($account['access_token']) && !empty($account['access_token']['token']) && !empty($account['access_token']['expire']) && $account['access_token']['expire'] > TIMESTAMP) {
            return $account['access_token']['token'];
        } else {
            if (empty($account['weid'])) {
                return '';
            }
            $appid = $account['key'];
            $secret = $account['secret'];
            if (empty($appid) || empty($secret)) {
                if (BAIJIA_AGENT_ALL == true) {
                    $appid = BAIJIA_APPID;
                    $secret = BAIJIA_SECRET;
                }
            }
            if (empty($appid) || empty($secret)) {
                return '';
            }
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
            $content = ihttp_get($url);
            if (empty($content)) {
                return '';
            }
            $token = @json_decode($content['content'], true);
            if (empty($token) || !is_array($token)) {
                return '';
            }
            if (empty($token['access_token']) || empty($token['expires_in'])) {
                return '';
            }
            $record = array();
            $record['token'] = $token['access_token'];
            $record['expire'] = TIMESTAMP + $token['expires_in'];
            $row = array();
            $row['access_token'] = iserializer($record);
            pdo_update('wechats', $row, array(
                'weid' => $account['weid']
            ));
            return $record['token'];
        }
    }
}
?>
