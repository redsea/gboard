<?php
/**
 * admin model
 * @author dhkim94@gmail.com
 */
class Common_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$this->config->load('my_conf/common', TRUE);
		$this->config->load('error_code/common', TRUE);
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		$this->slave_db = FALSE;
	}
	
	/**
	 * http request 시에 authorization code 를 확인한다.
	 * 만일 접속 할때 마다 access_token expire 시간을 연장하고 싶다면, 여기에서 하면 된다.
	 * session 값 업데이트 및 table column update 해야 한다.
	 * 
	 * @return 성공하면 성공 코드를 리턴하고, 실패라면 정의 된 에러 코드를 리턴한다.
	 */
	public function validAuthorization() {
		$this->load->helper('date');
		
		$access_token = $this->input->get_request_header(_OAUTH_KEY, TRUE);
		if(!$access_token) {
			$err_code = $this->config->item('common_no_auth', 'error_code/common');
			log_message('warn', "getAuthorization E[$err_code] no header "._OAUTH_KEY."[$access_token]");
			return $err_code;
		}
		
		// 1. access_token session 이 존재하면 
		//    넘겨 받은 access_token 값을 비교하여, 같으면 유효 기간을 체크 해서 인증 성공 여부를 리턴한다.
		$this->load->library('session');
		$access_token_in_session = $this->session->userdata('access_token');
		$access_token_expire_in_session = $this->session->userdata('access_token_expire');
		
		if($access_token_in_session && $access_token_in_session == $access_token) {
			if(mdate('%Y%m%d%H%i%s') > $access_token_expire_in_session) {
				$err_code = $this->config->item('common_invalid_access_token', 'error_code/common');
				log_message('warn', "getAuthorization E[$err_code] invalid access_token[$access_token]. ".
						"access_token_expire[$access_token_expire_in_session]");
				return $err_code;
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
		
		$this->slave_db->select('local_url, network_url, width, height, '.
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
			$element = array();
			
			if(isset($row['local_url']) && $row['local_url']) {
				$element['img'] = $row['local_url'];
			} else {
				$element['img'] = $row['network_url'];
			}
			if($element['img']) {
				$element['img_w'] = $row['width'];
				$element['img_h'] = $row['height'];
			}
			
			if(isset($row['thumbnail_local_url']) && $row['thumbnail_local_url']) {
				$element['thumb'] = $row['thumbnail_local_url'];
			} else {
				$element['thumb'] = $row['thumbnail_network_url'];
			}
			if($element['thumb']) {
				$element['thumb_w'] = $row['thumbnail_width'];
				$element['thumb_h'] = $row['thumbnail_height'];
			}
			
			if($element['img'] || $element['thumb']) {
				array_push($ret, $element);
			}
		}
		$query->free_result();
		
		if(count($ret) > 0) { return $ret; }
		
		return FALSE;
	}
	
	public function saveSessionDataByArray($values) {
		$this->load->library('session');
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
	 *							image_mark				- (x) member.image_mark
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
	 *							site_domain				- (x) site.domain
	 *							site_default_language	- (x) site.default_language
	 *							site_image_mark			- (x) site.image_mark
	 */
	public function setMemberInfoSession($member_info, $member_group_info) {
		$this->load->library('session');
		
		$session_data = array(
				'member_srl'	=> $member_info['member_srl'],
				'user_id'		=> $member_info['user_id'],
				'email_address'	=> $member_info['email_address'],
				'user_name'		=> $member_info['user_name'],
				'nick_name'		=> $member_info['nick_name'],
				'email_confirm'	=> $member_info['email_confirm'],
			);
		
		// XXX 프로필 이미지를 세션에 넣을 필요 없어 보임.
		// 프로필 이미지가 없으면 session 에 넣지 않는다.
		//if($member_info['image_mark']) {
		//	$profile_image = $this->getFileImageURL(unserialize($member_info['image_mark']));
		//	if($profile_image) {
		//		$session_data['profile_image'] = $profile_image;
		//	}
		//}
			
		$session_data['group_srl'] = $member_group_info['group_srl'];
		$session_data['is_root'] = $member_group_info['is_root'];
			
		$this->session->set_userdata($session_data);
		
		log_message('info', 'setMemberInfoSession set member session');
	}
}
?>