<?php
/**
 * Language Controller
 *
 * member/member 는 route 를 통해서 member/[function] 으로 라우팅 된다.
 *
 * @author	dhkim94@gmail.com
 */
class Mlanguage extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		$this->config->load('my_conf/common', TRUE);
		
		$this->config->load('error_code/common', TRUE);
		//$this->config->load('error_code/member', TRUE);
		
		//$this->load->library('myutil');
		
		//$this->load->model('common/common_model', 'cmodel');
		//$this->load->model('member/member_model', 'model');
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	/**
	 * 지원하는 언어 리스트를 구한다.
	 */
	public function index() {
		echo "mlanguage index";
	}
}
?>