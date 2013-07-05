<?php
/**
 * service model
 *
 * @author dhkim94@gmail.com
 */
class Service_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		//$this->load->helper('string');
		//$this->load->helper('date');
		$this->load->model('common/common_model', 'cmodel');
		
		//$this->config->load('my_conf/oauth20', TRUE);
		//$this->config->load('error_code/oauth20', TRUE);
		
		$this->yes = $this->config->item('yes', 'my_conf/common');
		//$this->no = $this->config->item('no', 'my_conf/common');
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		//$this->master_db = FALSE;
		$this->slave_db = FALSE;
	}
	
	/**
	 * admin controller 의 service menu action 처리용 function
	 *
	 * @param service_list {array} service list 가 저장될 장소
	 * @param member_srl {string} FALSE 이면 자동으로 session 에서 값을 가져 온다
	 * @param is_root {string} FALSE 이면 자동으로 session 에서 값을 가져 온다.
	 * @return {string} error code
	 */
	public function getServiceMenu(&$service_list, $member_srl=FALSE, $is_root=FALSE) {
		$result = $this->cmodel->isAdmin($member_srl, $is_root);
		if($result != $this->success_code) { return $result; }
		
		$group_srl = $this->session->userdata('group_srl');
		$domain = $this->session->userdata('domain');
		
		if(!$this->slave_db) { $this->slave_db = $this->load->database('slave', TRUE); }
		
		$sql = ' SELECT '.
		       '     B.service_name as service_name, B.controller as controller, '.
		       '     B.controller_action as controller_action, B.image_mark as image_mark, '.
		       '     A.list_order '.
			   ' FROM '.
			   '     ( '.
			   '         SELECT service_srl, list_order '.
			   '         FROM '.$this->table_prefix.'service_group_service '.
			   '         WHERE group_srl = ? '.
			   '     ) A, '.
			   '     ( '.
			   '         SELECT service_srl, service_name, controller, controller_action, image_mark '.
			   '         FROM '.$this->table_prefix.'service '.
			   '         WHERE is_active = ? '.
			   '     ) B '.
			   ' WHERE A.service_srl = B.service_srl ORDER BY A.list_order ASC ';
		$query = $this->slave_db->query($sql, array($group_srl, $this->yes));
		
		if($query->num_rows() <= 0) {
			log_message('warn', "not exist service for group_srl[$group_srl]");
			return $this->success_code;
		}
		
		foreach($query->result_array() as $row) {
			$row['url'] = 'http://'.$domain.'/'.$row['controller'].DIRECTORY_SEPARATOR.$row['controller_action'];
			
			if(!$row['image_mark']) { $row['site_icon'] = ''; }
			else { $row['site_icon'] = unserialize($row['image_mark']); }
			
			unset($row['controller']);
			unset($row['controller_action']);
			unset($row['list_order']);
			unset($row['image_mark']);
			
			array_push($service_list, $row);
		}
		$query->free_result();
		
		return $this->success_code;
	}


}
?>