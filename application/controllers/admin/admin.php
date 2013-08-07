<?php
/**
 * Controller for admin
 *
 * @author	dhkim94@gmail.com
 */
class Admin extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		//$this->config->load('error_code/member', TRUE);
		$this->load->library('myutil');
		
		
		
		// 언어를 가져온다. hook 에서 언어 체크 로직에 따라 값을 미리 넣어 두었음.
		//$this->load->get_var('h_lang');
				
		$this->lang->load('default', $this->load->get_var('h_lang'));
		
		$this->load->model('common/common_model', 'cmodel');
		$this->load->model('service/service_model', 'service_model');
		$this->load->model('mlanguage/mlanguage_model', 'lang_model');
		//$this->load->model('admin/admin_model', 'model');
		
		$this->config->load('error_code/common', TRUE);
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	/**
	 * 로그인 페이지를 보여준다.
	 */
	private function showLoginPage() {
		$data['title'] = $this->lang->line('page_title_login');
		$this->load->view('common/header', $data);
		$this->load->view('admin/login');
	}
	
	/**
	 * 접속자가 root 권한 인지 체크 하고, authorization 을 체크 한다.
	 *
	 * @param action {string} action name
	 * @param bench_mark_start {string} bench mark start string
	 * @param bench_mark_end {string} bench mark end string
	 * @return {boolean} root 권한이고, authorization 체크 성공하면 TRUE, 아니면 FALSE 를 리턴한다.
	 */
	private function isValidAuthorization($action, $bench_mark_start, $bench_mark_end) {
		$member_srl = $this->session->userdata('member_srl');
		
		$group_info = $this->session->userdata('group');
		$is_root = FALSE;
		foreach($group_info as $row) {
			if($row['is_root'] == 'Y') {
				$is_root = TRUE;
				break;
			}
		}
		
		// 로그인 하지 않았거나, root group 이 아닌 경우는 사용 못 하도록 한다.
		if(!$member_srl || !$is_root) {
			$data['notice_text'] = $this->cmodel->getTextByLanguage('__usrLang10');
			$this->load->view('common/no_authorization', $data);
			
			$this->benchmark->mark($bench_mark_end);
			log_message('info', $action.' T['.$this->benchmark->elapsed_time($bench_mark_start, $bench_mark_end).']');
			return FALSE;
		}
		
		// access_token 인증을 확인 한다.
		$result = $this->cmodel->validAuthorization(FALSE, TRUE);
		if($result != $this->success_code) {
			$data['notice_text'] = $this->cmodel->getTextByLanguage('__usrLang9');
			$this->load->view('common/no_authorization', $data);
			
			$this->benchmark->mark($bench_mark_end);
			log_message('info', $action.' T['.$this->benchmark->elapsed_time($bench_mark_start, $bench_mark_end).']');
			return FALSE;
		}
		
		return TRUE;
	}


	//--------------------------------------------------------
	// 1. HTML 으로 결과가 나오는 function
	//--------------------------------------------------------
	/**
	 * admin 웹페이지 기본 controller. 웹브라우저로 붙을때 여기로 붙는다.
	 */
	public function index() {
		$this->benchmark->mark('start_admin_index');
	
		$member_srl = $this->session->userdata('member_srl');
		$access_token = $this->session->userdata('access_token');
		
		$group_info = $this->session->userdata('group');
		$admin_group_info = array();
		$is_root = FALSE;
		foreach($group_info as $row) {
			if($row['is_root'] == 'Y') {
				$is_root = TRUE;
				$admin_group_info = $row;
				break;
			}
		}
		
		if($member_srl) {
			if(!$is_root) {
				$data['notice_text'] = $this->cmodel->getTextByLanguage('__usrLang10');
				$this->load->view('common/no_authorization', $data);
				
				$this->benchmark->mark('end_admin_index');
				log_message('info', 'admin_index T['.$this->benchmark->elapsed_time('start_admin_index', 'end_admin_index').']');
				return;
			}
			
			$result = $this->cmodel->validAuthorization($access_token, TRUE);
			if($result != $this->success_code) {
				log_message('debug', "Admin index unauthorized access_token[$access_token] goto login");
				$this->showLoginPage();
				
				$this->benchmark->mark('end_admin_index');
				log_message('info', 'admin_index T['.$this->benchmark->elapsed_time('start_admin_index', 'end_admin_index').']');
				return;
			}
				
			$session_expire_sec = $this->config->item('sess_expiration');
			$auth_expire_sec = strtotime($this->session->userdata('access_token_expire')) - time();

			$data['title'] = $this->lang->line('page_title_gboard_admin');
			// session 만료 기간, access_token 만료 기간 중 작은 값을 만료 기간으로 설정한다.
			$data['session_expire_time'] = $session_expire_sec >= $auth_expire_sec ? 
					$auth_expire_sec : $session_expire_sec;
								
			$data['profile_image'] = '';
			$profile_image = $this->session->userdata('profile_image');
			foreach($profile_image as $key=>$value) {
				if($key == '40x40') {
					$data['profile_image'] = $value;
					break;
				}
			}
			
			$service_list = array();
			$this->service_model->getServiceMenu($service_list);
			
			$data['service_list'] = $service_list;
			
			$this->load->view('common/header', $data);
			$this->load->view('admin/main', $data);
			
			$this->benchmark->mark('end_admin_index');
			log_message('info', 'admin_index T['.$this->benchmark->elapsed_time('start_admin_index', 'end_admin_index').']');
			return;
		}
	
		$this->showLoginPage();
		
		$this->benchmark->mark('end_admin_index');
		log_message('info', 'admin_index T['.$this->benchmark->elapsed_time('start_admin_index', 'end_admin_index').']');
	}
	
	/**
	 * 다국어로 설정되어 있는 텍스트 리스트를 출력 하는 html 페이지를 보여 준다.
	 */	
	public function text_list() {
		$this->benchmark->mark('start_admin_text_list');
		
		// root 권한으로 접근 했는지 체크하고, 인증을 체크 한다.
		if(!$this->isValidAuthorization('admin_text_list', 
				'start_admin_text_list', 'end_admin_text_list')) {
			return;
		}
		
		// 지원하는 언어 리스트를 구한다.
		$lang_list = array('list'=>array());
		$result = $this->lang_model->getSupportLanguage($lang_list['list']);
		$data['language'] = $lang_list['list'];
		
		$this->load->view('admin/text_list', $data);
		
		$this->benchmark->mark('end_admin_text_list');
		log_message('info', 'admin_text_list T['.$this->benchmark->elapsed_time('start_admin_text_list', 'end_admin_text_list').']');
	}
	
	/**
	 * oauth 사용하기 위해서 등록한 application list 를 보여준다.
	 */
	public function application_list() {
		$this->benchmark->mark('start_admin_applications_list');
		
		// root 권한으로 접근 했는지 체크하고, 인증을 체크 한다.
		if(!$this->isValidAuthorization('admin_applications_list', 
				'start_admin_applications_list', 'end_admin_applications_list')) {
			return;
		}
		
		$this->load->view('admin/application_list');
		
		$this->benchmark->mark('end_admin_applications_list');
		log_message('info', 'admin_applications_list T['.
			$this->benchmark->elapsed_time('start_admin_applications_list', 'end_admin_applications_list').']');
	}
	
	/**
	 * 로컬 서버나 s3 에 업로드 한 파일 리스트를 보여준다.
	 */
	public function file_list() {
		$this->benchmark->mark('start_admin_file_list');
		
		// root 권한으로 접근 했는지 체크하고, 인증을 체크 한다.
		if(!$this->isValidAuthorization('admin_file_list', 
				'start_admin_file_list', 'end_admin_file_list')) {
			return;
		}
		
		$this->load->view('admin/file_list');
		
		$this->benchmark->mark('end_admin_file_list');
		log_message('info', 'admin_file_list T['.
			$this->benchmark->elapsed_time('start_admin_file_list', 'end_admin_file_list').']');
	}
	
	/**
	 * member list 를 보여준다.
	 */
	public function member_list() {
		$this->benchmark->mark('start_admin_member_list');
		
		// root 권한으로 접근 했는지 체크하고, 인증을 체크 한다.
		if(!$this->isValidAuthorization('admin_member_list', 
				'start_admin_member_list', 'end_admin_member_list')) {
			return;
		}
		
		$this->load->view('admin/member_list');
		
		$this->benchmark->mark('end_admin_member_list');
		log_message('info', 'admin_member_list T['.
			$this->benchmark->elapsed_time('start_admin_member_list', 'end_admin_member_list').']');
	}
}
?>