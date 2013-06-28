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
	 */
	public function getAuthorization() {
		// 1. 인증된 서버(local server) 에서 접근 했으면 무조건 성공
		// TODO 넣어야 한다.
		
		
		// 2. session 에 값이 들어 있으면 무조건 성공
		// TODO 넣어야 한다.
	
		
		// 3. oauth_code table 에서 값을 체크 한다.
		$access_token = $this->input->get_request_header(_OAUTH_KEY, TRUE);
		if(!$access_token) {
			$err_code = $this->config->item('common_no_auth', 'error_code/common');
			log_message('warn', "getAuthorization E[$err_code] no header "._OAUTH_KEY."[$access_token]");
			return $err_code;
		}
		
		$this->load->helper('date');
		if(!$this->slave_db) { $this->slave_db = $this->load->database('slave', TRUE); }
		
		$data_where = array(
				'access_token' => $access_token,
				'access_token_expire >=' => mdate('%Y%m%d%h%i%s')
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
}
?>