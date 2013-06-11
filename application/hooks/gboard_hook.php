<?php
/**
 * gamma board hooking class
 *
 * request 시 가장 먼저 항싱 실행 해야 할 것들 정의
 *
 * @author	dhkim94@gmail.com
 */
class Gboard_hook {
	public function __construct() {
		$this->CI = &get_instance();
	}
	
	/**
	 * request content type 을 체크 한다.
	 */
	function hook_request_content_type() {
		echo "hook start<br/>";
		
		$this->CI->load->library('session');
	
		$headers = getallheaders();
		if(array_key_exists('Content-Type', $headers)) {
			echo "exist content-type<br/>";
			
			
			
			
		} else {
			echo "not exist content-type<br/>";
			
		}
		
		//$this->CI->session->set_userdata(array('request_type', 'json'));
		
		$this->CI->session->set_flashdata('item', 'value111');
		
		//$newdata = array(
          //         'type'  => 'json'
            //   );

//	    $this->CI->session->set_userdata($newdata);
		
		
	
		//$content_type = $this->CI->input->get_request_header();
		
		//echo json_encode($content_type)."<br/>";
		
		
		
		echo "hook end<br/>";
		
		
		/*
		if($this->CI) {
			echo "exist ci<br/>";
		} else {
			echo "not exist ci<br/>";
			echo gettype($this->CI);
		}
	
		$header = getallheaders();
		if(array_key_exists('Content-Type', $header)) {
			echo "exist content-type<br/>";
		} else {
			echo "not exist content-type<br/>";
			
		}
		*/
	}
}
?>