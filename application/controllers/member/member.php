<?php
/**
 * Member Controller
 *
 * member/member 는 route 를 통해서 member/[function] 으로 라우팅 된다.
 *
 * @author	dhkim94@gmail.com
 */
class Member extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/member', TRUE);
		
		$this->load->library('myutil');
		
		$this->load->model('common/common_model', 'cmodel');
		$this->load->model('member/member_model', 'model');
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}

	public function index() {
		echo "member index";
	}
	
	/**
	 * 일반 회원 가입을 하는 API
	 * 가입된 회원에 대해서는 email confirm 이 필요하다.
	 *
	 * 다음에 주의 해야 한다.
	 * 1. social 로 가입 했다면 social 에서 받은 social user id 를 uesr_id 로 사용해야 한다.
	 *
	 */
	public function join() {
		$result = $this->model->join();
		
		// TODO 가입을 했으니, 로그인 까지 시켜 줘야 하는데, email confirm 때문에 로그인 대기를 타야 한다.
		// TODO 이메일도 보내야 하네....
		// TODO 로그인 이후 다시 진행 하도록 하자.
		
		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'member'
				)
			);
	}
	
	/**
	 * 로그인 할때 사용하는 API
	 * 로그인이 완료 되면 호출 할 URL 주소를 넘긴다.
	 */
	public function login() {
		$this->benchmark->mark('start_member_login');
	
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
			$this->benchmark->mark('end_member_login');
			log_message('info', 'member_login T['.$this->benchmark->elapsed_time('start_member_login', 'end_member_login').']');
			return;
		}
		
		$user_id = trim($this->input->post2('user_id', TRUE));
		$password = trim($this->input->post2('password', TRUE));
		
		$ret = array();
		
		$result = $this->model->login($ret, $user_id, $password);
		if($result != $this->success_code) {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'member'
				)
			);
		} else {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'member',
						'other' => $ret
				)
			);
		}
		
		$this->benchmark->mark('end_member_login');
		log_message('info', 'member_login T['.$this->benchmark->elapsed_time('start_member_login', 'end_member_login').']');
	}
	
	/**
	 * member list 를 구한다.
	 */
	public function member_list() {
		$this->benchmark->mark('start_member_member_list');
	
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
			$this->benchmark->mark('end_member_member_list');
			log_message('info', 'member_member_list T['.
					$this->benchmark->elapsed_time('start_member_member_list', 'end_member_member_list').']');
			return;
		}
		
		$start_row = $this->input->post2('iDisplayStart');	// 보여줄 row 의 start index
		$row_count = $this->input->post2('iDisplayLength');	// 한 페이지에 보여줄 row count
		$search_value = $this->input->post2('sSearch');		// search value
		
		$iSortCol = $this->input->post2('iSortCol_0');		// sort 할 column number
		$sSortDir = $this->input->post2('sSortDir_0');		// sort 방향(asc, desc)
		
		if($sSortDir === FALSE) { $sSortDir = 'desc'; }
		
		$data = array('sEcho'=>$this->input->post2('sEcho'));
		$result = $this->model->getMemberList($data, $search_value, $start_row,
				$row_count, intval($iSortCol), $sSortDir);
		
		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'member',
						'other' => $data
					)
			);
		
		$this->benchmark->mark('end_member_member_list');
		log_message('info', 'member_member_list T['.
				$this->benchmark->elapsed_time('start_member_member_list', 'end_member_member_list').']');
	}
	
	/**
	 * member 의 상세 정보를 구한다.
	 */
	public function member_detail() {
		$this->benchmark->mark('start_member_member_detail');
		
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
			$this->benchmark->mark('end_member_member_detail');
			log_message('info', 'member_member_detail T['.
					$this->benchmark->elapsed_time('start_member_member_detail', 'end_member_member_detail').']');
			return;
		}
		
		$user_id = $this->input->post2('user_id');
		
		$data = array();
		$result = $this->model->getUserInfo($data, $user_id);
		
		if($result != $this->success_code) {
			$this->load->view(
					'common/output_view', 
					array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'member'
					)
				);
		} else {
			$this->load->view(
					'common/output_view', 
					array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'member',
						'other' => $data
					)
				);
		}
		
		$this->benchmark->mark('end_member_member_detail');
		log_message('info', 'member_member_detail T['.
				$this->benchmark->elapsed_time('start_member_member_detail', 'end_member_member_detail').']');
	}
}
?>