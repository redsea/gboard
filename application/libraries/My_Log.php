<?php
/**
 * MY_Log Core override(CI_Log)
 *
 * CI_Input 을 override 하였음.
 * 1. 이미지 서버 도메인을 구하기 위해서 uri helper 에서 image_url 함수를 생성하기 위해서 image_url function 추가
 *
 * @author	dhkim94@gmail.com
 */
class MY_Log extends CI_Log {
	function __construct() {
        parent::__construct();
        $this->_log4php = BGLog::sharedInstance();
    }

	public function write_log($level = 'error', $msg, $php_error = FALSE) {
		if ($this->_enabled === FALSE) {
			return FALSE;
		}

		$level = strtolower($level);
		switch($this->_threshold) {
			case 0:	return FALSE;
			case 1:
				if($level=='debug' || $level=='info' || $level=='warn') { return FALSE; }
				break;
			case 2:
				if($level=='debug' || $level=='info') { return FALSE; }
				break;
			case 3:
				if($level=='debug') { return FALSE; }
				break;
			default:
				break;
		}
		$this->_log4php->{$level}($msg);
	}
	
	public function set_log_key($log_key) {
		if($log_key) { $this->_log4php = BGLog::sharedInstance($log_key); }
	}
}
?>