<?php
/**
 * KindEditor编辑器
 * 
 * @package KindEditor
 * @author WiFeng
 * @version 2.1
 * @link http://521-wf.com
 */
class KindEditor_Plugin implements Typecho_Plugin_Interface
{
	public static $version = '2.0';
	private static $_pluginRootUrl = '';
	private static $_pluginName = 'KindEditor';
	private static $_init = false;

	private static function _init() {
		if(!self::$_init) {
			self::$_pluginName = substr(__CLASS__, 0, strrpos(__CLASS__, '_'));
			self::$_pluginRootUrl = Typecho_Widget::widget('Widget_Options')->pluginUrl.'/'.self::$_pluginName;
			self::$_init = true;
		}		
	}

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
		Typecho_Plugin::factory('admin/write-post.php')->richEditor = array(__CLASS__, 'showEditor');
		Typecho_Plugin::factory('admin/write-page.php')->richEditor = array(__CLASS__, 'showEditor');
		Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'prettyCode');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->content = array(__CLASS__, 'render');
        Helper::addAction('plugins-kind-upload', 'KindEditor_Upload');
		Helper::addAction('plugins-kind-filemanager', 'KindEditor_FileManager');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){
    	$editorThemeOptions = array(
    				'default' => '默认分格',
    				'simple' => '简洁分格',
    			);
    	$editorTheme = new Typecho_Widget_Helper_Form_Element_Select('editorTheme', $editorThemeOptions, 'default', _t('皮肤'));

		$editorLangOptions = array(
    				'zh_CN' => '中文简体',
    				'zh_TW' => '中文繁体',
    				'en'	=> '英语',
    				'ar'	=> '阿拉伯语'
    			);
    	$editorLang = new Typecho_Widget_Helper_Form_Element_Select('editorLang', $editorLangOptions, 'zh_CN', '语言');
    	
    	$editorWidth = new Typecho_Widget_Helper_Form_Element_Text('editorWidth', NULL, '700px', _t('宽度'));
    	
    	$editorHeight = new Typecho_Widget_Helper_Form_Element_Text('editorHeight', NULL, '500px', _t('高度'));
    	
    	$editorUploadFlagOptions = array('1' => '开启' ,'0' => '关闭');
    	$editorUploadFlagDescription = _t('附件指图片、文件、视频、Flash文件等');
    	$editorUploadFlag = new Typecho_Widget_Helper_Form_Element_Radio('editorUploadFlag', $editorUploadFlagOptions, '0', _t('附件上传'), $editorUploadFlagDescription);
    	
    	$attachPathDescription = _t('请确保此目录具有可写权限，如果此目录不存在，请确保 usr目录具有可写权限哦，不然上传会提示失败');
    	$attachPath = new Typecho_Widget_Helper_Form_Element_Text('attachPath', NULL, 'uploads/kind', _t('附件目录'), $attachPathDescription);
    	
    	$isShowPrettyOptions = array(
    				'0' => '关闭',
    				'1'	=> '自己在模板中添加',
    				'2'	=> '默认分格',
    			);
    	$isShowPretty = new Typecho_Widget_Helper_Form_Element_Radio('isShowPretty', $isShowPrettyOptions, '2', _t('渲染代码'));
    	
    	$line = new Typecho_Widget_Helper_Layout('hr');
    	
    	$editorNewlineTagOptions = array(
    			'br' => '新开始行',
    			'p' => '新开始段落',
    	);
    	$editorNewlineTag = new Typecho_Widget_Helper_Form_Element_Radio('editorNewlineTag', $editorNewlineTagOptions, 'p', _t('回车处理'));
    	
    	$editorPasteTypeOptions = array(
    			'0' => '禁止',
    			'1' => '纯文本',
    			'2' => 'HTML',
    	);
    	$editorPasteType = new Typecho_Widget_Helper_Form_Element_Radio('editorPasteType', $editorPasteTypeOptions, '2', _t('粘贴类型'));
    	
    	$form->addInput($editorTheme);
		$form->addInput($editorLang);
    	$form->addInput($editorWidth);
    	$form->addInput($editorHeight);
    	$form->addInput($editorUploadFlag);
    	$form->addInput($attachPath);
    	$form->addInput($isShowPretty);
    	$form->addItem($line);
    	$form->addInput($editorNewlineTag);
    	$form->addInput($editorPasteType);
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
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function render($content) {
		return $content;
	}

	public static function url($filepath, $versionParam = true) {
		self::_init();
		$filepath = trim($filepath, '/');
		$queryString = $versionParam ? ('?v=' . self::$version) : '';
		return Typecho_Common::url($filepath . $queryString, self::$_pluginRootUrl);
	}
	
	public static function initEditor() {
		self::_init();
	}

	public static function showEditor($post) {
		self::initEditor();
		$editorTheme = Typecho_Widget::widget('Widget_Options')->plugin(self::$_pluginName)->editorTheme;
		$editorWidth = Typecho_Widget::widget('Widget_Options')->plugin(self::$_pluginName)->editorWidth;
		$editorHeight = Typecho_Widget::widget('Widget_Options')->plugin(self::$_pluginName)->editorHeight;
		$editorLang = Typecho_Widget::widget('Widget_Options')->plugin(self::$_pluginName)->editorLang;
		if(!$editorLang) {
			$editorLang = 'zh_CN';
		}
		
		$editor_default_css_url = self::url("editor/themes/default/default.css");
		$editor_css_url = self::url("editor/themes/$editorTheme/$editorTheme.css");
		$kindeditor_js_url = self::url('editor/kindeditor-min.js');
		$lang_js_url = self::url('editor/lang/'.$editorLang.'.js');
		$plugin_jsres_url = self::url('editor/i/plugin.js');
		$plugin_cssres_url = self::url('editor/i/plugin.css');
		
		$editorUploadFlag = Typecho_Widget::widget('Widget_Options')->plugin(self::$_pluginName)->editorUploadFlag;
		$editorUploadFlag = $editorUploadFlag ? 'true' : 'false';
		$editorUploadJson = Typecho_Common::url('action/plugins-kind-upload', Typecho_Widget::widget('Widget_Options')->index);
		
		$editorNewlineTag = Typecho_Widget::widget('Widget_Options')->plugin(self::$_pluginName)->editorNewlineTag;
		$editorPasteType = Typecho_Widget::widget('Widget_Options')->plugin(self::$_pluginName)->editorPasteType;
		if(!in_array($editorPasteType, array('1', '2', '0'))) {
			$editorPasteType = 2;
		}

		$fileManagerJson = Typecho_Common::url('action/plugins-kind-filemanager', Typecho_Widget::widget('Widget_Options')->index);
		
		$autoSave = Typecho_Widget::widget('Widget_Options')->autoSave ? true : false;
		$autoSaveLeaveMessage = _t('您的内容尚未保存, 是否离开此页面?');
		$resizeUrl = Typecho_Common::url('action/ajax', Typecho_Widget::widget('Widget_Options')->index);

		echo <<<EOF
<link rel="stylesheet" href="{$editor_default_css_url}" />
<link rel="stylesheet" href="{$editor_css_url}" />
<script type="text/javascript" src="{$kindeditor_js_url}"></script>
<script type="text/javascript" src="{$lang_js_url}"></script>

<link rel="stylesheet" href="{$plugin_cssres_url}" />
<script type="text/javascript" src="{$plugin_jsres_url}"></script>

<script type="text/javascript">
var keditor;
var KK = KindEditor;
KindEditor.ready(function(K) {
        keditor = K.create("textarea#text", {
        	themeType : '{$editorTheme}',
        	width : '{$editorWidth}',
        	height : '{$editorHeight}',
        	langType : '{$editorLang}',
        	allowImageUpload : {$editorUploadFlag},
        	allowFlashUpload : {$editorUploadFlag},
        	allowMediaUpload : {$editorUploadFlag},
        	allowFileManager : true,
			fileManagerJson : '{$fileManagerJson}',
        	uploadJson : '{$editorUploadJson}',
        	newlineTag : '{$editorNewlineTag}',
        	pasteType : {$editorPasteType},
			items : ['source', '|', 'undo', 'redo', '|', 'preview', 'template', 'cut', 'copy', 'paste',
        'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
        'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
        'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'emoticons', 'code', 'fullscreen', '/',
        'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
        'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image',
        'flash', 'media', 'insertfile', 'table', 'hr', 'baidumap', 'pagebreak',
        'anchor', 'link', 'unlink', '|', 'i_br', 'i_more']
        });
		
});


$('btn-save').removeEvent('click');
$('btn-submit').removeEvent('click');

$('btn-save').addEvent('click', function (e) {
	keditor.sync();
	this.getParent('span').addClass('loading');
	this.setProperty('disabled', true);
	$(document).getElement('input[name=do]').set('value', 'save');
	$(document).getElement('.typecho-post-area form').submit();
});

$('btn-submit').addEvent('click', function (e) {
	keditor.sync();
	this.getParent('span').addClass('loading');
	this.setProperty('disabled', true);
	$(document).getElement('input[name=do]').set('value', 'publish');
	$(document).getElement('.typecho-post-area form').submit();
});

 /** 这两个函数在插件中必须实现 */
var insertImageToEditor = function (title, url, link, cid) {
	var html = '<a href="' + link + '" title="' + title + '"><img src="' + url + '" alt="' + title + '" /></a>';
	keditor.insertHtml(html).hideDialog().focus();
	new Fx.Scroll(window).toElement($(document).getElement('textarea#text'));
};

var insertLinkToEditor = function (title, url, link, cid) {
	var html = '<a href="' + url + '" title="' + title + '">' + title + '</a>';
	keditor.insertHtml(html).hideDialog().focus();
	new Fx.Scroll(window).toElement($(document).getElement('textarea#text'));
};
</script>

EOF;
		/**兼容原有自动保存功能**/
		if($autoSave) {
			echo <<<EOF
<script type="text/javascript">
	var autoSave = new Typecho.autoSave($("text").getParent("form").getProperty("action"), {
                time: 20,
                getContentHandle: function() {return $("text").get("value")},
                messageElement: 'auto-save-message',
                leaveMessage: '{$autoSaveLeaveMessage}',
                form: $("text").getParent("form")
            })
	setInterval(function() {
		keditor.sync();
		if(KK.trim(KK("textarea#text").val()) != "") {
			autoSave.onContentChange();
		}
	}, 10000);
</script>

EOF;
		}
	}

	public static function prettyCode() {
		$isShowPretty = Typecho_Widget::widget('Widget_Options')->plugin(self::$_pluginName)->isShowPretty;
		if($isShowPretty > 0) {	
		$pretty_js_url = self::url('editor/plugins/code/prettify.js');
		$pretty_css_url = self::url('editor/plugins/code/prettify.css');
			if($isShowPretty == 1) {
				echo <<<EOF
<script charset="utf-8" src="{$pretty_js_url}"></script>
<script type="text/javascript">
if(document.all) {
	window.attachEvent('onload', function(){
	prettyPrint();
});
} else {
	window.addEventListener('load', function(){
	prettyPrint();
});  
}
</script>
EOF;
			} else {
			echo <<<EOF
<link rel="stylesheet" href="{$pretty_css_url}" />
<script charset="utf-8" src="{$pretty_js_url}"></script>
<script type="text/javascript">
if(document.all) {
	window.attachEvent('onload', function(){
	prettyPrint();
});
} else {
	window.addEventListener('load', function(){
	prettyPrint();
});  
}
</script>
EOF;
			}
		}
		return true;
	}
}
