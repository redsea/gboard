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
	public function joina() {
		$this->load->model('member/member_model', 'model');
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
	
	public function login() {
		$result = $this->cmodel->getAuthorization();
		if($result != $this->success_code) {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'member'
				)
			);
			return;
		}
	
	
/*
	
	
		$this->load->model('member/member_model', 'model');
		$result = $this->model->login();
*/
	
		echo "login";
	}
	
	
	public function hello() {
		$this->load->model('member_group/member_group_model', 'model');
		
		// TODO oauth 이후에 진행한다.
		
		
		echo $this->model->dhkim();
		
		//echo "hello group";
	}
}
?>