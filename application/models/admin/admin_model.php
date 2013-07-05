<?php
/**
 * admin model
 * @author dhkim94@gmail.com
 */
class Admin_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		$this->master_db = FALSE;
		$this->slave_db = FALSE;
	}
	
	/**
	 * sites table 에 데이터가 존재하는지 체크 한다.
	 */
	//function isMemberExist() {
	//	return $this->db->count_all($this->db->dbprefix('sites'));
	//}
	

}
?>