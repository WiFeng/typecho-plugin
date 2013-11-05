<?php

/**
 * @package SecCode
 * @author WiFeng
 * @link http://521-wf.com
 */

class SecCode_Show implements Widget_Interface_Do {
	
	private $_secCodePath = '';

	public function execute() {
		$this->_secCodePath = rtrim(dirname(__FILE__), '\\') . '/';
	}
	
	public function action() {

		require_once 'SecCode/lib/SecCode.php';
		
		$secCode = new SecCode();

		$secCode->code = $this->makeCode();
		$secCode->type = Helper::options()->plugin('SecCode')->type;
		$secCode->width = Helper::options()->plugin('SecCode')->width;
		$secCode->height = Helper::options()->plugin('SecCode')->height;
		$secCode->background = Helper::options()->plugin('SecCode')->background;
		$secCode->adulterate = Helper::options()->plugin('SecCode')->adulterate;
		$secCode->ttf = Helper::options()->plugin('SecCode')->ttf;
		$secCode->angle = Helper::options()->plugin('SecCode')->angle;
		$secCode->warping = Helper::options()->plugin('SecCode')->warping;
		$secCode->scatter = Helper::options()->plugin('SecCode')->scatter;
		$secCode->color = Helper::options()->plugin('SecCode')->color;
		$secCode->size = Helper::options()->plugin('SecCode')->size;
		$secCode->shadow = Helper::options()->plugin('SecCode')->shadow;
		$secCode->animator = Helper::options()->plugin('SecCode')->animator;
		$secCode->fontpath = $this->_secCodePath . './image/font/';
		$secCode->datapath =  $this->_secCodePath . './image/';
		$secCode->includepath = 'SecCode/lib/';
	
		$secCode->display();

	}

	public function makeCode(){
		$seccode = $this->random(6, 1);
		$seccodeunits = '';
		if(Helper::options()->plugin('SecCode')->type == 1) {
			$lang = require 'SecCode/lib/lang.php';

			//$len = strtoupper(CHARSET) == 'GBK' ? 2 : 3;
			$len = strtoupper(Helper::options()->charset) == 'GBK' ? 2 : 3;;
			
			$code = array(substr($seccode, 0, 3), substr($seccode, 3, 3));
			$seccode = '';
			for($i = 0; $i < 2; $i++) {
				$seccode .= substr($lang['chn'], $code[$i] * $len, $len);
			}
		} elseif(Helper::options()->plugin('SecCode')->type == 3) {
			$s = sprintf('%04s', base_convert($seccode, 10, 20));
			$seccodeunits = 'CEFHKLMNOPQRSTUVWXYZ';
		} else {
			$s = sprintf('%04s', base_convert($seccode, 10, 24));
			$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
		}
		if($seccodeunits) {
			$seccode = '';
			for($i = 0; $i < 4; $i++) {
				$unit = ord($s{$i});
				$seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
			}
		}
		$_SESSION['SecCode_code_value'] = $seccode;
		return $seccode;
	}

	public function random($length, $numeric = 0) {
		$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
		$hash = '';
		$max = strlen($seed) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $seed{mt_rand(0, $max)};
		}
		return $hash;
	}

}

?>