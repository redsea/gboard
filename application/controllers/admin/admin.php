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
		
		$this->config->load('error_code/common', TRUE);
		
		$this->lang->load('default', $this->load->get_var('h_lang'));
		
		$this->load->model('common/common_model', 'cmodel');
		$this->load->library('session');
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	private function showLoginPage() {
		$data['title'] = $this->lang->line('page_title_login');
		$this->load->view('common/header', $data);
		$this->load->view('admin/login');
	}

	public function index() {
		$member_srl = $this->session->userdata('member_srl');
		$is_root = $this->session->userdata('is_root');
		$access_token = $this->session->userdata('access_token');
		
		
		log_message('debug', '----->member_srl['.$member_srl.']');
		log_message('debug', '----->is_root['.$is_root.']');
		log_message('debug', '----->access_token['.$access_token.']');
		
		
		if($member_srl) {
			if($is_root != 'Y') {
				// TODO 안내 페이지로 넘겨야 한다.
				//      안내 페이지에는 로그아웃 버튼을 넣어야 한다.
			
				log_message('debug', '-----> this page connect only root group');
				return;
			}
			
			$result = $this->cmodel->validAuthorization($access_token, TRUE);
			if($result != $this->success_code) {
				log_message('debug', "Admin index unauthorized access_token[$access_token] goto login");
				$this->showLoginPage();
				return;
			}
				
				
			// admin 메인 페이지를 보여 준다.
			// TODO 로그인 완료 하고 난 다음 보여 주는 것을 완성 해야 한다.
			//      page 는 형태만 있고, 실제 내용은 ajax 로 호출 하여 완성 해야 한다.
				
			// 이 페이지는 redirect 로 넘어 오기 때문에 authorization check 를 하지 않는다.
			//$result = $this->cmodel->validAuthorization();
			
			$session_expire_sec = $this->config->item('sess_expiration');
			$auth_expire_sec = strtotime($this->session->userdata('access_token_expire')) - time();
				
			log_message('debug', '----->session exipre sec['.$session_expire_sec.']');
			log_message('debug', '----->auth exipre sec['.$auth_expire_sec.']');
				
				
			$data['title'] = $this->lang->line('page_title_gboard_admin');
			// session 만료 기간, access_token 만료 기간 중 작은 값을 만료 기간으로 설정한다.
			$data['session_expire_time'] = $session_expire_sec >= $auth_expire_sec ? 
					$auth_expire_sec : $session_expire_sec;
			$data['home_url'] = 'http://'.$this->session->userdata('site_domain').'/admin';
						
			$this->load->view('common/header', $data);
			$this->load->view('admin/main', $data);
			return;
		}
	
		$this->showLoginPage();
	}

}
?>