<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * url_helper 확장
 * 1. image_url 함수를 추가(이미지 서버가 따로 있을때 이미지 서버의 도메인을 포함하는 url 생성)
 */
 
/**
 * Image URL
 * 
 * Create a image server URL based on your image path.
 *
 * @access	public
 * @param string
 * @return	string
 */
if(!function_exists('image_url')) {
	function image_url($uri = '') {
		$CI =& get_instance();
		return 'http://'.$CI->config->image_url($uri);
	}
}
 
/* End of file My_url_helper.php */
/* Location: ./application/helpers/My_url_helper.php */