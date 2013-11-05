<?php
/**
 * 绚丽的验证码
 * 
 * @package SecCode
 * @author WiFeng
 * @version 1.2.1
 * @link http://521-wf.com
 */

class SecCode_Plugin implements Typecho_Plugin_Interface
{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate() {
		Typecho_Plugin::factory('Widget_Feedback')->comment = array(__CLASS__, 'filter');
		Helper::addAction('seccode-show', 'SecCode_Show');	
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){
		Helper::removeAction('seccode-show');
	
	}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form) {
		$typeOptions = array(
			'0' => '英文图片验证码', 
			'1' => '中文图片验证码', 
			'2' => 'Flash 验证码', 
			//'3' => '语音验证码', 
			'4' => '位图验证码');
    	$typeDescription = _t('设置验证码的类型。<br />中、英文图片验证码需要 gd 库支持<br />中文图片验证码需要你的主机支持 FreeType 库。<br />要显示 Flash 验证码，建议你的主机支持 Ming 库以提高安全性');
    	$type = new Typecho_Widget_Helper_Form_Element_Radio('type', $typeOptions, '0', _t('验证码类型'), $typeDescription);

		$widthDescription = _t('验证码图片的宽度，范围在 100～200 之间');
		$width = new Typecho_Widget_Helper_Form_Element_Text('width', NULL, '150', _t('验证码图片宽度'), $widthDescription);

		$heightDescription = _t('验证码图片的高度，范围在 30～80 之间');
		$height = new Typecho_Widget_Helper_Form_Element_Text('height', NULL, '40', _t('验证码图片高度'), $heightDescription);
		
		$scatterDescription = _t('打散生成的验证码图片，输入打散的级别，0 为不打散');
		$scatter = new Typecho_Widget_Helper_Form_Element_Text('scatter', NULL, '0', _t('图片打散'), $scatterDescription);

		$backgroundOptions = array('0' => '否', '1' => '是');
		$backgroundDescription = _t('选择“是”将随机使用 /usr/plugins/SecCode/image/background/ 目录下的 JPG 图片作为验证码的背景图片，<br />选择“否”将使用随机的背景色');
		$background = new Typecho_Widget_Helper_Form_Element_Radio('background', $backgroundOptions, '0', _t('随机图片背景'), $backgroundDescription);
		
		$adulterateOptions = array('0' => '否', '1' => '是');
		$adulterateDescription = _t('选择“是”将给验证码背景增加随机的图形');
		$adulterate = new Typecho_Widget_Helper_Form_Element_Radio('adulterate', $adulterateOptions, '0', _t('随机背景图形'), $adulterateDescription);
		
		$ttfOptions = array('0' => '否', '1' => '是');
		$ttfDescription = _t('选择“是”将随机使用 /usr/plugins/SecCode/image/font/en/ 目录下的 TTF 字体文件生成验证码文字，<br />选择“否”将随机使用 /usr/plugins/SecCode/image/gif/ 目录中的GIF图片生成验证码文字。<br />中文图片验证码将随机使用 /usr/plugins/SecCode/image/font/ch/ 目录下的 TTF 字体文件，无需进行此设置');
		$ttf = new Typecho_Widget_Helper_Form_Element_Radio('ttf', $ttfOptions, '0', _t('随机 TTF 字体'), $ttfDescription);
		
		$angleOptions = array('0' => '否', '1' => '是');
		$angleDescription = _t('选择“是”将给验证码文字增加随机的倾斜度，本设置只针对 TTF 字体的验证码');
		$angle = new Typecho_Widget_Helper_Form_Element_Radio('angle', $angleOptions, '0', _t('随机倾斜度'), $angleDescription);

		$warpingOptions = array('0' => '否', '1' => '是');
		$warpingDescription = _t('选择“是”将给验证码文字增加随机的扭曲，本设置只针对 TTF 字体的验证码');
		$warping = new Typecho_Widget_Helper_Form_Element_Radio('warping', $warpingOptions, '0', _t('随机扭曲'), $warpingDescription);
		
		$colorOptions = array('0' => '否', '1' => '是');
		$colorDescription = _t('选择“是”将给验证码的背景图形和文字增加随机的颜色');
		$color = new Typecho_Widget_Helper_Form_Element_Radio('color', $colorOptions, '0', _t('随机颜色'), $colorDescription);

		$sizeOptions = array('0' => '否', '1' => '是');
		$sizeDescription = _t('选择“是”验证码文字的大小随机显示');
		$size = new Typecho_Widget_Helper_Form_Element_Radio('size', $sizeOptions, '0', _t('随机大小'), $sizeDescription);
		
		$shadowOptions = array('0' => '否', '1' => '是');
		$shadowDescription = _t('选择“是”将给验证码文字增加阴影');
		$shadow = new Typecho_Widget_Helper_Form_Element_Radio('shadow', $shadowOptions, '0', _t('文字阴影'), $shadowDescription); 
		
		$animatorOptions = array('0' => '否', '1' => '是');
		$animatorDescription = _t('选择“是”验证码将显示成 GIF 动画方式，选择“否”验证码将显示成静态图片方式');
		$animator = new Typecho_Widget_Helper_Form_Element_Radio('animator', $animatorOptions, '0', _t('GIF 动画'), $animatorDescription);

		$form->addInput($type);
		$form->addInput($width);
		$form->addInput($height);
		$form->addInput($scatter);
		$form->addInput($background);
		$form->addInput($adulterate);
		$form->addInput($ttf);
		$form->addInput($angle);
		$form->addInput($warping);
		$form->addInput($color);
		$form->addInput($size);
		$form->addInput($shadow);
		$form->addInput($animator);

		/**
		* 对设置选项自动隐藏、显示切换
		*/
		$script = new Typecho_Widget_Helper_Layout('script' , array('type' => 'text/javascript'));
		$script_html = <<<EOF
function seccode_load() {
    var typeObj = $$("input[name=type]");
	typeObj.addEvent("click", function() {
		var i = 0;
		var form = $$(this).getParent("form");
		var options_ul = form.getChildren("ul")[0];
		var options_ul_size = options_ul.length;
		if(this.value == 2 || this.value == 4) {	
			i = 0;
			for(i in options_ul) {
				if(i == 0 || i >= options_ul_size -1 || (this.value == 2 && i < 3) || (this.value == 4 && i < 1)) {
					$$(options_ul[i]).setStyle("display", "block");
					continue;
				}
				$$(options_ul[i]).setStyle("display", "none");
			}

		} else {
			for(i in options_ul) {
				$$(options_ul[i]).setStyle("display", "block");
			}
		}
	});
	$$("input[name=type]:checked")[0].click();
}

if(document.all) {
	window.attachEvent('onload', seccode_load);
} else {
	window.addEventListener('load', seccode_load);
}

EOF;
		$script->html($script_html);
		$form->addItem($script);
	}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}
    
	/**
	 * 展示验证码
	 */
	public static function output() {
		$secType = Helper::options()->plugin('SecCode')->type;
		$secWidth =  Helper::options()->plugin('SecCode')->width; 
		$secHeight =  Helper::options()->plugin('SecCode')->height;
		
		$secWidth = intval($secWidth);
		$secHeight = intval($secHeight);
		
		switch ($secType) {
			case '2' :
				$html = '<embed width="' . $secWidth . '"  height="' .$secHeight. '" src="' . Typecho_Common::url('action/seccode-show?r=1', Helper::options()->index) .'" quality="high" wmode="transparent" bgcolor="#ffffff" align="middle" menu="false" allowscriptaccess="sameDomain" type="application/x-shockwave-flash">';
				break;
			case '3' :
				$html = '';
				break;
			case '4' :
				$secWidth = 32;
				$secHeight = 24;
			default :
				$html = '<img  src="' . Typecho_Common::url('action/seccode-show?r=1', Helper::options()->index) .'" style="cursor:pointer;vertical-align:middle" title="' . _t('点击刷新验证码') . '" alt="' . _t('点击刷新验证码') . '"  onclick="this.src=this.src+1" />';

		}
			
		echo '<input type="text" name="seccode" id="seccode" class="text" size="10" value="" />&nbsp;&nbsp;&nbsp;' . $html;	
	}

	public static function filter($comment, $obj) {
		$seccode_session = isset($_SESSION['SecCode_code_value']) ? trim(strtoupper($_SESSION['SecCode_code_value'])) : '';
		$seccode_request = strtoupper(Typecho_Widget::widget('Widget_Feedback')->request->seccode);
		$userObj = $obj->widget('Widget_User');
		if($userObj->hasLogin() && $userObj->pass('administrator', true)) {
			return $comment;
		} elseif(empty($seccode_session) || $seccode_session != $seccode_request) {
			throw new Typecho_Widget_Exception(_t('验证码不正确哦'));
		}
		unset($_SESSION['SecCode_code_value']);
		return $comment;
	}
}
