<?php
/**
 * Member model
 *
 * @author dhkim94@gmail.com
 */
class Member_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$this->config->load('error_code/join', TRUE);
		$this->load->database();
	}
	
	function join() {
		$log = BGLog::sharedInstance();
	
		// 필수 요소 체크
		$user_id		= $this->input->post2('user_id', TRUE);
		$email_address	= $this->input->post2('email_address', TRUE);
		$password		= $this->input->post2('password', TRUE);
		$user_name		= $this->input->post2('user_name', TRUE);
		$nick_name		= $this->input->post2('nick_name', TRUE);
		
		if(!$user_id)	{ $user_id = $email_address; }
		if(!$nick_name)	{ $nick_name = $user_name; }
		
		// TODO social 로 가입일 경우는 user_id 를 꼭 받아야 하고. 여기에 social 의 id 가 들어간다. email 이 아님.
		
		
		// email_address 검증
		$this->load->helper('email');
		if(!$email_address) {
			$log->error("join invalid email_address[$email_address]. not found");
			return $this->config->item('join_invalid_email_address', 'error_code/join');
		}
		if(!valid_email($email_address)) {
			$log->error("join invalid email_address[$email_address]. illegal");
			return $this->config->item('join_invalid_email_address', 'error_code/join');
		}
		
		// user_id 검증
		if(!$user_id) {
			$log->error("join invalid user_id[$user_id]. not found");
			return $this->config->item('join_invalid_user_id', 'error_code/join');
		}
		if(strlen($user_id) > 128) {
			$log->error("join invalid user_id[$user_id]. too long max[128]. user length[".strlen($user_id)."]");
			return $this->config->item('join_invalid_user_id', 'error_code/join');
		}
		
		// password 검증
		if(!$password) {
			$log->error("join invalid password[$password]");
			return $this->config->item('join_invalid_password', 'error_code/join');
		}
		// XXX password 길이 체크를 해야 하나??
		
		// user_name 과 nick_name 검증.
		// nick_name 은 파라미터 값이 없어도 user_name 을 그대로 사용하기 때문에, 이상하게 하지 않으면 user_name 만 있으면 자동 통과 됨
		if(!$user_name) {
			$log->error("join invalid user_name[$user_name]");
			return $this->config->item('join_invalid_user_name', 'error_code/join');
		}
		if(!$nick_name) {
			$log->error("join invalid nick_name[$nick_name]");
			return $this->config->item('join_invalid_nick_name', 'error_code/join');
		}
		
		// 여기서 부터는 DB 에서 읽어서 체크 해야 한다.
		
		// user_id 가 이미 존재 하는지 체크
		
	}
	
	
	
	
	
	
	function dhkim() {
		// http://www.cikorea.net/user_guide_2.1.0/database/index.html
		// 위의 URL 을 참고 하여 database 처리를 해 보자.
	
		/*
		$this->load->database();
		
		//$this->db->query('SELECT group_id FROM gbd_member_group');
		
		$this->db->trans_begin();
		
		$this->db->query(
				' INSERT INTO gbd_member_group '.
				' (site_id, group_id, title, is_default, is_admin, icon, description, c_date, u_date) '.
				' VALUES '.
				' (1, 3, "ti3", "N", "N", "", "", "20130603010101", "" )'
			);
		
		
		if($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		
		//$this->db->trans_rollback();
		//$this->db->trans_commit();
		*/
		
		
		return "in model";
	}
}
?>