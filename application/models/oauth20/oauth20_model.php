<?php
/**
 * oauth20 model
 *
 * @author dhkim94@gmail.com
 */
class Oauth20_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$this->load->helper('string');
		$this->load->helper('date');
		
		$this->config->load('my_conf/oauth20', TRUE);
		$this->config->load('error_code/oauth20', TRUE);
		
		$this->yes = $this->config->item('yes', 'my_conf/common');
		//$this->no = $this->config->item('no', 'my_conf/common');
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		$this->master_db = FALSE;
		$this->slave_db = FALSE;
	}
	
	/**
	 * access_token 발급을 위한 authorization request 를 처리한다.
	 * 결과로 access_token 발급을 위한 authorization code 를 발급 힌다.
	 *
	 * 무조건 authorization code 를 발급 하지 않게 하기 위해서 다음과 같이 한다.
	 * authorization code 재활용을 위해서 좀 복잡하게 해 두었지만, 실제 정상적인 서비스에는 이런 경우가 거의 발생하지 않을 것으로 보인다.
	 * 1. 기존에 발급 되었지만, access_token 요청에 사용하지 않은 authorization code 를 검색한다.(c_date 기준 생성 이후, 2분이 지나도 access_token 요청하지 않은 code)
	 * 2. 1 번으로 값이 존재하면, c_date 를 현재 시간으로 업데이트 하고, 1번으로 얻은 authorization code 를 사용한다.
	 * 3. 1 번으로 값이 존재하지 않으면 authorization code 를 새로 발급 한다.
	 * 4. access_token 발급시에는 authorization code 의 c_date 를 비교해서 적당한 시간내에 같은 authorization code 로 요청한 것에 대한 것은
	 *    모두 access_token 을 발급 해 주어야 한다.(안전빵으로...)
	 */
	public function authorizationRequest(&$ret, $api_key=FALSE) {
		if(!$api_key) { $api_key = trim($this->input->post2('api_key', TRUE)); }
		if(!$api_key) {
			$err_code = $this->config->item('oauth20_no_api_key', 'error_code/oauth20');
			log_message('warn', "authorizationCodeGrant E[$err_code] no api_key[$api_key]");
			return $err_code;
		}
		
		// 만일 authorization_code 가 session 에 들어 있다면 이 값을 그대로 사용한다.
		$authorization_code = $this->session->userdata('authorization_code');
		$client_api_version = $this->session->userdata('client_api_version');
		
		if($authorization_code) {
			$ret['server_api_version'] = $this->config->item('system_api_version', 'my_conf/oauth20');
			$ret['client_api_version'] = $client_api_version;
			$ret['code'] = $authorization_code;
			return $this->success_code;
		}
		
		if(!$this->slave_db) { $this->slave_db = $this->load->database('slave', TRUE); }
		
		$this->slave_db->select('client_srl, api_version, is_using_root')->from($this->table_prefix.'oauth20')->where('api_key', $api_key);
		$query = $this->slave_db->get();
		
		if($query->num_rows() <= 0) {
			$err_code = $this->config->item('oauth20_not_found_api_key', 'error_code/oauth20');
			log_message('warn', "authorizationCodeGrant E[$err_code] not found api_key[$api_key]");
			return $err_code;
		}
		
		$row = $query->row_array();
		$query->free_result();
		
		set_log_key('auth:'.$row['client_srl']);
		
		// 만일 사용한 api key 가 system 용도로 발급 된 것일때는 한번 체크 해 준다.
		// XXX 실제로 기본 system 에서 접속 하는 것은 auth 체크를 하지 않을 것이기 때문에 의미 없음.
		if($row['is_using_root'] == $this->yes) {
			$connected_ip = $this->input->ip_address();
			if(!in_array($connected_ip, $this->config->item('system_api_key_using_ip', 'my_conf/oauth20'))) {
				$err_code = $this->config->item('oauth20_not_permitted_api_key', 'error_code/oauth20');
				log_message('warn', "authorizationCodeGrant E[$err_code] not permitted api_key[$api_key]. connect_ip[$connected_ip]");
				return $err_code;
			}
		}
		
		// api version 체크
		$current_api_version = $this->config->item('system_api_version', 'my_conf/oauth20');
		if($row['api_version'] != $current_api_version) {
			if(!in_array($row['api_version'], $this->config->item('supported_api_version', 'my_conf/oauth20'))) {
				$err_code = $this->config->item('oauth20_not_supported_api_version', 'error_code/oauth20');
				log_message('warn', 'authorizationCodeGrant E[$err_code] not supported api version['.$row['api_version'].']');
				return $err_code;
			}
		}
		
		$ret['server_api_version'] = $current_api_version;
		$ret['client_api_version'] = $row['api_version'];
		
		/*
		// 재사용 하는 것은 일단 보류 하자. access_token 발급 할때 개발 이슈가 너무 많다.
		// access_token 발생시 authorization code 재사용 할 수 있는 로직은 없음(주석 처리도 안함)
		// 발급을 했는데 2분이 지나도 사용하지 않는 authorization code 가 존재하는지 체크
		$authorize_code_reuse_tm = time() - $this->config->item('authorization_code_reuse_sec', 'my_conf/oauth20');
		$authorize_code_reuse_sec = mdate('%Y%m%d%H%i%s', $authorize_code_reuse_tm);
		
		$data_where = array(
				'client_srl' => $row['client_srl'],
				'access_token' => NULL,
				'c_date <=' => $authorize_code_reuse_sec,
				'u_date' => NULL
			);
		$this->slave_db->select('client_srl, authorization_code')->from($this->table_prefix.'oauth20_code')->where($data_where);
		$query = $this->slave_db->get();
		
		// 이러면 재활용 할 수 있는 authorization code 가 존재 한다.
		// 실제 서비스에는 재활용 하는 것이 드물 것이라고 예측 된다.
		$row_count = 0;
		if(($row_count=$query->num_rows()) > 0) {
			// 일단은 내가 사용한다고 찜 한다.
			// 이렇게 하면 재활용 하다가 다시 생성하는데, 그래도 좀 적게 생성 하므로 이렇게 하도록 한다.
			if(!$this->master_db) { $this->master_db = $this->load->database('master', TRUE); }
		
			$row = $query->row_array(mt_rand(0, $row_count-1));
			
			$this->master_db->where(array('client_srl'=>$row['client_srl'], 'authorization_code'=>$row['authorization_code']));
			$result = $this->master_db->update($this->table_prefix.'oauth20_code', array('c_date'=>mdate('%Y%m%d%H%i%s')));
			if(!$result) {
				// 이건 warn 로그만 찍고 넘어 간다.
				log_message('error', "authorizationCodeGrant update c_date column in oauth20_code table for reuse authorization code failed");
			}
			$ret['code'] = $row['authorization_code'];
			return $this->success_code;
		}
		$query->free_result();
		*/
		
		// authorization_code 를 생성한다.
		$ret['code'] = $authorization_code = random_string('unique');
		
		$data_oauth_code = array(
				'client_srl' => $row['client_srl'],
				'authorization_code' => $authorization_code,
				'c_date' => mdate('%Y%m%d%H%i%s')
			);
		
		if(!$this->master_db) { $this->master_db = $this->load->database('master', TRUE); }
		
		$result = $this->master_db->insert($this->table_prefix.'oauth20_code', $data_oauth_code);
		if(!$result) {
			$error_code = $this->config->item('oauth20_create_auth_code', 'error_code/oauth20');
			log_message('error', "authorizationCodeGrant E[$error_code] insert authorization_code in oauth20_code table failed");
			return $err_code;
		}
		
		// 한번 발급된 것은 저장 한다. access_token 발급되면 없앤다.
		$this->load->model('common/common_model', 'cmodel');
		$this->cmodel->saveSessionDataByArray(
				array(
						'authorization_code' => $ret['code'],
						'client_api_version' => $ret['client_api_version']
					)
			);
		
		return $this->success_code;
	}

	/**
	 * authorization code 를 바탕으로 access_token 을 생성한다.
	 * authorization code 재사용 할 수 있는 것은 빠져 있음. 이슈가 너무 많아서 구현 하지 않음.
	 */
	public function getAccessToken(&$ret, $api_key=FALSE, $api_secret=FALSE, $authorization_code=FALSE) {
		if(!$api_key) { $api_key = trim($this->input->post2('api_key', TRUE)); }
		if(!$api_key) {
			$err_code = $this->config->item('oauth20_no_api_key', 'error_code/oauth20');
			log_message('warn', "getAccessToken E[$err_code] no api_key[$api_key]");
			return $err_code;
		}
		
		if(!$api_secret) { $api_secret = trim($this->input->post2('api_secret', TRUE)); }
		if(!$api_secret) {
			$err_code = $this->config->item('oauth20_no_api_secret', 'error_code/oauth20');
			log_message('warn', "getAccessToken E[$err_code] no api_secret[$api_secret]");
			return $err_code;
		}
		
		if(!$authorization_code) { $authorization_code = trim($this->input->post2('code', TRUE)); }
		if(!$authorization_code) {
			$err_code = $this->config->item('oauth20_no_auth_code', 'error_code/oauth20');
			log_message('warn', "getAccessToken E[$err_code] no authorization_code[$authorization_code]");
			return $err_code;
		}
		
		if(!$this->slave_db) { $this->slave_db = $this->load->database('slave', TRUE); }
		
		$sql = ' SELECT '.
			   '     A.client_srl as client_srl, A.api_key as api_key, A.api_secret as api_secret, '.
			   '     A.api_version as api_version, A.is_using_root as is_using_root, '.
			   '     B.authorization_code as authorization_code, B.access_token as access_token, B.c_date as c_date '.
			   ' FROM '.
			   '     ( '.
			   '         SELECT client_srl, api_key, api_secret, api_version, is_using_root '.
			   '         FROM '.$this->table_prefix.'oauth20 '.
			   '         WHERE api_key = ? AND api_secret = ? '.
			   '     ) A, '.
			   '     ( '.
			   '         SELECT client_srl, authorization_code, access_token, c_date '.
			   '         FROM '.$this->table_prefix.'oauth20_code '.
			   '         WHERE authorization_code = ? '.
			   '     ) B '.
			   ' WHERE A.client_srl = B.client_srl ';
		$query = $this->slave_db->query($sql, array($api_key, $api_secret, $authorization_code));
		
		if($query->num_rows() <= 0) {
			$err_code = $this->config->item('oauth20_invalid_data_access_token', 'error_code/oauth20');
			log_message('warn', "getAccessToken E[$err_code] not found data. ".
					"authorization_code[$authorization_code]. api_key[$api_key], api_secret[$api_secret]");
			return $err_code;
		}
		
		$row = $query->row_array();
		$query->free_result();
		
		set_log_key('auth:'.$row['client_srl']);
		
		// 만일 사용한 api key 가 system 용도로 발급 된 것일때는 한번 체크 해 준다.
		// XXX 실제로 기본 system 에서 접속 하는 것은 auth 체크를 하지 않을 것이기 때문에 의미 없음.
		if($row['is_using_root'] == $this->yes) {
			$connected_ip = $this->input->ip_address();
			if(!in_array($connected_ip, $this->config->item('system_api_key_using_ip', 'my_conf/oauth20'))) {
				$err_code = $this->config->item('oauth20_not_permitted_api_key', 'error_code/oauth20');
				log_message('warn', "getAccessToken E[$err_code] not permitted api_key[$api_key]. connect_ip[$connected_ip]");
				return $err_code;
			}
		}
		
		// api version 체크
		$current_api_version = $this->config->item('system_api_version', 'my_conf/oauth20');
		if($row['api_version'] != $current_api_version) {
			if(!in_array($row['api_version'], $this->config->item('supported_api_version', 'my_conf/oauth20'))) {
				$err_code = $this->config->item('oauth20_not_supported_api_version', 'error_code/oauth20');
				log_message('warn', 'getAccessToken E[$err_code] not supported api version['.$row['api_version'].']');
				return $err_code;
			}
		}
		
		$ret['server_api_version'] = $current_api_version;
		$ret['client_api_version'] = $row['api_version'];
		
		if($row['access_token']) {
			$err_code = $this->config->item('oauth20_dissolved_auth_code', 'error_code/oauth20');
			log_message('warn', 'getAccessToken E[$err_code] dissolved authorization code['.$row['authorization_code'].']');
			return $err_code;
		}
		
		// access_token 을 생성한다.
		$access_token = random_string('unique');
		$expire_tm = time() + $this->config->item('access_token_expire_sec', 'my_conf/oauth20');
		$expire_date = mdate('%Y%m%d%H%i%s', $expire_tm);
		
		$data_oauth = array(
				'access_token' => $access_token,
				'access_token_expire' => $expire_date,
				'u_date' => mdate('%Y%m%d%H%i%s')
			);
		
		if(!$this->master_db) { $this->master_db = $this->load->database('master', TRUE); }
		
		$this->master_db->where(array('client_srl'=>$row['client_srl'], 'authorization_code'=>$row['authorization_code']));
		$result = $this->master_db->update($this->table_prefix.'oauth20_code', $data_oauth);
		if(!$result) {
			$err_code = $this->config->item('oauth20_create_access_token', 'error_code/oauth20');
			log_message('error', "getAccessToken E[$err_code] create access_toke failed");
			return $err_code;
		}
		
		$ret['access_token'] = $access_token;
		$ret['expire_in'] = $this->config->item('access_token_expire_sec', 'my_conf/oauth20').'';
		
		// autorization_code 를 세션에서 삭제 한다.
		$this->session->unset_userdata('authorization_code');
		
		// 발급 된 access_token 을 세션에 저장한다.
		$this->load->model('common/common_model', 'cmodel');
		$this->cmodel->saveSessionDataByArray(
				array(
						'access_token' => $ret['access_token'],
						'access_token_expire' => $expire_date
					)
			);
		
		return $this->success_code;
	}
	
	/**
	 * access_token 을 refresh 한다.
	 */
	public function refreshToken(&$ret, $api_key=FALSE, $api_secret=FALSE, $access_token=FALSE) {
		if(!$api_key) { $api_key = trim($this->input->post2('api_key', TRUE)); }
		if(!$api_key) {
			$err_code = $this->config->item('oauth20_no_api_key', 'error_code/oauth20');
			log_message('warn', "refreshToken E[$err_code] no api_key[$api_key]");
			return $err_code;
		}
		
		if(!$api_secret) { $api_secret = trim($this->input->post2('api_secret', TRUE)); }
		if(!$api_secret) {
			$err_code = $this->config->item('oauth20_no_api_secret', 'error_code/oauth20');
			log_message('warn', "refreshToken E[$err_code] no api_secret[$api_secret]");
			return $err_code;
		}
		
		if(!$access_token) { $access_token = trim($this->input->post2('access_token', TRUE)); }
		if(!$access_token) {
			$err_code = $this->config->item('oauth20_no_access_token', 'error_code/oauth20');
			log_message('warn', "refreshToken E[$err_code] no access_token[$access_token]");
			return $err_code;
		}
		
		if(!$this->slave_db) { $this->slave_db = $this->load->database('slave', TRUE); }
		
		$sql = ' SELECT '.
			   '     A.client_srl as client_srl, A.is_using_root as is_using_root, '.
			   '     B.access_token as access_token, B.access_token_expire as access_token_expire '.
			   ' FROM '.
			   '     ( '.
			   '         SELECT client_srl, is_using_root '.
			   '         FROM '.$this->table_prefix.'oauth20 '.
			   '         WHERE api_key = ? AND api_secret = ? '.
			   '     ) A, '.
			   '     ( '.
			   '         SELECT client_srl, access_token, access_token_expire '.
			   '         FROM '.$this->table_prefix.'oauth20_code '.
			   '         WHERE access_token = ? '.
			   '     ) B '.
			   ' WHERE A.client_srl = B.client_srl ';
		$query = $this->slave_db->query($sql, array($api_key, $api_secret, $access_token));
		
		if($query->num_rows() <= 0) {
			$err_code = $this->config->item('oauth20_invalid_data_refresh_token', 'error_code/oauth20');
			log_message('warn', "refreshToken E[$err_code] not found data. ".
					"access_token[$access_token]. api_key[$api_key], api_secret[$api_secret]");
			return $err_code;
		}
		
		$row = $query->row_array();
		$query->free_result();
		
		set_log_key('auth:'.$row['client_srl']);
		
		// 만일 사용한 api key 가 system 용도로 발급 된 것일때는 한번 체크 해 준다.
		// XXX 실제로 기본 system 에서 접속 하는 것은 auth 체크를 하지 않을 것이기 때문에 의미 없음.
		if($row['is_using_root'] == $this->yes) {
			$connected_ip = $this->input->ip_address();
			if(!in_array($connected_ip, $this->config->item('system_api_key_using_ip', 'my_conf/oauth20'))) {
				$err_code = $this->config->item('oauth20_not_permitted_api_key', 'error_code/oauth20');
				log_message('warn', "refreshToken E[$err_code] not permitted api_key[$api_key]. connect_ip[$connected_ip]");
				return $err_code;
			}
		}
		
		$curr_tm = time();
		$saved_expire_tm = strtotime($row['access_token_expire']);
		
		if($curr_tm - $saved_expire_tm > 0) {
			$err_code = $this->config->item('oauth20_expired_access_token', 'error_code/oauth20');
			log_message('warn', "refreshToken E[$err_code] expired access_token[$access_token]");
			return $err_code;
		}
		
		$access_token = random_string('unique');
		$expire_tm = $curr_tm + $this->config->item('access_token_expire_sec', 'my_conf/oauth20');
		$expire_date = mdate('%Y%m%d%H%i%s', $expire_tm);
		
		$data_oauth = array(
				'access_token' => $access_token,
				'access_token_expire' => $expire_date,
				'u_date' => mdate('%Y%m%d%H%i%s', $curr_tm)
			);
		
		if(!$this->master_db) { $this->master_db = $this->load->database('master', TRUE); }
		
		$this->master_db->where(array('client_srl'=>$row['client_srl'], 'access_token'=>$row['access_token']));
		$result = $this->master_db->update($this->table_prefix.'oauth20_code', $data_oauth);
		if(!$result) {
			$err_code = $this->config->item('oauth20_refresh_access_token', 'error_code/oauth20');
			log_message('error', "refreshToken E[$err_code] refresh access_toke failed");
			return $err_code;
		}
		
		$ret['access_token'] = $access_token;
		$ret['expire_in'] = $this->config->item('access_token_expire_sec', 'my_conf/oauth20').'';
		
		// refresh 된 access_token 을 session 에 저장한다.
		$this->load->model('common/common_model', 'cmodel');
		$this->cmodel->saveSessionDataByArray(
				array(
						'access_token' => $ret['access_token'],
						'access_token_expire' => $expire_date
					)
			);
		
		return $this->success_code;
	}
}