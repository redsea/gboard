<?php
/**
 * Member model
 *
 * @author dhkim94@gmail.com
 */
class Member_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$this->load->helper('date');
		
		$this->config->load('my_conf/common', TRUE);
		$this->config->load('my_conf/member', TRUE);
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/member', TRUE);
		
		$this->load->model('common/common_model', 'cmodel');
		
		$this->yes = $this->config->item('yes', 'my_conf/common');
		$this->no = $this->config->item('no', 'my_conf/common');
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		$this->master_db = FALSE;
		$this->slave_db = FALSE;
	}
	
	/**
	 * 가입시 member table 에 insert 될 정보 valid 체크를 한다.
	 *
	 * @param user_info {array} member 의 정보
	 *							- user_id : user id
	 *							- email_address : email address
	 *							- password : user password
	 *							- user_name : user name
	 *							- nick_name : nick name
	 *							- find_account_question : 아이디 찾을 때 사용되는 질문
	 *							- find_account_answer : 아이디 찾을 때 사용되는 답변
	 *							- image_mark : 회원 프로파일 이미지로 사용할 이미지 file_srl
	 */
	private function validJoinValue($user_info) {
		// email_address 검증
		$this->load->helper('email');
		
		// user_id 검증
		if(!$user_info['user_id']) {
			$err_code = $this->config->item('member_invalid_user_id', 'error_code/member');
			log_message('warn', "validJoinValue E[$err_code] no user_id");
			return $err_code;
		}
		if(strlen($user_info['user_id']) > 128) {
			$err_code = $this->config->item('member_invalid_user_id', 'error_code/member');
			log_message('warn', "validJoinValue E[$err_code] invalid user_id[".$user_info['user_id'].
					"]. too long max[128]. user length[".strlen($user_info['user_id'])."]");
			return $err_code;
		}
		
		// email_address 검증
		if(!$user_info['email_address']) {
			$err_code = $this->config->item('member_join_invalid_email_address', 'error_code/member');
			log_message('warn', "validJoinValue E[$err_code] no email_address");
			return $err_code;
		}
		if(!valid_email($user_info['email_address'])) {
			$err_code = $this->config->item('member_join_invalid_email_address', 'error_code/member');
			log_message('warn', "validJoinValue E[$err_code] invalid email_address[".
					$user_info['email_address']."]. illegal");
			return $err_code;
		}
		if(strlen($user_info['email_address']) > 128) {
			$err_code = $this->config->item('member_join_invalid_email_address', 'error_code/member');
			log_message('warn', "validJoinValue E[$err_code] invalid email_address[".
					$user_info['email_address']."]. too long max[128]. user length[".strlen($user_info['email_address'])."]");
			return $err_code;
		}
		
		// password 검증
		if(!$user_info['password']) {
			$err_code = $this->config->item('member_invalid_password', 'error_code/member');
			log_message('warn', "validJoinValue E[$err_code] no password");
			return $err_code;
		}
		
		// user_name 과 nick_name 검증.
		// nick_name 은 파라미터 값이 없어도 user_name 을 그대로 사용하기 때문에, 이상하게 하지 않으면 user_name 만 있으면 자동 통과 됨
		if(!$user_info['user_name']) {
			$err_code = $this->config->item('member_join_invalid_user_name', 'error_code/member');
			log_message('warn', "validJoinValue E[$err_code] no user_name");
			return $err_code;
		}
		if(strlen($user_info['user_name']) > 64) {
			$err_code = $this->config->item('member_join_invalid_user_name', 'error_code/member');
			log_message('warn', "validJoinValue E[$err_code] invalid user_name[".
					$user_info['user_name']."]. too long max[64]. user length[".strlen($user_info['user_name'])."]");
			return $err_code;
		}
		
		if(!$user_info['nick_name']) {
			$err_code = $this->config->item('member_join_invalid_nick_name', 'error_code/member');
			log_message('warn', "validJoinValue E[$err_code] no nick_name");
			return $err_code;
		}
		if(strlen($user_info['nick_name']) > 64) {
			$err_code = $this->config->item('member_join_invalid_nick_name', 'error_code/member');
			log_message('warn', "validJoinValue E[$err_code] invalid nick_name[".
					$user_info['nick_name']."]. too long max[64]. user length[".strlen($user_info['nick_name'])."]");
			return $err_code;
		}
		
		// account 찾기 질문 타입이 올바른지 체크
		if($user_info['find_account_question']) {
			if(!in_array($user_info['find_account_question'], $this->config->item('find_account_question', 'my_conf/member'))) {
				$err_code = $this->config->item('member_join_invalid_account_question', 'error_code/member');
				log_message('warn', "validJoinValue E[$err_code] invalid find_account_question[".
						$user_info['find_account_question']."]");
				return $err_code;
			}
			if(!$user_info['find_account_answer']) {
				$err_code = $this->config->item('member_join_invalid_account_answer', 'error_code/member');
				log_message('warn', "validJoinValue E[$err_code] invalid find_account_answer[".
						$user_info['find_account_answer']."]");
				return $err_code;
			}
		}
		
		// 동일한 user_id, nick_name 이 존재 하는지 체크
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$where_array = array('user_id' => $user_info['user_id'], 'nick_name' => $user_info['nick_name']);
		$this->slave_db->select('member_srl, user_id, email_address, nick_name')->from($this->slave_db->dbprefix('member'))->or_where($where_array);
		$query = $this->slave_db->get();

		if($query->num_rows() > 0) {
			$row = $query->row_array();	// 하나만 보면 됨.
			if($row['user_id'] == $user_info['user_id']) {
				$err_code = $this->config->item('member_join_duplicated_user_id', 'error_code/member');
				log_message('warn', "validJoinValue E[$err_code] duplicated user_id[".$user_info['user_id']."]");
				return $err_code;
			}
			if($row['nick_name'] == $user_info['nick_name']) {
				$err_code = $this->config->item('member_join_duplicated_nick_name', 'error_code/member');
				log_message('warn', "validJoinValue E[$err_code] duplicated nick_name[".$user_info['nick_name']."]");
				return $err_code;
			}
		}
		
		$query->free_result();
		
		if($user_info['image_mark']) {
			$this->slave_db->where_in('file_srl', $user_info['image_mark']);
			$this->slave_db->from($this->slave_db->dbprefix('files'));
			$count = $this->slave_db->count_all_results();
			$query->free_result();
			
			if($count != count($user_info['image_mark'])) {
				// 넘겨준 image_mark 로 사용할 file_srl 중 하나라도 없으면 가입 실패
				$err_code = $this->config->item('member_join_invalid_image_mark', 'error_code/member');
				log_message('warn', "validJoinValue E[$err_code] image_mark not exist all");
				return $err_code;
			}
		}
		
		// 그냥 자동으로 닫게 놔 둔다.
		//$this->slave_db->close();
		
		return $this->success_code;
	}
	
	/**
	 * 가입시 member_extra table 에 insert 될 정보 valid 체크를 한다.
	 *
	 * @param user_info {array} member 의 정보
	 *							- homepage : member 의 homepage URL
	 *							- blog : member 의 blog URL
	 *							- birthday : member 의 birthday
	 *							- country : member 의 contry
	 *							- social_type : social 인증을 통해서 가입 할때 social type
	 *							- social_id : social 인증을 통해서 가입 할때 social id
	 */
	private function validJoinExtraValue($user_info) {
		if($user_info['homepage']) {
			if(strlen($user_info['homepage']) > 256) {
				$err_code = $this->config->item('member_join_invalid_homepage', 'error_code/member');
				log_message('warn', "validJoinExtraValue E[$err_code] invalid homepage[".
						$user_info['homepage']."]. too long max[256]. user length[".strlen($user_info['homepage'])."]");
				return $err_code;
			}
		}
		
		if($user_info['blog']) {
			if(strlen($user_info['blog']) > 256) {
				$err_code = $this->config->item('member_join_invalid_blog', 'error_code/member');
				log_message('warn', "validJoinExtraValue E[$err_code] invalid blog[".
						$user_info['blog']."]. too long max[256]. user length[".strlen($user_info['blog'])."]");
				return $err_code;
			}
		}
		
		if($user_info['birthday']) {
			if(strlen($user_info['birthday']) > 8) {
				$err_code = $this->config->item('member_join_invalid_birthday', 'error_code/member');
				log_message('warn', "validJoinExtraValue E[$err_code] invalid birthday[".
						$user_info['birthday']."]. too long max[8]. user length[".strlen($user_info['birthday'])."]");
				return $err_code;
			}
			
			$tm = strtotime($user_info['birthday']);
			if(!$tm) {
				$err_code = $this->config->item('member_join_invalid_birthday', 'error_code/member');
				log_message('warn', "validJoinExtraValue E[$err_code] invalid birthday[".
						$user_info['birthday']."]. it is invalid date.");
				return $err_code;
			}
			
			// 20130000 으로 생년월일을 주면 strtotime 을 했을때 정상적인 값이 라고 나오기 때문에 실제 값을 비교함.
			if($user_info['birthday'] != date('Y', $tm).date('m', $tm).date('d', $tm)) {
				$err_code = $this->config->item('member_join_invalid_birthday', 'error_code/member');
				log_message('warn', "validJoinExtraValue E[$err_code] invalid birthday[".
						$user_info['birthday']."]. it is invalid date.");
				return $err_code;
			}
		}
		
		if($user_info['country']) {
			$data = null;
			$key = null;
			if(strlen($user_info['country']) == 2)	{
				$key='alpha2';
				$data = array('alpha2'=>$user_info['country']);
			} else {
				$key='alpha3';
				$data = array('alpha3'=>$user_info['country']);
			}
			
			if(!$this->slave_db) {
				$this->slave_db = $this->load->database('slave', TRUE);
				$this->slave_db->set_dbprefix($this->table_prefix);
			}
			
			$this->slave_db->where($key, strtoupper($data[$key]));
			$this->slave_db->from($this->slave_db->dbprefix('country_code'));
			$count = $this->slave_db->count_all_results();
			if($count <= 0) {
				$err_code = $this->config->item('member_join_invalid_country', 'error_code/member');
				log_message('warn', "validJoinExtraValue E[$err_code] invalid country[".$user_info['country']."]");
				return $err_code;
			}
		}
		
		if($user_info['country_call_code']) {
			if(!$this->slave_db) {
				$this->slave_db = $this->load->database('slave', TRUE);
				$this->slave_db->set_dbprefix($this->table_prefix);
			}
			
			$this->slave_db->where('country_call_code', $user_info['country_call_code']);
			$this->slave_db->from($this->slave_db->dbprefix('country_code'));
			$count = $this->slave_db->count_all_results();
			if($count <= 0) {
				$err_code = $this->config->item('member_join_no_contry_call_code', 'error_code/member');
				log_message('warn', "validJoinExtraValue E[$err_code] invalid country call code[".
						$user_info['country_call_code']."]");
				return $err_code;
			}
		}		
		
		if($user_info['social_type']) {
			// 지원하는 social_type 이 table column length 보다 크게 잡지 않을 것이므로, strlen 체크는 할 필요 없음		
			$support_social = $this->config->item('supported_social_type', 'my_conf/member');
			$support_social = explode('|', $support_social);
			if(!in_array($user_info['social_type'], $support_social)) {
				$err_code = $this->config->item('member_join_invalid_social_type', 'error_code/member');
				log_message('warn', "validJoinExtraValue E[$err_code] invalid social_type[".$user_info['social_type']."]");
				return $err_code;
			}
			
			if(!$user_info['social_id'] || strlen($user_info['social_id']) > 64) {
				$err_code = $this->config->item('member_join_invalid_social_id', 'error_code/member');
				log_message('warn', "validJoinExtraValue E[$err_code] invalid social_id[".$user_info['social_id']."]");
				return $err_code;
			}
		}
		
		return $this->success_code;
	}
	
	/**
	 * 일반 회원 가입을 시킨다.
	 * 가입된 회원에 대해서는 email confirm 이 필요하다.
	 *
	 * @param user_info {array} 가입할 회원 정보를 담은 array
	 *							- user_id : user id
	 *							- email_address : email address
	 *							- password : user password
	 *							- user_name : user name
	 *							- nick_name : nick name
	 *							- find_account_question : 아이디 찾을 때 사용되는 질문
	 *							- find_account_answer : 아이디 찾을 때 사용되는 답변
	 *							- image_mark : 회원 프로파일 이미지로 사용할 이미지 file_srl
	 *							- allow_mailing : mail 을 받을 건지 여부
	 *							- allow_message : message 를 받을 건지 여부
	 *							- description : member 의 description
	 *							- homepage : member 의 homepage URL
	 *							- blog : member 의 blog URL
	 *							- birthday : member 의 birthday
	 *							- gender : member 의 성별
	 *							- country : member 의 contry
	 *							- country_call_code : 국가 전화 번호
	 *							- mobile_phone_number : member 의 휴대폰 번호
	 *							- phone_number : member 의 일반 전화 번호
	 *							- social_type : social 인증을 통해서 가입 할때 social type
	 *							- social_id : social 인증을 통해서 가입 할때 social id
	 */
	public function join($user_info=FALSE) {
		if(!$user_info) { $user_info = array(); }
	
		// 1. member table 에 insert 될 정보를 가져온다
		if(!isset($user_info['user_id']) || !$user_info['user_id']) {
			$user_info['user_id'] = trim($this->input->post2('user_id', TRUE));
		}
		if(!isset($user_info['email_address']) || !$user_info['email_address']) {
			$user_info['email_address'] = trim($this->input->post2('email_address', TRUE));
		}
		if(!isset($user_info['password']) || !$user_info['password']) {
			$user_info['password'] = trim($this->input->post2('password', TRUE));
		}
		if(!isset($user_info['user_name']) || !$user_info['user_name']) {
			$user_info['user_name'] = trim($this->input->post2('user_name', TRUE));
		}
		if(!isset($user_info['nick_name']) || !$user_info['nick_name']) {
			$user_info['nick_name'] = trim($this->input->post2('nick_name', TRUE));
		}
		
		// nick_name 도 mandatory parameter 로 꼭 받아야 하는 것으로 하자!
		// nick_name 이 없으면 user_name 을 nick_name 으로 사용한다.
		//if(!isset($user_info['nick_name']))	{ $user_info['nick_name'] = $user_info['user_name']; }
		
		if(!isset($user_info['find_account_question']) || !$user_info['find_account_question']) {
			$user_info['find_account_question'] = trim($this->input->post2('find_account_question', TRUE));
		}
		if(!isset($user_info['find_account_answer']) || !$user_info['find_account_answer']) {
			$user_info['find_account_answer'] = trim($this->input->post2('find_account_answer', TRUE));
		}
		
		if(!isset($user_info['image_mark']) || !$user_info['image_mark']) {
			$user_info['image_mark'] = trim($this->input->post2('image_mark', TRUE));
		}
		if($user_info['image_mark']) {
			$image_mark_arr = explode(',', $user_info['image_mark']);
			$user_info['image_mark'] = array();
			foreach($image_mark_arr as $value) {
				if(trim($value) != '') {
					array_push($user_info['image_mark'], $value);
				}
			}
		} else {
			$user_info['image_mark'] = FALSE;
		}

		$user_info['password'] = md5($user_info['password']);
		if($user_info['find_account_answer']) {
			$user_info['find_account_answer'] = md5($user_info['find_account_answer']);
		}
		
		if(!isset($user_info['allow_mailing']) || !$user_info['allow_mailing']) {
			$user_info['allow_mailing'] = $this->input->post2('allow_mailing', TRUE);
		}
		if(!isset($user_info['allow_message']) || !$user_info['allow_message']) {
			$user_info['allow_message'] = $this->input->post2('allow_message', TRUE);
		}
		if(!isset($user_info['description']) || !$user_info['description']) {
			$user_info['description'] = $this->input->post2('description', TRUE);
		}
		
		if($user_info['allow_mailing'] && strtoupper($user_info['allow_mailing']) == $this->yes) {
			$user_info['allow_mailing'] = $this->yes;
		} else {
			$user_info['allow_mailing'] = $this->no;
		}
		
		if($user_info['allow_message'] && strtoupper($user_info['allow_message']) == $this->yes) {
			$user_info['allow_message'] = $this->yes;
		} else {
			$user_info['allow_message'] = $this->no;
		}
		
		// 가입시 main value 에 대한 valid 체크
		if(($return_code=$this->validJoinValue($user_info)) != $this->success_code) {
			return $return_code;
		}
		
		// 2. member_extra 테이블에 insert 될 정보를 가져온다.
		if(!isset($user_info['homepage']) || !$user_info['homepage']) {
			$user_info['homepage'] = $this->input->post2('homepage', TRUE);
		}
		if(!isset($user_info['blog']) || !$user_info['blog']) {
			$user_info['blog'] = $this->input->post2('blog', TRUE);
		}
		if(!isset($user_info['birthday']) || !$user_info['birthday']) {
			$user_info['birthday'] = $this->input->post2('birthday', TRUE);
		}
		if(!isset($user_info['gender']) || !$user_info['gender']) {
			$user_info['gender'] = $this->input->post2('gender', TRUE);
		}
		if($user_info['gender'] && strtoupper($user_info['gender']) != 'M') { $user_info['gender'] = 'F'; }
		if(!isset($user_info['country']) || !$user_info['country']) {
			$user_info['country'] = $this->input->post2('country', TRUE);
		}
		
		// 유효성 체크를 하지 않는다.
		if(!isset($user_info['mobile_phone_number']) || !$user_info['mobile_phone_number']) {
			$user_info['mobile_phone_number'] = $this->input->post2('mobile_phone_number', TRUE);
		}
		
		// 유효성 체크를 하지 않는다.
		if(!isset($user_info['phone_number']) || !$user_info['phone_number']) {
			$user_info['phone_number'] = $this->input->post2('phone_number', TRUE);
		}
		
		// 국가 전화 번호
		if(!isset($user_info['country_call_code']) || !$user_info['country_call_code']) {
			$user_info['country_call_code'] = $this->input->post2('country_call_code', TRUE);
		}
		
		if(!isset($user_info['social_type']) || !$user_info['social_type']) {
			$user_info['social_type'] = $this->input->post2('social_type', TRUE);
		}
		if(!isset($user_info['social_id']) || !$user_info['social_id']) {
			$user_info['social_id'] = $this->input->post2('social_id', TRUE);
		}
		
		if(($return_code=$this->validJoinExtraValue($user_info)) != $this->success_code) {
			return $return_code;
		}
		
		// 3. 기본 가입 그룹 정보를 가져온다.
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$this->slave_db->select('group_srl')->from($this->slave_db->dbprefix('member_group'))->where('is_default', 'Y');
		$query = $this->slave_db->get();
		if(!$query || $query->num_rows() <= 0) {
			$err_code = $this->config->item('member_join_select_default_group', 'error_code/member');
			log_message('warn', "join E[$err_code] select member_group default group fail");
			return $err_code;
		}
		$row = $query->row_array();
		$default_member_group = $row['group_srl'];
		$query->free_result();
				
		$c_date = mdate('%Y%m%d%H%i%s');
		
		$data_member_extra = array(
				'homepage'				=> $user_info['homepage'] ? $user_info['homepage'] : '',
				'blog'					=> $user_info['blog'] ? $user_info['blog'] : '',
				'birthday'				=> $user_info['birthday'] ? $user_info['birthday'] : '',
				'gender'				=> $user_info['gender'] ? $user_info['gender'] : '',
				'country'				=> $user_info['country'] ? $user_info['country'] : '',
				'country_call_code'		=> $user_info['country_call_code'] ? $user_info['country_call_code'] : '',
				'mobile_phone_number'	=> $user_info['mobile_phone_number'] ? $user_info['mobile_phone_number'] : '',
				'phone_number'			=> $user_info['phone_number'] ? $user_info['phone_number'] : '',
				'account_social_type'	=> $user_info['social_type'] ? $user_info['social_type'] : '',
				'account_social_id'		=> $user_info['social_id'] ? $user_info['social_id'] : '',
				'c_date'				=> $c_date
			);
		
		// member table 에 insert
		$data_member = array(
				'user_id'				=> $user_info['user_id'],
				'email_address'			=> $user_info['email_address'],
				'user_password'			=> $user_info['password'],
				'user_name'				=> $user_info['user_name'],
				'nick_name'				=> $user_info['nick_name'],
				'find_account_question'	=> $user_info['find_account_question'] ? $user_info['find_account_question'] : '',
				'find_account_answer'	=> $user_info['find_account_answer'] ? $user_info['find_account_answer'] : '',
				'allow_mailing'			=> $user_info['allow_mailing'] ? $user_info['allow_mailing'] : '',
				'allow_message'			=> $user_info['allow_message'] ? $user_info['allow_message'] : '',
				'image_mark'			=> $user_info['image_mark'] ? serialize($user_info['image_mark']) : '',
				'description'			=> $user_info['description'] ? $user_info['description'] : '',
				'change_password_date'	=> $c_date,
				'c_date'				=> $c_date
			);
		
		$error_code = $this->success_code;
		if(!$this->master_db) {
			$this->master_db = $this->load->database('master', TRUE);
			$this->master_db->set_dbprefix($this->table_prefix);
		}
		
		$this->master_db->trans_begin();
		
		// member table 에 insert
		$result = $this->master_db->insert($this->master_db->dbprefix('member'), $data_member);
		if(!$result) {
			$error_code = $this->config->item('member_join_insert_member', 'error_code/member');
			log_message('error', "join E[$error_code] insert member table failed");
		}
		
		// member_extra table 에 insert
		if($result) {
			$member_srl = $this->master_db->insert_id();
			$data_member_extra['member_srl'] = $member_srl;
		
			$result = $this->master_db->insert($this->master_db->dbprefix('member_extra'), $data_member_extra);
			if(!$result) {
				$error_code = $this->config->item('member_join_insert_member_extra', 'error_code/member');
				log_message('error', "join E[$error_code] insert member_extra table failed");
			}
		}
		
		// member table 의 list_order update
		if($result) {
			$this->master_db->where('member_srl', $member_srl);
			$result = $this->master_db->update($this->master_db->dbprefix('member'), array('list_order'=>-$member_srl));
			if(!$result) {
				$error_code = $this->config->item('member_join_update_list_order', 'error_code/member');
				log_message('error', "join E[$error_code] update list_order column in member table failed");
			}
		}
		
		// member_group_member table 에 insert(기본 그룹 가입)
		if($result) {
			$data_member_group = array(
					'group_srl'		=> $default_member_group,
					'member_srl'	=> $member_srl,
					'c_date'		=> $c_date
				);
			$result = $this->master_db->insert($this->master_db->dbprefix('member_group_member'), $data_member_group);
			if(!$result) {
				$error_code = $this->config->item('member_join_insert_member_group_member', 'error_code/member');
				log_message('error', "join E[$error_code] insert member_group_member table failed");
			}
		}
		
		// image_mark 로 사용한 file_srl 을 member 소유로 member_srl 업데이트
		if($result && $user_info['image_mark']) {
			$this->master_db->where_in('file_srl', $user_info['image_mark']);
			$result = $this->master_db->update($this->master_db->dbprefix('files'), array('member_srl'=>$member_srl));
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
	
	/**
	 * 서비스에 로그인 한다.
	 *
	 * @param ret {array} 로그인 후 넘겨 줄 데이터
	 * @param user_id {string} user id. 값이 없으면 post 로 가져온다.
	 * @param password {string} password. 값이 없으면 post 로 가져온다.
	 */
	public function login(&$ret, $user_id=FALSE, $password=FALSE) {
		// user_id 존재 여부
		if(!$user_id) {
			$err_code = $this->config->item('member_invalid_user_id', 'error_code/member');
			log_message('warn', "login E[$err_code] no user_id");
			return $err_code;
		}
		if(strlen($user_id) > 128) {
			$err_code = $this->config->item('member_invalid_user_id', 'error_code/member');
			log_message('warn', "login E[$err_code] invalid user_id[$user_id]. too long max[128]. user length[".strlen($user_id)."]");
			return $err_code;
		}
		
		// password 존재 여부
		if(!$password) {
			$err_code = $this->config->item('member_invalid_password', 'error_code/member');
			log_message('warn', "login E[$err_code] no password");
			return $err_code;
		}
		
		$password = md5($password);
		
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		// member 정보를 가져온다.
		$sql = ' SELECT '.
			   '     A.member_srl as member_srl, A.user_id as user_id, A.email_address as email_address, '.
			   '     A.user_password as user_password, A.user_name as user_name, A.nick_name as nick_name, '.
			   '     A.allow_mailing as allow_mailing, A.allow_message as allow_message, A.image_mark as image_mark, '.
			   '     A.block as block, A.email_confirm as email_confirm, A.limit_date as limit_date, '.
			   '     A.last_login_date as last_login_date, A.change_password_date as change_password_date, '.
			   '     A.c_date as c_date, '.
			   '     B.homepage as homepage, B.blog as blog, B.birthday as birthday, B.gender as gender, '.
			   '     B.country as country, B.country_call_code as country_call_code, '.
			   '     B.mobile_phone_number as mobile_phone_number, B.phone_number as phone_number, '.
			   '     B.account_social_type as account_social_type, B.account_social_id as account_social_id, '.
			   '     B.login_count as login_count, B.serial_login_count as serial_login_count '.
			   ' FROM '.
			   '     ( '.
			   '         SELECT member_srl, user_id, email_address, user_password, user_name, nick_name, '.
			   '             allow_mailing, allow_message, image_mark, block, email_confirm, limit_date, '.
			   '             last_login_date, change_password_date, c_date '.
			   '         FROM '.$this->slave_db->dbprefix('member').
			   '         WHERE user_id = ? '.
			   '     ) A, '.
			   '     '.$this->slave_db->dbprefix('member_extra').' B '.
			   ' WHERE A.member_srl = B.member_srl ';
		$query = $this->slave_db->query($sql, array($user_id));

		if($query->num_rows() <= 0) {
			$err_code = $this->config->item('member_no_user', 'error_code/member');
			log_message('warn', "login E[$err_code] no user_id[$user_id]");
			return $err_code;
		}
		
		$member_info = $query->row_array();
		$query->free_result();
		
		// log key 를 설정
		set_log_key($member_info['user_id']);
		
		// password 체크
		if($password != $member_info['user_password']) {
			$err_code = $this->config->item('member_wrong_password', 'error_code/member');
			log_message('warn', "login E[$err_code] wrong password[$password]");
			return $err_code;
		}
		
		// block 된 유저인지 체크
		if(isset($member_info['block']) && $member_info['block'] == 'Y') {
			$err_code = $this->config->item('member_blocked_user_id', 'error_code/member');
			log_message('warn', "login E[$err_code] blocked user_id[$user_id]");
			return $err_code;
		}
		
		$current_tm = time();
		$last_login_tm = strtotime($member_info['last_login_date']);
		
		// 사용 제한 시간(limit_date) 이 넘었는지 체크
		if(isset($member_info['limit_date']) && $member_info['limit_date']) {
			$use_limit_tm = strtotime($member_info['limit_date']);
			if($current_tm > $use_limit_tm) {
				$err_code = $this->config->item('member_use_limit_date', 'error_code/member');
				log_message('warn', "login E[$err_code] use limited date user_id[$user_id], limited_date[".
						$member_info['limit_date']."]");
				return $err_code;
			}
		}
		
		// member 의 group 정보를 가져온다.
		$sql = ' SELECT '.
			   '     B.group_srl as group_srl, B.title as group_name, B.is_root as is_root '.
			   ' FROM '.
			   '     '.$this->slave_db->dbprefix('member_group_member').' A, '.
			   '     '.$this->slave_db->dbprefix('member_group').' B '.
			   ' WHERE A.member_srl = ? AND A.group_srl = B.group_srl ';
		$query = $this->slave_db->query($sql, array($member_info['member_srl']));
		
		if($query->num_rows() <= 0) {
			$err_code = $this->config->item('member_no_group', 'error_code/member');
			log_message('warn', "login E[$err_code] no group");
			return $err_code;
		}
		
		$member_group_info = array();
		foreach($query->result_array() as $row) {
			array_push($member_group_info, $row);
		}
		$query->free_result();
		
		if(!$this->master_db) {
			$this->master_db = $this->load->database('master', TRUE);
			$this->master_db->set_dbprefix($this->table_prefix);
		}
		
		$this->master_db->trans_begin();
		
		$this->master_db->where_in('member_srl', $member_info['member_srl']);
		$result = $this->master_db->update($this->master_db->dbprefix('member'), 
				array(
					'last_login_date' => mdate('%Y%m%d%H%i%s', $current_tm),
					'u_date' => mdate('%Y%m%d%H%i%s', $current_tm)
				)
			);
		if(!$result) {
			log_message('warn', "login update last_login_date, u_date column in member table failed");
		}
		
		$last_login_date = mdate('%Y%m%d', $last_login_tm);
		$current_date = mdate('%Y%m%d', $current_tm);
		
		if($last_login_date != $current_date) {
			// login_count 를 증가 시킨다.
			$new_login_count = $member_info['login_count'] + 1;
			$new_serial_login_count = 0;
			
			// 로그인 한 날짜가 하루 차이라면
			if((strtotime($current_date)-strtotime($last_login_date)) <= 86400) {
				$new_serial_login_count = $member_info['serial_login_count'] + 1;
			}
			
			// 로그인 총 카운트와 연속 로그인 카운트를 변경 시킨다.
			$this->master_db->where_in('member_srl', $member_info['member_srl']);
			$result = $this->master_db->update($this->master_db->dbprefix('member_extra'), 
					array(
						'login_count' => $new_login_count,
						'serial_login_count' => $new_serial_login_count,
						'u_date' => mdate('%Y%m%d%H%i%s', $current_tm)
					)
				);
			if($result) {
				// member 정보의 로그인 총 카운트, 연속 로그인 카운트 를 현재 로그인 정보로 업데이트 한다.
				$member_info['login_count'] = $new_login_count;
				$member_info['serial_login_count'] = $new_serial_login_count;
			} else {
				log_message('warn', "login update login_count, serial_login_count, udate column in member_extra table failed");
			}
		}

		if($this->master_db->trans_status() === FALSE) {
			$this->master_db->trans_rollback();
		} else {
			$this->master_db->trans_commit();
		}
		
		// 로그인 정보를 session 에 저장한다.
		$this->load->model('common/common_model', 'cmodel');
		$this->cmodel->setMemberInfoSession($member_info, $member_group_info);
		
		log_message('info', 'login complete');
		
		return $this->success_code;
	}
	
	/**
	 * 가입된 멤버 리스트를 구한다.
	 *
	 * @param data {array} member list 가 저장될 array
	 * @param search_value {string} 검색 할 value(원본 파일명 에서 검색 한다)
	 * @param start_row {string} limit 할 start row number
	 * @param row_count {string} limt 할 row count
	 * @param iSortCol {boolean} sort 할 column 분류. 0(유저아이디), 1(유저이메일), 2(유저네임), 3(닉네임), 6(생성일)
	 *                           0, 1, 2, 3, 6 값이 아니면 6 으로 취급 한다.
	 * @param sSortDir {string} order by 방향. asc, desc 값 중 하나.
	 * @return 항상 success_code 를 리턴한다.(row 가 없으면 empty array 값이 사용되기 때문에 항상 성공임)
	 */
	public function getMemberList(&$data, $search_value=FALSE, $start_row=FALSE, $row_count=FALSE,
			$iSortCol=FALSE, $sSortDir=FALSE) {
			
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$data['iTotalRecords'] = '0';
		$data['iTotalDisplayRecords'] = '0';
		$data['aaData'] = array();
		
		// iTotalRecords 값을 가져 온다
		$all_member_count = $this->slave_db->count_all($this->slave_db->dbprefix('member'));
		if($all_member_count <= 0) {
			// 데이터가 없으므로 바로 리턴한다.
			return $this->success_code;
		}
		
		$sLimit = '';
		if($row_count !== FALSE && $start_row !== FALSE) {
			$sLimit = ' LIMIT '.intval($start_row).', '.intval($row_count);
		}

		$sWhere = '';
		if($search_value) {
			$sWhere = " WHERE user_id LIKE '%".$this->slave_db->escape_str($search_value)."%' OR ".
					  "       email_address LIKE '%".$this->slave_db->escape_str($search_value)."%' OR ".
					  "       user_name LIKE '%".$this->slave_db->escape_str($search_value)."%' OR ".
					  "       nick_name LIKE '%".$this->slave_db->escape_str($search_value)."%' ";
		}

		$sOrder = '';
		if($iSortCol !== FALSE && $sSortDir !== FALSE) {
			$sOrder = ' ORDER BY ';
			switch($iSortCol) {
				case 1:		$sOrder .= 'email_address ';	break;
				case 2:		$sOrder .= 'user_name ';		break;
				case 3:		$sOrder .= 'nick_name ';		break;
				case 5:		$sOrder .= 'c_date ';			break;
				default:	$sOrder .= 'user_id ';			break;
			}
			$sOrder .= $sSortDir;
		}
		
		$sql = ' SELECT '.
			   '     user_id, email_address, user_name, nick_name, block, c_date '.
			   ' FROM '.$this->slave_db->dbprefix('member').$sWhere.$sOrder.$sLimit;
        $query = $this->slave_db->query($sql);
	
		if($query->num_rows() <= 0) {
			// 데이터가 없으므로 바로 리턴한다.
			return $this->success_code;
		}
		
		foreach($query->result() as $row) {
			$row->DT_RowId = 'member_'.$row->user_id;
			
			array_push($data['aaData'], $row);
		}
		$query->free_result();
		
		// filter row count 를 가져온다
		$sql = ' SELECT COUNT(1) as count '.
			   ' FROM '.$this->slave_db->dbprefix('member').$sWhere;
		$query = $this->slave_db->query($sql);
		
		$row = $query->row_array();
		$query->free_result();
		
		$data['iTotalRecords'] = $all_member_count.'';	// 총 row 갯수
		$data['iTotalDisplayRecords'] = $row['count'].'';	// filter 된 row 갯수
		
		return $this->success_code;
	}
	
	/**
	 * 유저의 상세 정보를 구한다.
	 *
	 * @param data {&array} 유저의 상세 정보가 저장될 array
	 * @param user_id {string} user id
	 */
	public function getUserInfo(&$data, $user_id=FALSE) {
		if($user_id === FALSE) {
			$error_code = $this->config->item('member_invalid_user_id', 'error_code/member');
			log_message('error', "getUserInfo E[$error_code] no user_id");
			return $error_code;
		}
		
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$sql = ' SELECT '.
			   '     A.member_srl as member_srl, A.user_id as user_id, A.email_address as email_address, '.
			   '     A.user_name as user_name, A.nick_name as nick_name, A.allow_mailing as allow_mailing, '.
			   '     A.allow_message as allow_message, A.image_mark as image_mark, A.block as block, '.
			   '     A.email_confirm as email_confirm, A.limit_date as limit_date, '.
			   '     A.last_login_date as last_login_date, A.change_password_date as change_password_date, '.
			   '     A.c_date as c_date, '.
			   '     B.homepage as homepage, B.blog as blog, B.birthday as birthday, B.gender as gender, '.
			   '     B.country as country, B.country_call_code as country_call_code, B.mobile_phone_number as mobile_phone_number, '.
			   '     B.phone_number as phone_number, B.account_social_type as account_social_type, '.
			   '     B.account_social_id as account_social_id, B.login_count as login_count, '.
			   '     B.serial_login_count as serial_login_count '.
			   ' FROM '.$this->slave_db->dbprefix('member').' A, '.$this->slave_db->dbprefix('member_extra').' B '.
			   ' WHERE A.user_id = ? AND A.member_srl = B.member_srl ';
		$query = $this->slave_db->query($sql, $user_id);
		
		if($query->num_rows() <= 0) {
			$error_code = $this->config->item('member_no_user', 'error_code/member');
			log_message('error', "getUserInfo E[$error_code] no user. user_id[$user_id]");
			return $error_code;
		}
		
		$data = $query->row_array();
		$query->free_result();
		
		if(!$data['limit_date']) { $data['limit_date'] = ''; }
		if(!$data['last_login_date']) { $data['last_login_date'] = ''; }
		if(!$data['change_password_date']) { $data['change_password_date'] = ''; }
		
		if(!$data['homepage']) { $data['homepage'] = ''; }
		if(!$data['blog']) { $data['blog'] = ''; }
		if(!$data['birthday']) { $data['birthday'] = ''; }
		if(!$data['gender']) { $data['gender'] = ''; }
		if(!$data['country']) { $data['country'] = ''; }
		if(!$data['country_call_code']) { $data['country_call_code'] = ''; }
		if(!$data['mobile_phone_number']) { $data['mobile_phone_number'] = ''; }
		if(!$data['phone_number']) { $data['phone_number'] = ''; }
		if(!$data['account_social_type']) { $data['account_social_type'] = ''; }
		if(!$data['account_social_id']) { $data['account_social_id'] = ''; }
		
		// group 명 다국어 매핑
		//$data['group_name'] = $this->cmodel->getTextByLanguage($data['group_name']);
		
		// profile image 찾기
		if($data['image_mark']) {
			$data['image_mark'] = $this->cmodel->getFileImageURL(unserialize($data['image_mark']));
			if($data['image_mark']) { $data['image_mark'] = $this->cmodel->sortImageURLBySize($data['image_mark']); }
			else					{ $data['image_mark'] = new stdClass(); }
		} else {
			$data['image_mark'] = new stdClass();
		}
		
		// group 명을 찾아야 함.(이거 원래 sql join 으로 처리 하려고 했으나, 쓸데없이 sql 문이 복잡해 져서 따로 땜)
		$sql = ' SELECT '.
			   '     B.group_srl as group_srl, B.title as group_name '.
			   ' FROM '.
			   '     '.$this->slave_db->dbprefix('member_group_member').' A, '.
			   '     '.$this->slave_db->dbprefix('member_group').' B '.
			   ' WHERE A.member_srl = ? AND A.group_srl = B.group_srl ';
		$query = $this->slave_db->query($sql, $data['member_srl']);
		
		$data['group_name'] = array();
		
		if($query->num_rows() > 0) {
			$str_arr = array();
			foreach($query->result_array() as $row) {
				array_push($str_arr, $row['group_name']);
			}
			
			// group 명 다국어 매핑
			$str_arr = $this->cmodel->getTextsByLanguage($str_arr);
			
			foreach($str_arr as $value) {
				array_push($data['group_name'], $value);
			}
		}
		
		return $this->success_code;
	}
}
?>