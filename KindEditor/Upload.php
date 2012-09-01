<?php
/**
* @package KindEditor
* @author WiFeng
* @link http://521-wf.com
*/
class KindEditor_Upload extends Widget_Abstract_Contents implements Widget_Interface_Do {
	
	public function execute() {

		if(Typecho_Widget::widget('Widget_User')->pass('contributor', true) && $this->request->isPost()) {
			return true;
		} else {
			$this->alert('登录超时，或者是您的账号在别处已经登录');
			return false;
		}
		
	}
	
	public function action() {
		$attachPath = Helper::options()->plugin('KindEditor')->attachPath;
		$attachPath = !empty($attachPath) ? trim($attachPath, '/') : 'uploads/kind';
		$usrPath = trim(dirname(dirname(dirname(__FILE__))), '\\') . '/';
		$save_path = $usrPath . $attachPath . '/';
		$siteUrl = Helper::options()->siteUrl;
		$charset = Helper::options()->charset;
		$save_url = $siteUrl . 'usr/' . $attachPath .'/';
		
		$ext_arr = array(
				'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
				'flash' => array('swf', 'flv'),
				'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
				'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
		);
		
		
		//有上传文件时
		if (empty($_FILES) === false) {
			if($_FILES['imgFile']['error'] > 0) {
				$msg = $this->getErrorMes($_FILES['imgFile']['error']);
				$this->alert($msg.',请您重新上传');
			}
			//原文件名
			$file_name = $_FILES['imgFile']['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES['imgFile']['tmp_name'];
			//文件大小
			$file_size = $_FILES['imgFile']['size'];
			//检查文件名
			if (!$file_name) {
				$this->alert('请选择文件。');
			}
		
			@mkdir($save_path);
		
			//检查目录写权限
			if (@is_writable($save_path) === false) {
				$this->alert('上传目录没有写权限。');
			}
			//检查是否已上传
			if (@is_uploaded_file($tmp_name) === false) {
				$this->alert('临时文件可能不是上传文件。');
			}

			//检查目录名
			$dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
			if (empty($ext_arr[$dir_name])) {
				$this->alert('目录名不正确。');
			}
			//获得文件扩展名
			$temp_arr = explode('.', $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//检查扩展名
			if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
				$this->alert('上传文件扩展名是不允许的扩展名。\n只允许' . implode(',', $ext_arr[$dir_name]) . '格式。');
			}
			//创建文件夹
			if ($dir_name !== '') {
				$save_path .= $dir_name . '/';
				$save_url .= $dir_name . '/';
				if (!is_dir($save_path)) {
					mkdir($save_path);
				}
			}
			$ym = date('Ym');
			$save_path .= $ym . '/';
			$save_url .= $ym . '/';
			if (!is_dir($save_path)) {
				mkdir($save_path);
			}
			//新文件名
			$new_file_name = date('YmdHis') . '_' . rand(10000, 99999) . '.' . $file_ext;
			//移动文件
			$file_path = $save_path . $new_file_name;
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				$this->alert('上传文件失败。');
			}
			@chmod($file_path, 0644);
			$file_url = $save_url . $new_file_name;
			
			$this->response->throwJson(array('error' => 0, 'url' => $file_url));
		}
	}
	
	private function alert($msg) {
		$this->response->throwJson(array('error' => 1, 'message' => $msg));
	}
	
	private function getErrorMes($errorid) {
		$msg = '';
		switch ($errorid) {
			case '1':
				$msg = '超过php.ini允许的大小。';
				break;
			case '2':
				$msg = '超过表单允许的大小。';
				break;
			case '3':
				$msg = '图片只有部分被上传。';
				break;
			case '4':
				$msg = '请选择图片。';
				break;
			case '6':
				$msg = '找不到临时目录。';
				break;
			case '7':
				$msg = '写文件到硬盘出错。';
				break;
			case '8':
				$msg = 'File upload stopped by extension。';
				break;
			case '999':
			default:
				$msg = '未知错误。';
		}
		return $msg;
	}
}

?>
