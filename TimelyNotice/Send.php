<?php
/**
 * @package TimelyNotice
 * @author WiFeng
 * @link http://521-wf.com
 */
class TimelyNotice_Send implements Widget_Interface_Do{
	
	public $db;

	public function execute() {
		$this->db = Typecho_Db::get();
	}

	public function action() {
		$switch = Helper::options()->plugin('TimelyNotice')->switch;
		if($switch) {
			$noticeInfo = $this->db->fetchRow($this->db->select('*')->from('table.timelynotice'));
			if(is_array($noticeInfo) && !empty($noticeInfo)) {
				$comment = $this->db->fetchRow($this->db->select('*')->where('coid = ?', $noticeInfo['commentId'])->from('table.comments'));
				$content = $this->db->fetchRow($this->db->select('cid','title')->where('cid= ?', $comment['cid'])->from('table.contents'));
				$relationData = array(
						'comment_author' 		=> $comment['author'],
						'comment_content'		=> $comment['text'],
						'comment_date'			=> $comment['created'],
						'reply_author'			=> $noticeInfo['replyAuthor'],
						'reply_content'			=> $noticeInfo['replyContent'],
						'reply_date'			=> $noticeInfo['replyDate'],
						'content_title'			=> $content['title'],
						'content_detail_url'	=> $noticeInfo['permalink']
						);
				
				$mail = $comment['mail'];
				$author = $comment['author'];
				$subject = $this->createSubject($relationData);
				$message = $this->createMessage($relationData);
				
				$objEmail = new TimelyNotice_Email();
				$objEmail->smtpServer = Helper::options()->plugin('TimelyNotice')->smtpServer;
				$objEmail->smtpPort = Helper::options()->plugin('TimelyNotice')->smtpPort;
				$objEmail->timeout = Helper::options()->plugin('TimelyNotice')->timeout;
				$objEmail->fromEmail = Helper::options()->plugin('TimelyNotice')->fromEmail;
				$objEmail->fromName = Helper::options()->plugin('TimelyNotice')->fromName;
				$objEmail->fromEmailPass = Helper::options()->plugin('TimelyNotice')->fromEmailPass;
				
				$logs = $objEmail->send($mail, $author, $subject, $message);
				if($logs['quitcode'] == 221) {
					$this->db->query($this->db->delete('table.timelynotice')->where('noticeId = ?', $noticeInfo['noticeId']));
				} else {
					//var_dump($logs);
				}
			}
		} else {
			$this->db->query($this->db->delete('table.timelynotice'));
		}
		
	}

	private function createSubject($data = array()) {
		$subject = Helper::options()->plugin('TimelyNotice')->subject;

		$content_title = $site_title = '';
		extract($data);
		
		$site_title = Helper::options()->title;

		$search = array(
			'{content_title}',	
			'{site_title}'
		);

		$replace = array(
			strip_tags($content_title),
			strip_tags($site_title)		
		);

		return str_replace($search, $replace, $subject);
	}

	private function createMessage($data = array()) {
		$message = Helper::options()->plugin('TimelyNotice')->message;

		$site_title = '';
		$site_url = '';
		$site_description = '';
		$comment_author = '';
		$comment_content = '';
		$comment_date = '';
		$reply_author = '';
		$reply_content = '';
		$reply_date = '';
		$content_title = '';
		$content_detail_url = '';
		
		extract($data);
		
		$site_title = Helper::options()->title;
		$site_url = Helper::options()->siteUrl;
		$site_description = Helper::options()->description;
		
		$comment_date_obj = new Typecho_Date($comment_date);
		$comment_date = $comment_date_obj->format('Y-m-d');
		$reply_date_obj  = new Typecho_Date($reply_date);
		$reply_date = $reply_date_obj->format('Y-m-d');
		
		$comment_content = preg_replace('/<a(.*?)>(.+?)<\/a>/', ' \2 ' ,$comment_content);
		$reply_content = preg_replace('/<a(.*?)>(.+?)<\/a>/', ' \2 ' ,$reply_content);

		$search = array(
			'{site_title}',
			'{site_url}',
			'{site_description}',
			'{comment_author}',
			'{comment_content}',
			'{comment_date}',
			'{reply_author}',
			'{reply_content}',
			'{reply_date}',
			'{content_title}',
			'{content_detail_url}'
		);

		$replace = array(
			strip_tags($site_title),
			$site_url,
			strip_tags($site_description),
			strip_tags($comment_author),
			strip_tags($comment_content),
			$comment_date,
			strip_tags($reply_author),
			strip_tags($reply_content),
			$reply_date,
			strip_tags($content_title),
			$content_detail_url	
		);

		return str_replace($search, $replace, $message);
	}
}
?>