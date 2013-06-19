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
		
		$this->master_db = FALSE;
		$this->slave_db = FALSE;
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
			$err_code = $this->config->item('member_join_invalid_user_id', 'error_code/member');
			log_message('warn', "_validMemberValue E[$err_code] invalid user_id[$user_id]. not found");
			return $err_code;
		}
		if(strlen($user_id) > 128) {
			$err_code = $this->config->item('member_join_invalid_user_id', 'error_code/member');
			log_message('warn', "_validMemberValue E[$err_code] invalid user_id[$user_id]. too long max[128]. user length[".strlen($user_id)."]");
			return $err_code;
		}
		
		// email_address 검증
		if(!$email_address) {
			$err_code = $this->config->item('member_join_invalid_email_address', 'error_code/member');
			log_message('warn', "_validMemberValue E[$err_code] invalid email_address[$email_address]. not found");
			return $err_code;
		}
		if(!valid_email($email_address)) {
			$err_code = $this->config->item('member_join_invalid_email_address', 'error_code/member');
			log_message('warn', "_validMemberValue E[$err_code] invalid email_address[$email_address]. illegal");
			return $err_code;
		}
		if(strlen($email_address) > 128) {
			$err_code = $this->config->item('member_join_invalid_email_address', 'error_code/member');
			log_message('warn', "_validMemberValue E[$err_code] invalid email_address[$email_address]. too long max[128]. user length[".strlen($email_address)."]");
			return $err_code;
		}
		
		// password 검증
		if(!$password) {
			$err_code = $this->config->item('member_join_invalid_password', 'error_code/member');
			log_message('warn', "_validMemberValue E[$err_code] invalid password[$password]");
			return $err_code;
		}
		
		// user_name 과 nick_name 검증.
		// nick_name 은 파라미터 값이 없어도 user_name 을 그대로 사용하기 때문에, 이상하게 하지 않으면 user_name 만 있으면 자동 통과 됨
		if(!$user_name) {
			$err_code = $this->config->item('member_join_invalid_user_name', 'error_code/member');
			log_message('warn', "_validMemberValue E[$err_code] invalid user_name[$user_name]");
			return $err_code;
		}
		if(strlen($user_name) > 64) {
			$err_code = $this->config->item('member_join_invalid_user_name', 'error_code/member');
			log_message('warn', "_validMemberValue E[$err_code] invalid user_name[$user_name]. too long max[64]. user length[".strlen($user_name)."]");
			return $err_code;
		}
		
		if(!$nick_name) {
			$err_code = $this->config->item('member_join_invalid_nick_name', 'error_code/member');
			log_message('warn', "_validMemberValue E[$err_code] invalid nick_name[$nick_name]");
			return $err_code;
		}
		if(strlen($nick_name) > 64) {
			$err_code = $this->config->item('member_join_invalid_nick_name', 'error_code/member');
			log_message('warn', "_validMemberValue E[$err_code] invalid nick_name[$nick_name]. too long max[64]. user length[".strlen($nick_name)."]");
			return $err_code;
		}
		
		// account 찾기 질문 타입이 올바른지 체크
		if($find_account_question) {
			if(!in_array($find_account_question, $this->config->item('find_account_question', 'my_conf/member'))) {
				$err_code = $this->config->item('member_join_invalid_account_question', 'error_code/member');
				log_message('warn', "_validMemberValue E[$err_code] invalid find_account_question[$find_account_question]");
				return $err_code;
			}
			if(!$find_account_answer) {
				$err_code = $this->config->item('member_join_invalid_account_answer', 'error_code/member');
				log_message('warn', "_validMemberValue E[$err_code] invalid find_account_answer[$find_account_answer]");
				return $err_code;
			}
		}
		
		// 동일한 user_id, nick_name 이 존재 하는지 체크
		if(!$this->slave_db) { $this->slave_db = $this->load->database('slave', TRUE); }
		
		$where_array = array('user_id' => $user_id, 'nick_name' => $nick_name);
		$this->slave_db->select('member_srl, user_id, email_address, nick_name')->from($this->table_prefix.'member')->or_where($where_array);
		$query = $this->slave_db->get();

		if($query->num_rows() > 0) {
			$row = $query->row_array();	// 하나만 보면 됨.
			if($row['user_id'] == $user_id) {
				$err_code = $this->config->item('member_join_duplicated_user_id', 'error_code/member');
				log_message('warn', "_validMemberValue E[$err_code] duplicated user_id[$user_id]");
				return $err_code;
			}
			if($row['nick_name'] == $nick_name) {
				$err_code = $this->config->item('member_join_duplicated_nick_name', 'error_code/member');
				log_message('warn', "_validMemberValue E[$err_code] duplicated nick_name[$nick_name]");
				return $err_code;
			}
		}
		
		$query->free_result();
		
		if($image_mark) {
			$this->slave_db->where_in('file_srl', $image_mark);
			$this->slave_db->from($this->table_prefix.'files');
			$count = $this->slave_db->count_all_results();
			$query->free_result();
			
			if($count != count($image_mark)) {
				// 넘겨준 image_mark 로 사용할 file_srl 중 하나라도 없으면 가입 실패
				$err_code = $this->config->item('member_join_invalid_image_mark', 'error_code/member');
				log_message('warn', "_validMemberValue E[$err_code] image_mark not exist all");
				return $err_code;
			}
		}
		
		// 그냥 자동으로 닫게 놔 둔다.
		//$this->slave_db->close();
		
		return $this->success_code;
	}
	
	/**
	 * 가입시 member_extra table 에 insert 될 정보 valid 체크를 한다.
	 */
	private function _validMemberExtraValue($homepage=FALSE, $blog=FALSE, $birthday=FALSE,
			$country=FALSE, $social_type=FALSE, $social_id=FALSE) {
		if($homepage) {
			if(strlen($homepage) > 256) {
				$err_code = $this->config->item('member_join_invalid_homepage', 'error_code/member');
				log_message('warn', "_validMemberExtraValue E[$err_code] invalid homepage[$homepage]. too long max[256]. user length[".strlen($homepage)."]");
				return $err_code;
			}
		}
		
		if($blog) {
			if(strlen($blog) > 256) {
				$err_code = $this->config->item('member_join_invalid_blog', 'error_code/member');
				log_message('warn', "_validMemberExtraValue E[$err_code] invalid blog[$blog]. too long max[256]. user length[".strlen($blog)."]");
				return $err_code;
			}
		}
		
		if($birthday) {
			if(strlen($birthday) > 8) {
				$err_code = $this->config->item('member_join_invalid_birthday', 'error_code/member');
				log_message('warn', "_validMemberExtraValue E[$err_code] invalid birthday[$birthday]. too long max[8]. user length[".strlen($birthday)."]");
				return $err_code;
			}
			
			$tm = strtotime($birthday);
			if(!$tm) {
				$err_code = $this->config->item('member_join_invalid_birthday', 'error_code/member');
				log_message('warn', "_validMemberExtraValue E[$err_code] invalid birthday[$birthday]. it is invalid date.");
				return $err_code;
			}
			
			// 20130000 으로 생년월일을 주면 strtotime 을 했을때 정상적인 값이 라고 나오기 때문에 실제 값을 비교함.
			if($birthday != date('Y', $tm).date('m', $tm).date('d', $tm)) {
				$err_code = $this->config->item('member_join_invalid_birthday', 'error_code/member');
				log_message('warn', "_validMemberExtraValue E[$err_code] invalid birthday[$birthday]. it is invalid date.");
				return $err_code;
			}
		}
		
		if($country) {
			$data = null;
			$key = null;
			if(strlen($country) == 2)	{ $key='alpha2'; $data = array('alpha2'=>$country); }
			else						{ $key='alpha3'; $data = array('alpha3'=>$country); }
			
			if(!$this->slave_db) { $this->slave_db = $this->load->database('slave'); }
			$this->slave_db->where($key, strtoupper($data[$key]));
			$this->slave_db->from($this->table_prefix.'country_code');
			$count = $this->slave_db->count_all_results();
			if($count <= 0) {
				$err_code = $this->config->item('member_join_invalid_country', 'error_code/member');
				log_message('warn', "_validMemberExtraValue E[$err_code] invalid country[$country].");
				return $err_code;
			}
		}
		
		if($social_type) {
			// 지원하는 social_type 이 table column length 보다 크게 잡지 않을 것이므로, strlen 체크는 할 필요 없음		
			$support_social = $this->config->item('supported_social_type', 'my_conf/member');
			$support_social = explode('|', $support_social);
			if(!in_array($social_type, $support_social)) {
				$err_code = $this->config->item('member_join_invalid_social_type', 'error_code/member');
				log_message('warn', "_validMemberExtraValue E[$err_code] invalid social_type[$social_type].");
				return $err_code;
			}
			
			if(!$social_id || strlen($social_id) > 64) {
				$err_code = $this->config->item('member_join_invalid_social_id', 'error_code/member');
				log_message('warn', "_validMemberExtraValue E[$err_code] invalid social_id[$social_id].");
				return $err_code;
			}
		}
		
		return $this->success_code;
	}
	
	/**
	 * 일반 회원 가입을 시킨다.
	 * 가입된 회원에 대해서는 email confirm 이 필요하다.
	 */
	public function join() {
		// 1. member table 에 insert 될 정보를 가져온다
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

		$password = md5($password);
		$find_account_answer = md5($find_account_answer);
		
		$allow_mailing = $this->input->post2('allow_mailing', TRUE);
		$allow_message = $this->input->post2('allow_message', TRUE);
		$description = $this->input->post2('description', TRUE);
		
		if($allow_mailing && strtoupper($allow_mailing) == $this->yes)	{ $allow_mailing = $this->yes; }
		else				{ $allow_mailing = $this->no; }
		
		if($allow_message && strtoupper($allow_message) == $this->yes)	{ $allow_message = $this->yes; }
		else				{ $allow_message = $this->no; }
		
		// 가입시 main value 에 대한 valid 체크
		if(($return_code=$this->_validMemberValue($user_id, $email_address,
				$password, $user_name, $nick_name, $image_mark,
				$find_account_question, $find_account_answer)) != $this->success_code) {
			return $return_code;
		}
		
		// 2. member_extra 테이블에 insert 될 정보를 가져온다.
		$homepage = $this->input->post2('homepage', TRUE);
		$blog = $this->input->post2('blog', TRUE);
		$birthday = $this->input->post2('birthday', TRUE);
		$gender = $this->input->post2('gender', TRUE);
		if($gender && strtoupper($gender) != 'M') { $gender = 'F'; }
		$country = $this->input->post2('country', TRUE);
		$mobile_phone_number = $this->input->post2('mobile_phone_number', TRUE);	// 유효성 체크를 하지 않는다.
		$phone_number = $this->input->post2('phone_number', TRUE);					// 유효성 체크를 하지 않는다.
		$account_social_type = $this->input->post2('social_type', TRUE);
		$account_social_id = $this->input->post2('social_id', TRUE);
		
		if(($return_code=$this->_validMemberExtraValue($homepage, $blog, 
				$birthday, $country, $account_social_type, $account_social_id)) != $this->success_code) {
			return $return_code;
		}
		
		// 3. 기본 가입 그룹 정보를 가져온다.
		if(!$this->slave_db) { $this->slave_db = $this->load->database('slave'); }
		$this->slave_db->select('group_srl')->from($this->table_prefix.'member_group')->where('is_default', 'Y');
		$query = $this->slave_db->get();
		if(!$query || $query->num_rows() <= 0) {
			$err_code = $this->config->item('member_join_select_default_group', 'error_code/member');
			log_message('warn', "join E[$err_code] select member_group default group fail");
			return $err_code;
		}
		$row = $query->row_array();
		$default_member_group = $row['group_srl'];
		$query->free_result();
		
		// 4. 기본 site 정보 가져오기
		$this->slave_db->select('site_srl')->from($this->table_prefix.'sites')->where('is_default', 'Y');
		$query = $this->slave_db->get();
		if(!$query || $query->num_rows() <= 0) {
			$err_code = $this->config->item('member_join_select_default_site', 'error_code/member');
			log_message('warn', "join E[$err_code] select sites default site fail");
			return $err_code;
		}
		$row = $query->row_array();
		$default_site = $row['site_srl'];
		$query->free_result();
		
		$this->load->helper('date');
		$c_date = mdate('%Y%m%d%h%i%s');
		
		$data_member_extra = FALSE;
		if($homepage || $blog || $birthday || $gender || $country || $mobile_phone_number ||
				$phone_number || ($account_social_type && $account_social_id)) {
			$data_member_extra = array(
					'homepage' => $homepage,
					'blog' => $blog,
					'birthday' => $birthday,
					'gender' => $gender,
					'country' => $country,
					'mobile_phone_number' => $mobile_phone_number,
					'phone_number' => $phone_number,
					'account_social_type' => $account_social_type,
					'account_social_id' => $account_social_id,
					'c_date' => $c_date
				);
		}
		
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
				'image_mark' => serialize($image_mark),
				'description' => $description,
				'change_password_date' => $c_date,
				'c_date' => $c_date
			);
		
		$error_code = $this->success_code;
		if(!$this->master_db) { $this->master_db = $this->load->database('master', TRUE); }
		
		$this->master_db->trans_begin();
		
		// member table 에 insert
		$result = $this->master_db->insert($this->table_prefix.'member', $data_member);
		if(!$result) {
			$error_code = $this->config->item('member_join_insert_member', 'error_code/member');
			log_message('error', "join E[$error_code] insert member table failed");
		}
		
		// member_extra table 에 insert
		if($result) {
			$member_srl = $this->master_db->insert_id();
			$data_member_extra['member_srl'] = $member_srl;
		
			$result = $this->master_db->insert($this->table_prefix.'member_extra', $data_member_extra);
			if(!$result) {
				$error_code = $this->config->item('member_join_insert_member_extra', 'error_code/member');
				log_message('error', "join E[$error_code] insert member_extra table failed");
			}
		}
		
		// member table 의 list_order update
		if($result) {
			$this->master_db->where('member_srl', $member_srl);
			$result = $this->master_db->update($this->table_prefix.'member', array('list_order'=>-$member_srl));
			if(!$result) {
				$error_code = $this->config->item('member_join_update_list_order', 'error_code/member');
				log_message('error', "join E[$error_code] update list_order column in member table failed");
			}
		}
		
		// member_group_member table 에 insert(기본 그룹 가입)
		if($result) {
			$data_member_group = array(
					'group_srl' => $default_member_group,
					'member_srl' => $member_srl,
					'site_srl' => $default_site,
					'c_date' => $c_date
				);
			$result = $this->master_db->insert($this->table_prefix.'member_group_member', $data_member_group);
			if(!$result) {
				$error_code = $this->config->item('member_join_insert_member_group_member', 'error_code/member');
				log_message('error', "join E[$error_code] insert member_group_member table failed");
			}
		}
		
		// image_mark 로 사용한 file_srl 을 member 소유로 member_srl 업데이트
		if($result) {
			$this->master_db->where_in('file_srl', $image_mark);
			$result = $this->master_db->update($this->table_prefix.'files', array('member_srl'=>$member_srl));
			if(!$result) {
				$error_code = $this->config->item('member_join_update_file_owner', 'error_code/member');
				log_message('error', "join E[$error_code] update member_srl column in files table failed");
			}
		}

		if($this->master_db->trans_status() === FALSE) {
			$this->master_db->trans_rollback();
		} else {
			$this->master_db->trans_commit();
		}
		
		return $error_code;
	}	
}
?>