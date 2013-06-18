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
		
		$this->config->load('error_code/member', TRUE);
		
		$this->load->library('myutil');
	}

	public function index() {
		echo "member index";
	}
	
	/**
	 * 회원 가입을 하는 API
	 *
	 * 다음에 주의 해야 한다.
	 * 1. social 로 가입 했다면 social 에서 받은 social user id 를 uesr_id 로 사용해야 한다.
	 * 2. nick_name 이 없다면 user_name 을 nick_name 으로 자동 사용하는데, user_name 은 중복 허용, nick_name 은 중복 허용 하지
	 *    않기 때문에 왠만하면 nick_name 을 주는 것이 좋다.
	 *
	 */
	public function join() {
		$this->load->model('member/member_model', 'model');
		$result = $this->model->join();
		
		echo $result."<br/>";
		
		
		$this->load->view(
				'output_view', 
				array(
						'output'=>$this->myutil, 
						//'code'=>$this->config->item('mfile_insert_table_fail', 'error_code/member'),
						'code'=>$result,
						'controller' => 'member'
				)
			);
		
		
		
		
		
		/*
		$this->load->helper(array('form', 'url'));
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('username', 'Username', 'required');
		
		if ($this->form_validation->run() == FALSE) {
			echo "false<br/>";
		} else {
			echo "true<br/>";
		}
		*/
		
		
		
		
		
		
		/*
    `user_id` varchar(128) NOT NULL, 
    `email_address` varchar(128) NOT NULL, 
    `password` varchar(64) NOT NULL, 
    `user_name` varchar(64) NOT NULL, 
    `nick_name` varchar(64) NOT NULL, 
    `social_type` varchar(8) DEFAULT NULL,
    `find_account_question` bigint(11) DEFAULT NULL, 
    `find_account_answer` varchar(256) DEFAULT NULL, 
    `homepage` varchar(256) DEFAULT NULL, 
    `blog` varchar(256) DEFAULT NULL, 
    `birthday` char(8) DEFAULT NULL, 
    `allow_mailing` char(2) NOT NULL DEFAULT 'N', 
    `allow_message` char(2) NOT NULL DEFAULT 'N', 
    `image_mark` TEXT NULL , 
    `block` char(2) NOT NULL DEFAULT 'N', 
    `description` text, 
    `list_order` bigint(11) NOT NULL DEFAULT '1', 
    `email_confirm` char(2) NOT NULL DEFAULT 'N', 
    `limit_date` char(14) DEFAULT NULL, 
    `last_login_date` char(14) DEFAULT NULL, 
    `change_password_date` char(14) NOT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    */
		
		
	}
	
	
	public function hello() {
		$this->load->model('member_group/member_group_model', 'model');
		
		
		
		
		echo $this->model->dhkim();
		
		//echo "hello group";
	}
}
?>