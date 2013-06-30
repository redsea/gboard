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
		
		
		// 언어를 가져온다. hook 에서 언어 체크 로직에 따라 값을 미리 넣어 두었음.
		//$this->load->get_var('h_lang');
		
		$this->lang->load('default', $this->load->get_var('h_lang'));
		
		$this->load->library('session');
	}
	

	public function index() {
		$member_srl = $this->session->userdata('member_srl');
		$is_root = $this->session->userdata('is_root');
		
		if($member_srl) {
			if($is_root != 'Y') {
				// TODO 안내 페이지로 넘겨야 한다.
				//      안내 페이지에는 로그아웃 버튼을 넣어야 한다.
			
				log_message('debug', '-----> this page connect only root group');
				return;
			}
			
			// admin 메인 페이지를 보여 준다.
			// TODO 로그인 완료 하고 난 다음 보여 주는 것을 완성 해야 한다.
			//      page 는 형태만 있고, 실제 내용은 ajax 로 호출 하여 완성 해야 한다.
			
			$data['title'] = $this->lang->line('page_title_gboard_admin');
			$this->load->view('common/header', $data);
			$this->load->view('admin/main');
			return;
		}
	
		$data['title'] = $this->lang->line('page_title_login');
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