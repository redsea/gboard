<?php
/**
 * admin model
 * @author dhkim94@gmail.com
 */
class Common_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$this->load->helper('date');
		
		$this->config->load('my_conf/common', TRUE);
		$this->config->load('error_code/common', TRUE);
		$this->config->load('my_conf/oauth20', TRUE);
		
		$this->yes = $this->config->item('yes', 'my_conf/common');
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		$this->master_db = FALSE;
		$this->slave_db = FALSE;
	}
	
	/**
	 * 접근한 유저가 root(admin) 인지 체크 한다.
	 *
	 * @param member_srl {string} FALSE 이면 자동으로 session 에서 값을 가져 온다
	 * @param is_root {string} FALSE 이면 자동으로 session 에서 값을 가져 온다.
	 * @return {string} error code
	 */
	public function isAdmin(&$member_srl=FALSE, $is_root=FALSE) {
		if(!$member_srl) { $member_srl = $this->session->userdata('member_srl'); }
		if(!$member_srl) {
			$error_code = $this->config->item('common_not_logined', 'error_code/common');
			log_message('warn', "getServiceMenu E[$err_code] not logined");
			return $error_code;
		}
		
		if(!$is_root) { $is_root = $this->session->userdata('is_root'); }
		if(!$is_root || strtoupper($is_root) != $this->yes) {
			$error_code = $this->config->item('common_use_only_root', 'error_code/common');
			log_message('warn', "getServiceMenu E[$err_code] have no right. member_srl[$member_srl] is not admin");
			return $error_code;
		}
		
		return $this->success_code;
	}
	
	/**
	 * http request 시에 authorization code 를 확인한다.
	 * 만일 접속 할때 마다 access_token expire 시간을 연장하고 싶다면, 여기에서 하면 된다.
	 * session 값 업데이트 및 table column update 해야 한다.
	 * 
	 * @param access_token {string} 체크할 access_token. 만일 FALSE 이면 http request header 에서 우선 가져온다.
	 *								만일 request header 에 access_token 이 없으면 session 에서 access_token 을 찾는다.
	 * @param auto_update_expire {boolean} access token expire time 을 자동으로 업데이트 할지 여부
	 *									TRUE 라면 본 함수를 호출 할때 마다 expire time 은 최초 상태로 초기화 됨.
	 *									FALSE 라면 초기화 되지 않음
	 * @return {string} 성공하면 성공 코드를 리턴하고, 실패라면 정의 된 에러 코드를 리턴한다.
	 */
	public function validAuthorization($access_token=FALSE, $auto_update_expire=FALSE) {
		if(!$access_token) {
			$access_token = $this->input->get_request_header(_OAUTH_KEY, TRUE);
		}
		if(!$access_token) {
			$access_token = $this->session->userdata('access_token');
		}
		if(!$access_token) {
			$err_code = $this->config->item('common_no_auth', 'error_code/common');
			log_message('warn', "getAuthorization E[$err_code] no access_token in header or session "._OAUTH_KEY);
			return $err_code;
		}
		
		// 1. access_token session 이 존재하면 
		//    넘겨 받은 access_token 값을 비교하여, 같으면 유효 기간을 체크 해서 인증 성공 여부를 리턴한다.
		$access_token_in_session = $this->session->userdata('access_token');
		$access_token_expire_in_session = $this->session->userdata('access_token_expire');
		
		if($access_token_in_session && $access_token_in_session == $access_token) {
			if(mdate('%Y%m%d%H%i%s') > $access_token_expire_in_session) {
				$err_code = $this->config->item('common_invalid_access_token', 'error_code/common');
				log_message('warn', "getAuthorization E[$err_code] invalid access_token[$access_token]. ".
						"access_token_expire[$access_token_expire_in_session]");
				return $err_code;
			}
			
			// 자동 유지 조건을 주면 access_token 확인을 하면 expire_time 이 늘어 나도록 한다.
			// 웹 페이지에서 사용할때 요청 시마다 접속 시간 늘이기 위해서 추가 함.
			// 이런 경우는 access_token 값은 바꾸지 않고 expire_time 만 업데이트 한다.
			if($auto_update_expire) {
				$expire_tm = time() + $this->config->item('access_token_expire_sec', 'my_conf/oauth20');
				$expire_date = mdate('%Y%m%d%H%i%s', $expire_tm);
		
				$data_oauth = array(
					'access_token_expire' => $expire_date,
					'u_date' => mdate('%Y%m%d%H%i%s')
				);
		
				if(!$this->master_db) { $this->master_db = $this->load->database('master', TRUE); }
				$this->master_db->where('access_token', $access_token);
				$result = $this->master_db->update($this->table_prefix.'oauth20_code', $data_oauth);
				if(!$result) {
					$err_code = $this->config->item('common_au_expire_access_token', 'error_code/common');
					log_message('error', "validAuthorization E[$err_code] auto update access_token[$access_token] expire time failed");
					return $err_code;
				}
			
				$this->saveSessionDataByArray(array('access_token_expire' => $expire_date));
			}
			
			return $this->success_code;
		}
		
		// 2. oauth_code table 에서 값을 체크 한다.
		if(!$this->slave_db) { $this->slave_db = $this->load->database('slave', TRUE); }
		
		$data_where = array(
				'access_token' => $access_token,
				'access_token_expire >=' => mdate('%Y%m%d%H%i%s')
			);
		$this->slave_db->select('client_srl, access_token')->from($this->table_prefix.'oauth20_code')->where($data_where);
		$query = $this->slave_db->get();
		
		if($query->num_rows() <= 0) {
			$err_code = $this->config->item('common_invalid_access_token', 'error_code/common');
			log_message('warn', "getAuthorization E[$err_code] invalid access_token[$access_token]");
			return $err_code;
		}
		
		$row = $query->row_array();
		$query->free_result();
		
		// 자동 유지 조건을 주면 access_token 확인을 하면 expire_time 이 늘어 나도록 한다.
		// 웹 페이지에서 사용할때 요청 시마다 접속 시간 늘이기 위해서 추가 함.
		// 이런 경우는 access_token 값은 바꾸지 않고 expire_time 만 업데이트 한다.
		if($auto_update_expire) {
			$expire_tm = time() + $this->config->item('access_token_expire_sec', 'my_conf/oauth20');
			$expire_date = mdate('%Y%m%d%H%i%s', $expire_tm);
		
			$data_oauth = array(
				'access_token_expire' => $expire_date,
				'u_date' => mdate('%Y%m%d%H%i%s')
			);
		
			if(!$this->master_db) { $this->master_db = $this->load->database('master', TRUE); }
			$this->master_db->where('access_token', $access_token);
			$result = $this->master_db->update($this->table_prefix.'oauth20_code', $data_oauth);
			if(!$result) {
				$err_code = $this->config->item('common_au_expire_access_token', 'error_code/common');
				log_message('error', "validAuthorization E[$err_code] auto update access_token[$access_token] expire time failed");
				return $err_code;
			}
			
			$this->saveSessionDataByArray(
				array('access_token' => $access_token, 'access_token_expire' => $expire_date));
		}
		
		return $this->success_code;
	}
	
	/**
	 * files 테이블에서 이미지 파일 id 를 받아, 이미지 정보 array 를 구한다.
     * 만일 여러 이미지가 각각으로 취급되어야 한다면(10개의 게시물에 각각 포함된 이미지 등등)
     * 해당 함수를 사용하지 말고 새로운 함수를 만들어야 한다.
	 *
	 * @param file_srl {array} file_srl array
	 * @return {array} 이미지 파일 정보 array 를 리턴한다.
	 *				   포함된 이미지 정보는 다음과 같다.
	 *				   ['img', 'img_w', 'img_h', 'thumb', 'thumb_w', 'thumb_h']
	 *				   만일 img 가 없다면 img_w, img_h 는 없고, thumb 이 없다면 thumb_w, thumb_h 도 없다.
	 *				   img, thumb 둘 중 하나만 존재 할 수도 있다.
	 */
	public function getFileImageURL($file_srl) {
		if(!$this->slave_db) { $this->slave_db = $this->load->database('slave', TRUE); }
		
		$this->slave_db->select('file_srl, local_url, network_url, width, height, '.
					'thumbnail_local_url, thumbnail_network_url, thumbnail_width, thumbnail_height')
				->from($this->table_prefix.'files')->where_in('file_srl', $file_srl);
		$query = $this->slave_db->get();
		if(!$query || $query->num_rows() <= 0) {
			log_message('warn', "getFileImageURL no user profile image. file_srl[".json_encode($file_srl)."]");
			if($query) { $query->free_result(); }
			return FALSE;
		}
		
		$ret = array();
		foreach($query->result_array() as $row) {
			$element = new stdClass();
			
			if(isset($row['local_url']) && $row['local_url']) {
				$element->img = $row['local_url'];
			} else {
				$element->img = $row['network_url'];
			}
			if($element->img) {
				$element->img_w = $row['width'];
				$element->img_h = $row['height'];
			}
			
			if(isset($row['thumbnail_local_url']) && $row['thumbnail_local_url']) {
				$element->thumb = $row['thumbnail_local_url'];
			} else {
				$element->thumb = $row['thumbnail_network_url'];
			}
			if($element->thumb) {
				$element->thumb_w = $row['thumbnail_width'];
				$element->thumb_h = $row['thumbnail_height'];
			}
			
			if($element->img || $element->thumb) {
				$ret[$row['file_srl']] = $element;
			}
		}
		$query->free_result();
		
		if(count($ret) > 0) { return $ret; }
		
		return FALSE;
	}
	
	/**
	 * getFileImageURL 으로 부터 얻은 file_srl 로 정렬된 array 를 사이즈 형태로 정렬된 것으로 변환 한다.
	 * 만일 동일한 사이즈의 그림이 존재한다면 최초 설정된 값만 유효하고, 이후는 무시 된다.
	 *
	 * @param image_file_info {array} getFileImageURL 으로 부터 얻은 file_srl 로 정렬되어 있는 array
	 * @return size 별로 정렬하여 리턴한다.
	 */
	public function sortImageURLBySize($image_file_info) {
		$ret = array();
		
		foreach($image_file_info as $value) {
			if($value->img) {
				$key = $value->img_w.'x'.$value->img_h;
				if(!array_key_exists($key, $ret)) {
					$ret[$key] = $value->img;
				}
			}
			if($value->thumb) {
				$key = $value->thumb_w.'x'.$value->thumb_h;
				if(!array_key_exists($key, $ret)) {
					$ret[$key] = $value->thumb;
				}
			}
		}
		
		return $ret;
	}
	
	/**
	 * 세션에 원하는 데이터를 저장한다.
	 *
	 * @param values {array} session 에 저장할 데이터를 포함한 array
	 */
	public function saveSessionDataByArray($values) {
		$this->session->set_userdata($values);
		
		log_message('info', 'saveSessionDataByArray save session['.json_encode($values).']');
	}
	
	/**
	 * session 정보를 저장한다. 현재는 필요 할 것이라고 예상 되는 것만 최소한으로 저장 한다.
	 *
	 * @param member_info {array} 포함하고 있는 정보는 다음과 같다. 현재 포함하고 있는 것은 (o) 로 표시 했음.
	 *							member_srl				- (o) member.member_srl
	 *							user_id					- (o) member.user_id
	 *							email_address			- (o) member.email_address
	 *							user_password			- (x) member.user_password
	 *							user_name				- (o) member.user_name
	 *							nick_name				- (o) member.nick_name
	 *							allow_mailing			- (x) member.allow_mailing
	 *							allow_message			- (x) member.allow_message
	 *							image_mark				- (o) member.image_mark
	 *							block					- (x) member.block
	 *							email_confirm			- (o) member.email_confirm
	 *							limit_date				- (x) member.limit_date
	 *							last_login_date			- (x) member.last_login_date
	 *							change_password_date	- (x) member.change_password_date
	 *							c_date					- (x) member.c_date
	 *							homepage				- (x) member_extra.homepage
	 *							blog					- (x) member_extra.blog
	 *							birthday				- (x) member_extra.birthday
	 *							gender					- (x) member_extra.gender
	 *							country					- (x) member_extra.country
	 *							country_call_code		- (x) member_extra.country_call_code
	 *							mobile_phone_number		- (x) member_extra.mobile_phone_number
	 *							phone_number			- (x) member_extra.phone_number
	 *							account_social_type		- (x) member_extra.account_social_type
	 *							account_social_id		- (x) member_extra.account_social_id
	 *							login_count				- (x) member_extra.login_count
	 *							serial_login_count		- (x) member_extra.serial_login_count
	 * @param member_group_info {array} 포함하고 있는 정보는 다음과 같다. 현재 포함하고 있는 것은 (o) 로 표시 했음.
	 *							group_srl				- (o) member_group.group_srl
	 *							title					- (x) member_group.title
	 *							is_root					- (o) member_group.is_root
	 *							group_image_mark		- (x) member_group.image_mark
	 *							index_module_srl		- (x) site.index_module_srl
	 *							site_domain				- (o) site.domain
	 *							site_default_language	- (x) site.default_language
	 *							site_image_mark			- (x) site.image_mark
	 */
	public function setMemberInfoSession($member_info, $member_group_info) {
		$session_data = array(
				'member_srl'	=> $member_info['member_srl'],
				'user_id'		=> $member_info['user_id'],
				'email_address'	=> $member_info['email_address'],
				'user_name'		=> $member_info['user_name'],
				'nick_name'		=> $member_info['nick_name'],
				'email_confirm'	=> $member_info['email_confirm']
			);
		
		// 프로필 이미지가 없으면 session 에 넣지 않는다.
		if($member_info['image_mark']) {
			$profile_image = $this->getFileImageURL(unserialize($member_info['image_mark']));
			if($profile_image) {
				$profile_image = $this->sortImageURLBySize($profile_image);
				$session_data['profile_image'] = $profile_image;
			}
		}
			
		$session_data['group_srl'] = $member_group_info['group_srl'];
		$session_data['is_root'] = $member_group_info['is_root'];
		$session_data['domain'] = $member_group_info['site_domain'];
			
		$this->session->set_userdata($session_data);
		
		log_message('info', 'setMemberInfoSession set member session');
	}
}
?>