<?php
/**
 * Service admin Controller
 *
 * @author	dhkim94@gmail.com
 */
class Admin extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		//$this->config->load('error_code/member', TRUE);
		//$this->load->library('myutil');
		
		// $this->load->get_var('h_lang'); 언어 설정값 가져오기
		
		log_message('debug', '-----> create Admin controller. u_lang['.$this->load->get_var('h_lang').']');
		
	}
	

	public function index() {
	
		//$this->load->library('user_agent');
		//log_message('debug', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		log_message('debug', $this->input->server('HTTP_ACCEPT_LANGUAGE'));
	
		$data['title'] = 'Login';
		$this->load->view('common/header', $data);
		$this->load->view('admin/login');
	
	
		/*
		$this->load->model('admin/admin_model', 'model');
		
		if($this->model->isMemberExist() > 0) {
			
		} else {
			// sites 생성 할 수 있는 폼을 보여 준다.
			$data['title'] = 'Create Site';
			$this->load->view('common/header', $data);
			$this->load->view('admin/create_site');
		}
		*/
	}

}
?>