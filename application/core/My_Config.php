<?php
/**
 * MY_Config Core override(CI_Config)
 *
 * CI_Input 을 override 하였음.
 * 1. 이미지 서버 도메인을 구하기 위해서 uri helper 에서 image_url 함수를 생성하기 위해서 image_url function 추가
 *
 * @author	dhkim94@gmail.com
 */
class MY_Config extends CI_Config {
	function __construct() {
        parent::__construct();
    }
    
    /**
	 * URL with image server
	 * Returns image_url [. uri_string]
	 *
	 * @access public
	 * @param string $uri
	 * @return string
	 */
	function image_url($uri = '') {
		return $this->slash_item('my_image_server_url').ltrim($this->_uri_string($uri), '/');
	}
	
	/**
	 * 시스템에서 기본으로 생성한 일반 유저의 member_srl 을 구한다.
	 */
	function system_member_srl() {
		return $this->item('system_default_member_srl');
	}
}
?>