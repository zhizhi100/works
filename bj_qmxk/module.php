<?php




defined('IN_IA') or exit('Access Denied');

class bj_qmxkModule extends WeModule {

    public function fieldsFormDisplay($rid = 0) {
        global $_W;
        $setting = $_W['account']['modules'][$this->_saveing_params['mid']]['config'];
        include $this->template('rule');
    }

    public function fieldsFormSubmit($rid = 0) {
        global $_GPC, $_W;
        if (!empty($_GPC['title'])) {
            $data = array(
                'title' => $_GPC['title'],
                'description' => $_GPC['description'],
                'picurl' => $_GPC['thumb-old'],
                'url' => create_url('mobile/module/list', array('name' => 'bj_qmxk', 'weid' => $_W['weid'])),
            );
            if (!empty($_GPC['thumb'])) {
                $data['picurl'] = $_GPC['thumb'];
                file_delete($_GPC['thumb-old']);
            }
            $this->saveSettings($data);
        }
        return true;
    }

    public function settingsDisplay($settings) {
        global $_GPC, $_W;
        if (checksubmit()) {
            $cfg = array(
               'agentRegister' => $_GPC['agentRegister'],
                'noticeemail' => $_GPC['noticeemail'],
                'shopname' => $_GPC['shopname'],
//             	'prefix'=> $_GPC['prefix'],
            		'noagent_title'=>$_GPC['noagent_title'],
            		'agent_title'=>$_GPC['agent_title'],
                'rebacktime'=> $_GPC['rebacktime'],
            		'consumerRebates'=>$_GPC["consumerRebates"],
            		'rebatesScale'=>$_GPC["rebatesScale"],
				'zhifuCommission' => $_GPC['zhifuCommission'],
				'globalCommissionLevel' => $_GPC['globalCommissionLevel'],
                'globalCommission' => $_GPC['globalCommission'],
                'globalCommission2' => $_GPC['globalCommission2'],
                'globalCommission3' => $_GPC['globalCommission3'],
            	'dividend_type' => $_GPC['dividend_type'],
            		'withdrawBalance' => $_GPC['withdrawBalance'],
            		'shoppingBalance' => $_GPC['shoppingBalance'],
            		'immobileBalance' => $_GPC['immobileBalance'],
            		'childrenAccount'=>$_GPC['childrenAccount'],
            		'alias1'=>$_GPC['alias1'],
            		'alias2'=>$_GPC['alias2'],
            		'alias3'=>$_GPC['alias3'],
            		'alias4'=>$_GPC['alias4'],
            		'alias5'=>$_GPC['alias5'],
            	'multi'=>$_GPC["multi"],
//             	'send_voucher'=>$_GPC["send_voucher"],
//             	'voucher_money'=>$_GPC["voucher_money"],
            	'subscribeDividend'=>$_GPC["subscribeDividend"],
            	'subscribeDividendRate'=>$_GPC["subscribeDividendRate"],
				'indexss' => intval($_GPC['indexss']),
				'ydyy' => $_GPC['ydyy'],
				'paymsgTemplateid' => $_GPC['paymsgTemplateid'],
                'address' => $_GPC['address'],
                'phone' => $_GPC['phone'],
               //  'appid' => $_GPC['appid'],
               //     'secret' => $_GPC['secret'],
                'officialweb' => $_GPC['officialweb'],
                'description'=>  htmlspecialchars_decode($_GPC['description']),
                 'footer' => $_GPC['footer'],
                 'footerurl' => $_GPC['footerurl']
            );
            if (!empty($_GPC['logo'])) {
                $cfg['logo'] = $_GPC['logo'];
                file_delete($_GPC['logo-old']);
            }
            
            if ($this->saveSettings($cfg)) {
            	cache_build_modules();
                message('保存成功', 'refresh');
            }
            message('保存成功', 'refresh');
        }
        if(empty($settings['footer']))
        {
        	
        $settings['footer']=$_W['account']['name'];	
        }

        include $this->template('setting');
    }

}
