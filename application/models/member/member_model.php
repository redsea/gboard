<?php
/**
 * Member model
 *
 * @author dhkim94@gmail.com
 */
class Member_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$this->config->load('my_conf/common', TRUE);
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/member', TRUE);
		
		$this->load->database();
		
		$this->yes = $this->config->item('yes', 'my_conf/common');
		$this->no = $this->config->item('no', 'my_conf/common');
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	/**
	 * 가입시 valid 체크를 한다.
	 */
	private function _validJoin($user_id=FALSE, $email_address=FALSE, 
			$password=FALSE, $user_name=FALSE, $nick_name=FALSE) {
	
		// 필수 요소 체크
		if(!$user_id)		{ $user_id = $this->input->post2('user_id', TRUE); }
		if(!$email_address)	{ $email_address = $this->input->post2('email_address', TRUE); }
		if(!$password)		{ $password = $this->input->post2('password', TRUE); }
		if(!$user_name)		{ $user_name = $this->input->post2('user_name', TRUE); }
		if(!$nick_name)		{ $nick_name = $this->input->post2('nick_name', TRUE); }
		
		if(!$email_address)	{ $email_address = $user_id; }
		if(!$nick_name)	{ $nick_name = $user_name; }
		
		// TODO social 로 가입일 경우는 user_id 를 꼭 받아야 하고. 여기에 social 의 id 가 들어간다. email 이 아님.
		
		
		// email_address 검증
		$this->load->helper('email');
		if(!$email_address) {
			log_message('warn', "join invalid email_address[$email_address]. not found");
			return $this->config->item('member_join_invalid_email_address', 'error_code/member');
		}
		if(!valid_email($email_address)) {
			log_message('warn', "join invalid email_address[$email_address]. illegal");
			return $this->config->item('member_join_invalid_email_address', 'error_code/member');
		}
		
		// user_id 검증
		if(!$user_id) {
			log_message('warn', "join invalid user_id[$user_id]. not found");
			return $this->config->item('member_join_invalid_user_id', 'error_code/member');
		}
		if(strlen($user_id) > 128) {
			log_message('warn', "join invalid user_id[$user_id]. too long max[128]. user length[".strlen($user_id)."]");
			return $this->config->item('member_join_invalid_user_id', 'error_code/member');
		}
		
		// password 검증
		if(!$password) {
			log_message('warn', "join invalid password[$password]");
			return $this->config->item('member_join_invalid_password', 'error_code/member');
		}
		// XXX password 길이 체크를 해야 하나??
		
		// user_name 과 nick_name 검증.
		// nick_name 은 파라미터 값이 없어도 user_name 을 그대로 사용하기 때문에, 이상하게 하지 않으면 user_name 만 있으면 자동 통과 됨
		if(!$user_name) {
			log_message('warn', "join invalid user_name[$user_name]");
			return $this->config->item('member_join_invalid_user_name', 'error_code/member');
		}
		if(!$nick_name) {
			log_message('warn', "join invalid nick_name[$nick_name]");
			return $this->config->item('member_join_invalid_nick_name', 'error_code/member');
		}
		
		// 동일한 user_id, nick_name 이 존재 하는지 체크
		$where_array = array('user_id' => $user_id, 'nick_name' => $nick_name);
		$this->db->select('member_srl, user_id, email_address, nick_name')->from($this->table_prefix.'member')->or_where($where_array);
		$query = $this->db->get();

		if($query->num_rows() > 0) {
			$row = $query->row_array();	// 하나만 보면 됨.
			if($row['user_id'] == $user_id) {
				log_message('warn', "join duplicated user_id[$user_id]");
				return $this->config->item('member_join_duplicated_user_id', 'error_code/member');
			}
			if($row['nick_name'] == $nick_name) {
				log_message('warn', "join duplicated nick_name[$nick_name]");
				return $this->config->item('member_join_duplicated_nick_name', 'error_code/member');
			}
		}
		
		$query->free_result();
		
		return $this->success_code;
	}
	
	function join() {
		// join valid 체크
		if(($return_code=$this->_validJoin()) != $this->success_code) {
			return $return_code;
		}

		$password = md5($this->input->post2('password', TRUE));
		$social_type = $this->input->post2('social_type', TRUE);
		$find_account_question = $this->input->post2('find_account_question', TRUE);
		$find_account_answer = $this->input->post2('find_account_answer', TRUE);
		$allow_mailing = $this->input->post2('allow_mailing', TRUE);
		$allow_message = $this->input->post2('allow_message', TRUE);
		$description = $this->input->post2('description', TRUE);
		
		if($allow_mailing && strtoupper($allow_mailing) == $this->yes)	{ $allow_mailing = $this->yes; }
		else				{ $allow_mailing = $this->no; }
		
		if($allow_message && strtoupper($allow_message) == $this->yes)	{ $allow_message = $this->yes; }
		else				{ $allow_message = $this->no; }
		
		
		// image_mark. 이건 미리 올릴 것이기 때문에 file table 에서 읽어 와야 한다.

		
		echo $this->yes;
		//echo $password;
		
		
		return $this->success_code;
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