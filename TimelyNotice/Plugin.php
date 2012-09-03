<?php
/**
 * 回复评论及时通知
 * 
 * @package TimelyNotice
 * @author WiFeng
 * @version 2.0
 * @link http://521-wf.com
 */
class TimelyNotice_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate() {
		$adapterName = Typecho_Db::get()->getAdapterName();
		if(strtolower($adapterName) != 'mysql') {
			throw new Typecho_Plugin_Exception(_t('目前的版本仅支持Mysql数据库'));
		}
		$tableName = Typecho_Db::get()->getPrefix() . 'timelynotice';
		$sql = "CREATE TABLE `{$tableName}` (
  `noticeId` smallint(3) unsigned NOT NULL auto_increment,
  `commentId` int(10) unsigned NOT NULL default '0',
  `permalink` varchar(200) NOT NULL,
  `replyId` int(10) unsigned NOT NULL default '0',
  `replyContent` text NOT NULL,
  `replyAuthor` varchar(32) NOT NULL,
  `replyDate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`noticeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		try {
			Typecho_Db::get()->query($sql);
		}catch(Exception $e){	
			throw new Typecho_Plugin_Exception(_t('创建数据表“' . $tableName . '”失败！请确保该数据表不存在。'));
		}
		Typecho_Plugin::factory('Widget_Feedback')->finishComment = array(__CLASS__, 'generateRecode');
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'iframeExec');
	    Helper::addAction('timelynotice-send', 'TimelyNotice_Send');
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
		$tableName = Typecho_Db::get()->getPrefix() . 'timelynotice';
		$sql = "DROP TABLE `{$tableName}`";
		Typecho_Db::get()->query($sql);
		Helper::removeAction('timelynotice-send');
	}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){

		$switchOptions = array('0' => '关闭', '1' => '开启');
    	$switchDescription = _t('如果开启，需要认真填写下面各项');
    	$switch = new Typecho_Widget_Helper_Form_Element_Radio('switch', $switchOptions, '1', _t('通知功能'), $switchDescription);

		$smtpServer = new Typecho_Widget_Helper_Form_Element_Text('smtpServer', NULL, '', _t('SMTP地址'));

		$smtpPort = new Typecho_Widget_Helper_Form_Element_Text('smtpPort', NULL, '25', _t('SMTP端口'));
		
		$fromName = new Typecho_Widget_Helper_Form_Element_Text('fromName', NULL, 'No-reply', _t('发送者名称'));

		$fromEmail = new Typecho_Widget_Helper_Form_Element_Text('fromEmail', NULL, '', _t('发送者Email地址'));
		$fromEmail->input->setAttribute('autocomplete', 'off');

		$fromEmailPass = new Typecho_Widget_Helper_Form_Element_Password('fromEmailPass', NULL, '', _t('发送者Email密码'));
		$fromEmailPass->input->setAttribute('autocomplete', 'off');

		$timeout = new Typecho_Widget_Helper_Form_Element_Text('timeout', NULL, '10', _t('发送超时时长'));
		
		$subjectDescription = _t('支持自定义标签：{content_title}(博文标题)、{site_title}(站点标题)}');
		$subject = new Typecho_Widget_Helper_Form_Element_Text('subject', NULL, '【{site_title}】有人对您的评论进行回复了，快去看看哦~~', _t('邮件主题'), $subjectDescription);

		$messageDescription = _t('支持自定义标签：<br />
								{site_title}(站点标题)、{site_url}(站点地址)、{site_description}(站点描述)<br />
								{comment_author}(评论作者)、{comment_content}(评论内容)、{comment_date}(评论时间)<br />
								{reply_author}(回复评论的作者)、{reply_content}(回复内容)、{reply_date}{回复时间}<br />
								{content_title}(博文标题)、{content_detail_url}(博文内容详情地址)
								');
		$message = new Typecho_Widget_Helper_Form_Element_Textarea('message', NULL, '
<STYLE type="text/css">
body, p, a{
	font-family: "Microsoft YaHei","微软雅黑",tahoma,arial,simsun,"宋体";
}
</STYLE>
<p>{comment_author}，您好：</p>
<p>您的评论“{comment_content}”</p> 
<p>{reply_author} 回复“<b>{reply_content}</b>” [时间：{reply_date}]</p>
<p>原文地址：<a href="{content_detail_url}">{content_title}</a></p>
<p style="color:#999999">
如以上链接无法点击，请复制此链接到浏览器地址栏访问：<br />{content_detail_url}
<p/>
<p>以上内容为系统邮件，请勿回复【{site_title}】</p>
', _t('邮件内容'), $messageDescription);

		$form->addInput($switch);
    	$form->addInput($smtpServer);
		$form->addInput($smtpPort);
		$form->addInput($fromName);
    	$form->addInput($fromEmail);
		$form->addInput($fromEmailPass);
		$form->addInput($timeout);
		$form->addInput($subject);
		$form->addInput($message);

	}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
	public static function iframeExec() {
		echo '
<script src="' . Typecho_Common::url('action/timelynotice-send', Helper::options()->index).'"></script>
';
	}
	
	public static function generateRecode($feedbackObj) {
		$commentId = $feedbackObj->parent;
		if($commentId > 0) {
			$replyContent = $feedbackObj->text;
			$replyAuthor = $feedbackObj->author;
			$replyAuthorEmail = $feedbackObj->mail;
			$replyDate = $feedbackObj->created;

			$currentCoid = $feedbackObj->coid;
			$currentParent = $feedbackObj->parent;

			// 以下生成评论访问链接地址
			$feedbackObj->coid = $commentId;
			$feedbackObj->parent = $commentId;
			$permalink = $feedbackObj->permalink;
			
			$feedbackObj->coid = $currentCoid;
			$feedbackObj->parent = $currentParent;

			$insertStruct = array(
				'commentId' => $commentId,
				'permalink' => $permalink,
				'replyId' => $currentCoid,
				'replyContent' => $replyContent,
				'replyAuthor' => $replyAuthor,
				'replyDate' => $replyDate,
				
			);
			$insertId = Typecho_Db::get()->query(Typecho_Db::get()->insert('table.timelynotice')->rows($insertStruct));
		}
		return true;
	}
    
}
