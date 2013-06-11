<?php
/**
 * admin model
 * @author dhkim94@gmail.com
 */
class Admin_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->db->set_dbprefix('gbd_');
		
	}
	
	/**
	 * sites table 에 데이터가 존재하는지 체크 한다.
	 */
	function isMemberExist() {
		return $this->db->count_all($this->db->dbprefix('sites'));
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
		
		
		return "in admin model";
	}
}
?>