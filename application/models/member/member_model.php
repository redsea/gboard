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
		$this->config->load('my_conf/member', TRUE);
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/member', TRUE);
		
		$this->yes = $this->config->item('yes', 'my_conf/common');
		$this->no = $this->config->item('no', 'my_conf/common');
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	/**
	 * 가입시 member table 에 insert 될 정보 valid 체크를 한다.
	 */
	private function _validMemberValue($user_id=FALSE, $email_address=FALSE, 
			$password=FALSE, $user_name=FALSE, $nick_name=FALSE, $image_mark=FALSE,
			$find_account_question=FALSE, $find_account_answer=FALSE) {
		// email_address 검증
		$this->load->helper('email');
		
		// user_id 검증
		if(!$user_id) {
			log_message('warn', "_validMemberValue invalid user_id[$user_id]. not found");
			return $this->config->item('member_join_invalid_user_id', 'error_code/member');
		}
		if(strlen($user_id) > 128) {
			log_message('warn', "_validMemberValue invalid user_id[$user_id]. too long max[128]. user length[".strlen($user_id)."]");
			return $this->config->item('member_join_invalid_user_id', 'error_code/member');
		}
		
		// email_address 검증
		if(!$email_address) {
			log_message('warn', "_validMemberValue invalid email_address[$email_address]. not found");
			return $this->config->item('member_join_invalid_email_address', 'error_code/member');
		}
		if(!valid_email($email_address)) {
			log_message('warn', "_validMemberValue invalid email_address[$email_address]. illegal");
			return $this->config->item('member_join_invalid_email_address', 'error_code/member');
		}
		if(strlen($email_address) > 128) {
			log_message('warn', "_validMemberValue invalid email_address[$email_address]. too long max[128]. user length[".strlen($email_address)."]");
			return $this->config->item('member_join_invalid_email_address', 'error_code/member');
		}
		
		// password 검증
		if(!$password) {
			log_message('warn', "_validMemberValue invalid password[$password]");
			return $this->config->item('member_join_invalid_password', 'error_code/member');
		}
		// XXX password 길이 체크를 해야 하나??
		
		// user_name 과 nick_name 검증.
		// nick_name 은 파라미터 값이 없어도 user_name 을 그대로 사용하기 때문에, 이상하게 하지 않으면 user_name 만 있으면 자동 통과 됨
		if(!$user_name) {
			log_message('warn', "_validMemberValue invalid user_name[$user_name]");
			return $this->config->item('member_join_invalid_user_name', 'error_code/member');
		}
		if(strlen($user_name) > 64) {
			log_message('warn', "_validMemberValue invalid user_name[$user_name]. too long max[64]. user length[".strlen($user_name)."]");
			return $this->config->item('member_join_invalid_user_name', 'error_code/member');
		}
		
		if(!$nick_name) {
			log_message('warn', "_validMemberValue invalid nick_name[$nick_name]");
			return $this->config->item('member_join_invalid_nick_name', 'error_code/member');
		}
		if(strlen($nick_name) > 64) {
			log_message('warn', "_validMemberValue invalid nick_name[$nick_name]. too long max[64]. user length[".strlen($nick_name)."]");
			return $this->config->item('member_join_invalid_nick_name', 'error_code/member');
		}
		
		// account 찾기 질문 타입이 올바른지 체크
		if($find_account_question) {
			if(!in_array($find_account_question, $this->config->item('find_account_question', 'my_conf/member'))) {
				log_message('warn', "_validMemberValue invalid find_account_question[$find_account_question]");
				return $this->config->item('member_join_invalid_account_question', 'error_code/member');
			}
			if(!$find_account_answer) {
				log_message('warn', "_validMemberValue invalid find_account_answer[$find_account_answer]");
				return $this->config->item('member_join_invalid_account_answer', 'error_code/member');
			}
		}
		
		// 동일한 user_id, nick_name 이 존재 하는지 체크
		$this->load->database('slave');
		
		$where_array = array('user_id' => $user_id, 'nick_name' => $nick_name);
		$this->db->select('member_srl, user_id, email_address, nick_name')->from($this->table_prefix.'member')->or_where($where_array);
		$query = $this->db->get();

		if($query->num_rows() > 0) {
			$row = $query->row_array();	// 하나만 보면 됨.
			if($row['user_id'] == $user_id) {
				log_message('warn', "_validMemberValue duplicated user_id[$user_id]");
				return $this->config->item('member_join_duplicated_user_id', 'error_code/member');
			}
			if($row['nick_name'] == $nick_name) {
				log_message('warn', "_validMemberValue duplicated nick_name[$nick_name]");
				return $this->config->item('member_join_duplicated_nick_name', 'error_code/member');
			}
		}
		
		$query->free_result();
		
		if($image_mark) {
			$this->db->where_in('file_srl', $image_mark);
			$this->db->from($this->table_prefix.'files');
			$count = $this->db->count_all_results();
			$query->free_result();
			
			if($count != count($image_mark)) {
				// 넘겨준 image_mark 로 사용할 file_srl 중 하나라도 없으면 가입 실패
				log_message('warn', '_validMemberValue image_mark not exist all');
				return $this->config->item('member_join_invalid_image_mark', 'error_code/member');
			}
		}
		
		$this->db->close();
		
		return $this->success_code;
	}
	
	/**
	 * 가입시 member_extra table 에 insert 될 정보 valid 체크를 한다.
	 */
	private function _validMemberExtraValue() {
		
	}
	
	public function join() {
		// member table 에 insert 될 정보를 가져온다
		$user_id = trim($this->input->post2('user_id', TRUE));
		$email_address = trim($this->input->post2('email_address', TRUE));
		$password = trim($this->input->post2('password', TRUE));
		$user_name = trim($this->input->post2('user_name', TRUE));
		$nick_name = trim($this->input->post2('nick_name', TRUE));
		
		// nick_name 도 mandatory parameter 로 꼭 받아야 하는 것으로 하자!
		// nick_name 이 없으면 user_name 을 nick_name 으로 사용한다.
		//if(!$nick_name)	{ $nick_name = $user_name; }
		
		$find_account_question = trim($this->input->post2('find_account_question', TRUE));
		$find_account_answer = trim($this->input->post2('find_account_answer', TRUE));
		
		$image_mark = trim($this->input->post2('image_mark', TRUE));
		if($image_mark) {
			$image_mark_arr = explode(',', $image_mark);
			$image_mark = array();
			foreach($image_mark_arr as $value) {
				if(trim($value) != '') {
					array_push($image_mark, $value);
				}
			}
		} else {
			$image_mark = FALSE;
		}
	
		// 가입시 main value 에 대한 valid 체크
		if(($return_code=$this->_validMemberValue($user_id, $email_address,
				$password, $user_name, $nick_name, $image_mark,
				$find_account_question, $find_account_answer)) != $this->success_code) {
			return $return_code;
		}

		$password = md5($password);
		$find_account_answer = md5($find_account_answer);
		
		$allow_mailing = $this->input->post2('allow_mailing', TRUE);
		$allow_message = $this->input->post2('allow_message', TRUE);
		$description = $this->input->post2('description', TRUE);
		
		if($allow_mailing && strtoupper($allow_mailing) == $this->yes)	{ $allow_mailing = $this->yes; }
		else				{ $allow_mailing = $this->no; }
		
		if($allow_message && strtoupper($allow_message) == $this->yes)	{ $allow_message = $this->yes; }
		else				{ $allow_message = $this->no; }
		
		
		// TODO 여기서 부터 해야 한다....member_extra 테이블에 들어갈 정보 유효성 체크를 해야 한다.
		
		// member_extra 테이블에 insert 될 정보를 가져온다.
		$homepage = $this->input->post2('homepage', TRUE);
		$blog = $this->input->post2('blog', TRUE);
		$birthday = $this->input->post2('birthday', TRUE);
		$gender = $this->input->post2('gender', TRUE);
		$nation = $this->input->post2('nation', TRUE);
		if($nation) {
			$data = null;
			$key = null;
			if(strlen($nation) == 2)	{ $key='alpha2'; $data = array('alpha2'=>$nation); }
			else						{ $key='alpha3'; $data = array('alpha3'=>$nation); }
			
			$this->db->where($key, strtoupper($data[$key]));
			$this->db->from($this->table_prefix.'nations');
			$count = $this->db->count_all_results();
			
			if($count <= 0) {
				
			}
			
			echo "->count[$count]";
		}
		
		
		$nation_phone_number = $this->input->post2('country_call_code', TRUE);
		$mobile_phone_number = $this->input->post2('mobile_phone_number', TRUE);
		$phone_number = $this->input->post2('phone_number', TRUE);
		$account_social_type = $this->input->post2('social_type', TRUE);
		$account_social_id = $this->input->post2('social_id', TRUE);
		
		$support_social = $this->config->item('supported_social_type', 'my_conf/member');
		$support_social = explode('|', $support_social);
		$is_join_by_social = FALSE;
		foreach($support_social as $value) {
			if($account_social_type == $value) {
				$is_join_by_social = TRUE;
				break;
			}
		}
		
		if($account_social_type && !$is_join_by_social) {
			log_message('warn', "join not supported social type[$account_social_type]");
			return $this->config->item('member_join_not_supported_social', 'error_code/member');
		}
		
		
		/*
		
		// member table 에 insert
		$data_member = array(
				'user_id' => $user_id,
				'email_address' => $email_address,
				'password' => $password,
				'user_name' => $user_name,
				'nick_name' => $nick_name,
				'find_account_question' => $find_account_question,
				'find_account_answer' => $find_account_answer,
				'allow_mailing' => $allow_mailing,
				'allow_message' => $allow_message,
				'image_mark' => $image_mark,
				'description' => $description,
				'c_date' => mdate('%Y%m%d%h%i%s')
			);
		
		$this->db->trans_begin();
		
		$result = $this->db->insert($this->table_prefix.'member', $data);
		if(!$result) {
			log_message('error', 'join insert member failed');
			return FALSE;
		}
		

		if($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		*/
		
		
		
		// image_mark. 이건 미리 올릴 것이기 때문에 file table 에서 읽어 와야 한다.

		
		
		
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