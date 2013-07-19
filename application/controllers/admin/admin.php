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
		//$this->load->model('admin/admin_model', 'model');
		
		$this->config->load('error_code/common', TRUE);
		
		$this->config->load('my_conf/service', TRUE);
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	private function showLoginPage() {
		$data['title'] = $this->lang->line('page_title_login');
		$this->load->view('common/header', $data);
		$this->load->view('admin/login');
	}

	/**
	 * admin 웹페이지 기본 controller. 웹브라우저로 붙을때 여기로 붙는다.
	 */
	public function index() {
		$this->benchmark->mark('start_admin_index');
		
		//$user_id = $this->session->userdata('member_srl');
	
		$member_srl = $this->session->userdata('member_srl');
		$is_root = $this->session->userdata('is_root');
		$access_token = $this->session->userdata('access_token');
		
		if($member_srl) {
			if($is_root != 'Y') {
				// TODO 안내 페이지로 넘겨야 한다.
				//      안내 페이지에는 로그아웃 버튼을 넣어야 한다.
			
				log_message('debug', '-----> this page connect only root group');
				
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
				
				
			// admin 메인 페이지를 보여 준다.
			// TODO 로그인 완료 하고 난 다음 보여 주는 것을 완성 해야 한다.
			//      page 는 형태만 있고, 실제 내용은 ajax 로 호출 하여 완성 해야 한다.
				
			// 이 페이지는 redirect 로 넘어 오기 때문에 authorization check 를 하지 않는다.
			//$result = $this->cmodel->validAuthorization();
			
			$session_expire_sec = $this->config->item('sess_expiration');
			$auth_expire_sec = strtotime($this->session->userdata('access_token_expire')) - time();

			$data['title'] = $this->lang->line('page_title_gboard_admin');
			// session 만료 기간, access_token 만료 기간 중 작은 값을 만료 기간으로 설정한다.
			$data['session_expire_time'] = $session_expire_sec >= $auth_expire_sec ? 
					$auth_expire_sec : $session_expire_sec;
			$data['home_url'] = 'http://'.$this->session->userdata('domain').'/admin';
			$data['profile_image'] = $this->session->userdata('profile_image');
			
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
	 * Admin service 의 menu action 처리(admin tree 를 가져온다)
	 * service controller 에서 처리 할 수 있지만, admin menu 는 admin 만 접근 할수 있도록
	 * 하기 위해서 admin controller 안에서 처리 한다.
	 *
	 * @param service_id {string} admin tree 의 service id
	 * @param leaf_id {string} 가져올 leaf 의 id. 값이 없으면 root 부터 가져온다.
	 * @param depth {number} 가져올 depth. 값이 없으면 2 (자신과 children 까지) 로 설정 한다. -1 이면 max tree depth 인 10으로 설정 한다.
	 */
	public function menu($service_id, $leaf_id=0, $depth=2) {
		$this->benchmark->mark('start_admin_menu');
		
		$result = $this->cmodel->isAdmin();
		if($result != $this->success_code) {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'common'
				)
			);
			$this->benchmark->mark('end_admin_menu');
			log_message('info', 'admin_menu T['.$this->benchmark->elapsed_time('start_admin_menu', 'end_admin_menu').']');
			return;
		}
		
		// access_token 체크
		$result = $this->cmodel->validAuthorization(FALSE, TRUE);
		if($result != $this->success_code) {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'common'
				)
			);
			$this->benchmark->mark('end_admin_menu');
			log_message('info', 'admin_menu T['.$this->benchmark->elapsed_time('start_admin_menu', 'end_admin_menu').']');
			return;
		}
		
		// 받은 depth 가 -1 이면 max tree depth 인 10으로 설정 한다.
		if($depth == -1) {
			$depth = $this->config->item('tree_max_depth', 'my_conf/service');
		}
		
		$menu_tree = array();
		$result = $this->service_model->getMenuTree($menu_tree, $service_id, $leaf_id, $depth);
		
		if($result == $this->success_code) {
			$tree_part['data'] = $menu_tree[$leaf_id]['children'];
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'service',
						'other' => $tree_part
				)
			);
		} else {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'service'
				)
			);
		}
		
		$this->benchmark->mark('end_admin_menu');
		log_message('info', 'admin_menu T['.$this->benchmark->elapsed_time('start_admin_menu', 'end_admin_menu').']');
	}

}
?>