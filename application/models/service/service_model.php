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
		
		$this->config->load('my_conf/common', TRUE);
		$this->config->load('my_conf/service', TRUE);
		
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/service', TRUE);
		
		$this->yes = $this->config->item('yes', 'my_conf/common');
		//$this->no = $this->config->item('no', 'my_conf/common');
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		//$this->master_db = FALSE;
		$this->slave_db = FALSE;
	}
	
	private function getTreePart(&$leaf_data, $service_srl, $parent_element_srl=FALSE) {
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		// parent_element_srl 이 없으면 값을 가져올 수 없음
		if(count($parent_element_srl) <= 0) { return; }
		
		$parent_element_srl = implode(',', $parent_element_srl);
		
		$sql = ' SELECT '.
		       '     A.element_srl as element_srl, A.menu_srl as menu_id, A.parent_element_srl as parent_element_srl, '.
		       '     B.menu_name as menu_name, B.menu_type as menu_type, '.
		       '     B.menu_controller as menu_controller, B.menu_action as menu_action, B.description as description '.
		       ' FROM '.
		       '     ( '.
		       '         SELECT '.
		       '             element_srl, menu_srl, parent_element_srl, list_order '.
		       '         FROM '.$this->slave_db->dbprefix('menus_tree').
		       '         WHERE service_srl = ? AND parent_element_srl in ('.$parent_element_srl.') '.
		       '     ) A, '.
		       '     ( '.
		       '         SELECT '.
		       '             menu_srl, menu_name, menu_type, menu_controller, menu_action, description '.
		       '         FROM '.$this->slave_db->dbprefix('menus').
		       '     ) B '.
		       ' WHERE A.menu_srl = B.menu_srl ORDER BY A.parent_element_srl, A.list_order ASC ';
		
		// in 을 binding 하니 결과가 이상하다. 그냥 값을 넣자.
		$query = $this->slave_db->query($sql, array($service_srl));
		
		foreach($query->result_array() as $row) {
			$row['parent'] = $row['parent_element_srl'];
			$row['children'] = array();
			
			//unset($row['menu_srl']);
			unset($row['parent_element_srl']);
			
			array_push($leaf_data, $row);
		}
		
		$query->free_result();
	}
	
	/**
	 * active 한 서비스만 포함되어 있는 서비스 리스트를 구한다.
	 * active 이고, member group 권한에 맞는 서비스 리스트를 구한다.
	 *
	 * @param service_list {array} service list 가 저장될 장소
	 * @param member_srl {string} FALSE 이면 자동으로 session 에서 값을 가져 온다
	 * @param is_root {string} FALSE 이면 자동으로 session 에서 값을 가져 온다.
	 * @return {string} error code
	 */
	public function getServiceList1(&$service_list, $member_srl=FALSE, $is_root=FALSE) {
		$group_info = $this->session->userdata('group');
		$group_srl = array();
		foreach($group_info as $row) {
			array_push($group_srl, $row['group_srl']);
		}
		$group_srl = implode(',', $group_srl);
		
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$sql = ' SELECT '.
		       '     B.service_id as service_id, B.service_name as service_name, B.controller as controller, '.
		       '     B.controller_action as controller_action, B.image_mark as image_mark '.
			   ' FROM '.
			   '     ( '.
			   '         SELECT service_srl '.
			   '         FROM '.$this->slave_db->dbprefix('service_group_service').
			   '         WHERE group_srl = ? '.
			   '     ) A, '.
			   '     ( '.
			   '         SELECT service_srl, service_id, service_name, controller, controller_action, image_mark '.
			   '         FROM '.$this->slave_db->dbprefix('service').
			   '         WHERE is_active = ? '.
			   '     ) B '.
			   ' WHERE A.service_srl = B.service_srl ORDER BY B.service_id ASC ';
		$query = $this->slave_db->query($sql, array($group_srl, $this->yes));
		
		if($query->num_rows() <= 0) {
			log_message('warn', "not exist service for group_srl[$group_srl]");
			return $this->success_code;
		}
		
		$str_arr = array();
		$list_data = array();
		
		foreach($query->result_array() as $row) {
			$row['action'] = $row['controller_action'];
			
			if(!$row['image_mark']) { $row['service_icon'] = ''; }
			else { $row['service_icon'] = unserialize($row['image_mark']); }
			
			array_push($str_arr, $row['service_name']);
			
			unset($row['controller_action']);
			unset($row['image_mark']);
			
			$list_data->{$row['controller'].'_'.$row['action']} = $row;
			array_push($list_data, $row);
		}
		$query->free_result();
		
		$str_arr = $this->cmodel->getTextsByLanguage($str_arr);
		
		foreach($list_data as $row) {
			$row['service_name'] = $str_arr[$row['service_name']];
			array_push($service_list, $row);
		}
		
		return $this->success_code;
	}
	
	/**
	 * 서비스 리스트를 구한다. getServiceList1 와는 다르게 모든 서비스가 포함되어 있는 리스트를 구한다.
	 * datatable 에서 사용하기 위해서 pageing, sort, search 기능도 제공 한다.
	 *
	 * @param data {array} member list 가 저장될 array
	 * @param search_value {string} 검색 할 value(원본 파일명 에서 검색 한다)
	 * @param start_row {string} limit 할 start row number
	 * @param row_count {string} limt 할 row count
	 * @param iSortCol {boolean} sort 할 column 분류. 1(서비스명), 2(컨트롤러명), 3(액션명), 5(생성일)
	 *                           1, 2, 3 값이 아니면 5 로 취급 한다.
	 * @param sSortDir {string} order by 방향. asc, desc 값 중 하나.
	 * @return 항상 success_code 를 리턴한다.(row 가 없으면 empty array 값이 사용되기 때문에 항상 성공임)
	 */
	public function getServiceList2(&$data, $search_value=FALSE, $start_row=FALSE, $row_count=FALSE,
			$iSortCol=FALSE, $sSortDir=FALSE) {
			
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$data['iTotalRecords'] = '0';
		$data['iTotalDisplayRecords'] = '0';
		$data['aaData'] = array();
		
		// iTotalRecords 값을 가져 온다
		$all_service_count = $this->slave_db->count_all($this->slave_db->dbprefix('service'));
		if($all_service_count <= 0) {
			// 데이터가 없으므로 바로 리턴한다.
			return $this->success_code;
		}
		
		$sLimit = '';
		if($row_count !== FALSE && $start_row !== FALSE) {
			$sLimit = ' LIMIT '.intval($start_row).', '.intval($row_count);
		}

		$sWhere = '';
		if($search_value) {
			$sWhere = " WHERE controller LIKE '%".$this->slave_db->escape_str($search_value)."%' OR ".
					  "       controller_action LIKE '%".$this->slave_db->escape_str($search_value)."%' ";
		}

		$sOrder = '';
		if($iSortCol !== FALSE && $sSortDir !== FALSE) {
			$sOrder = ' ORDER BY ';
			switch($iSortCol) {
				case 1:		$sOrder .= 'service_name ';			break;
				case 2:		$sOrder .= 'controller ';			break;
				case 3:		$sOrder .= 'controller_action ';	break;
				default:	$sOrder .= 'c_date ';				break;
			}
			$sOrder .= $sSortDir;
		}
		
		$sql = ' SELECT '.
			   '     service_id, service_name, controller, controller_action, is_active, c_date '.
			   ' FROM '.$this->slave_db->dbprefix('service').$sWhere.$sOrder.$sLimit;
        $query = $this->slave_db->query($sql);
	
		if($query->num_rows() <= 0) {
			// 데이터가 없으므로 바로 리턴한다.
			return $this->success_code;
		}
		
		$str_arr = array();
		foreach($query->result() as $row) {
			array_push($str_arr, $row->service_name);
			$row->DT_RowId = 'service_'.$row->service_id;
			array_push($data['aaData'], $row);
		}
		$query->free_result();
		
		// service_name 다국어 변환
		$str_arr = $this->cmodel->getTextsByLanguage($str_arr);
		foreach($data['aaData'] as $row) {
			$row->service_name = $str_arr[$row->service_name];
		}
		
		// filter row count 를 가져온다
		$sql = ' SELECT COUNT(1) as count '.
			   ' FROM '.$this->slave_db->dbprefix('service').$sWhere;
		$query = $this->slave_db->query($sql);
		
		$row = $query->row_array();
		$query->free_result();
		
		$data['iTotalRecords'] = $all_service_count.'';	// 총 row 갯수
		$data['iTotalDisplayRecords'] = $row['count'].'';	// filter 된 row 갯수
		
		return $this->success_code;
	}
	
	/**
	 * 메뉴 리스트를 구한다.
	 * datatable 에서 사용하기 위해서 pageing, sort, search 기능도 제공 한다.
	 *
	 * @param data {array} member list 가 저장될 array
	 * @param search_value {string} 검색 할 value(원본 파일명 에서 검색 한다)
	 * @param start_row {string} limit 할 start row number
	 * @param row_count {string} limt 할 row count
	 * @param iSortCol {boolean} sort 할 column 분류. 2(컨트롤러명), 3(액션명), 5(생성일)
	 *                           2, 3 값이 아니면 5 로 취급 한다.
	 * @param sSortDir {string} order by 방향. asc, desc 값 중 하나.
	 * @return 항상 success_code 를 리턴한다.(row 가 없으면 empty array 값이 사용되기 때문에 항상 성공임)
	 */
	public function getMenuList(&$data, $search_value=FALSE, $start_row=FALSE, $row_count=FALSE,
			$iSortCol=FALSE, $sSortDir=FALSE) {
		
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$data['iTotalRecords'] = '0';
		$data['iTotalDisplayRecords'] = '0';
		$data['aaData'] = array();
		
		// iTotalRecords 값을 가져 온다
		$all_menu_count = $this->slave_db->count_all($this->slave_db->dbprefix('menus'));
		if($all_menu_count <= 0) {
			// 데이터가 없으므로 바로 리턴한다.
			return $this->success_code;
		}

		$sLimit = '';
		if($row_count !== FALSE && $start_row !== FALSE) {
			$sLimit = ' LIMIT '.intval($start_row).', '.intval($row_count);
		}

		$sWhere = '';
		if($search_value) {
			$sWhere = " WHERE menu_controller LIKE '%".$this->slave_db->escape_str($search_value)."%' OR ".
					  "       menu_action LIKE '%".$this->slave_db->escape_str($search_value)."%' ";
		}

		$sOrder = '';
		if($iSortCol !== FALSE && $sSortDir !== FALSE) {
			$sOrder = ' ORDER BY ';
			switch($iSortCol) {
				case 2:		$sOrder .= 'menu_controller ';		break;
				case 3:		$sOrder .= 'menu_action ';			break;
				default:	$sOrder .= 'c_date ';				break;
			}
			$sOrder .= $sSortDir;
		}

		$sql = ' SELECT '.
			   '     menu_srl, menu_name, menu_type, menu_controller, menu_action, description, c_date '.
			   ' FROM '.$this->slave_db->dbprefix('menus').$sWhere.$sOrder.$sLimit;
        $query = $this->slave_db->query($sql);
	
		if($query->num_rows() <= 0) {
			// 데이터가 없으므로 바로 리턴한다.
			return $this->success_code;
		}

		$str_arr = array();
		foreach($query->result() as $row) {
			array_push($str_arr, $row->menu_name);
			array_push($str_arr, $row->description);
			$row->DT_RowId = 'menu_'.$row->menu_srl;
			
			unset($row->menu_srl);
			
			array_push($data['aaData'], $row);
		}
		$query->free_result();
		
		// menu_name, description 다국어 변환
		$str_arr = $this->cmodel->getTextsByLanguage($str_arr);
		foreach($data['aaData'] as $row) {
			$row->menu_name = $str_arr[$row->menu_name];
			$row->description = $str_arr[$row->description];
		}

		// filter row count 를 가져온다
		$sql = ' SELECT COUNT(1) as count '.
			   ' FROM '.$this->slave_db->dbprefix('menus').$sWhere;
		$query = $this->slave_db->query($sql);
		
		$row = $query->row_array();
		$query->free_result();
		
		$data['iTotalRecords'] = $all_menu_count.'';		// 총 row 갯수
		$data['iTotalDisplayRecords'] = $row['count'].'';	// filter 된 row 갯수

		return $this->success_code;
	}

	/**
	 * service id 에 맞는 menu tree 의 내용을 가져온다.
	 * tree 형태로 보여준다.
	 *
	 * @param menu_tree {array} menu tree 값이 들어갈 array
	 * @param service_id {string} menu tree 와 매핑 되는 service id
	 * @param parent_element_srl {string} parent element 의 srl. 값이 없으면 root(parent_srl 이 0) 로 디폴트 값을 가진다.
	 * @param depth_count {number} 가져올 tree 의 depth. parent element 의 depth 로 부터 $depth_count 만큼 가져온다.
	 *                             -1 이면 전체 depth 를 가져온다.
	 */
	public function getMenuTree(&$menu_tree, $service_id=FALSE, $parent_element_srl=FALSE, $depth_count=1) {
		if(!$service_id) {
			$err_code = $this->config->item('service_no_service_id', 'error_code/service');
			log_message('warn', "getMenuTree E[$err_code] no service_id value");
			return $err_code;
		}
		
		// 받은 depth_count 가 -1 이면 max tree depth 인 10으로 설정 한다.
		if($depth_count == -1) {
			$depth_count = $this->config->item('tree_max_depth', 'my_conf/service');
		}
		
		$children = array();
		
		if(!$parent_element_srl) {
			$parent_element_srl = array(0);
			$menu_tree[0] = array('element_srl'=>0, 'children'=>array());
			$children[0] = &$menu_tree[0]['children'];
		} else {
			$menu_tree[$parent_element_srl] = array('element_srl'=>$parent_element_srl, 'children'=>array());
			$children[$parent_element_srl] = &$menu_tree[$parent_element_srl]['children'];
			$parent_element_srl = array($parent_element_srl);
		}
		
		// service_id 에 맞는 service_srl 을 구한다.
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$this->slave_db->select('service_srl')->from($this->slave_db->dbprefix('service'))->where('service_id', $service_id);
		$query = $this->slave_db->get();
		
		if($query->num_rows() <= 0) {
			$err_code = $this->config->item('service_invalid_service_id', 'error_code/service');
			log_message('warn', "getMenuTree E[$err_code] invalid service_id");
			return $err_code;
		}
		
		$row = $query->row_array();
		$query->free_result();
		$service_srl = $row['service_srl'];
		
		// 최초 넘겨 받은 menu tree parent element_srl 이 존재하는지 체크 한다.
		if($parent_element_srl[0] != 0) {
			$this->slave_db->where(array('service_srl'=>$service_srl, 'menu_srl'=>$parent_element_srl[0]));
			$this->slave_db->from($this->slave_db->dbprefix('menus_tree'));
			$parent_element_count = $this->slave_db->count_all_results();
			
			if($parent_element_count <= 0) {
				$err_code = $this->config->item('service_no_tree_element', 'error_code/service');
				log_message('warn', "getMenuTree E[$err_code] not exist parent_element_id[".$parent_element_srl[0]."]");
				return $err_code;
			}
		}
		
		$tree_data = array();
		
		for($i=0 ; $i<$depth_count ; $i++) {
			$leaf_data = array();
			$this->getTreePart($leaf_data, $service_srl, $parent_element_srl);
			
			// leaf data 가 없으면 더 이상 depth 를 내려 갈 수 없음. 중단
			if(count($leaf_data) <= 0) { break; }
			
			$parent_element_srl = array();
			foreach($leaf_data as $leaf) {
				array_push($parent_element_srl, $leaf['element_srl']);
				$tree_data[$leaf['element_srl']] = $leaf;
			}
		}
		
		if(count($tree_data) > 0) {
			// menu name, menu description 다국어 매핑
			$menu_names = array();
			foreach($tree_data as $value) {
				array_push($menu_names, $value['menu_name']);
				array_push($menu_names, $value['description']);
			}
			$menu_names = $this->cmodel->getTextsByLanguage($menu_names);
			
			foreach($tree_data as $value) {
				// 다국어로 되어 있는 값을 실제 값으로 가져 온다.
				$value['menu_name'] = $menu_names[$value['menu_name']];
				$value['description'] = $menu_names[$value['description']];
			
				if(!array_key_exists($value['parent'], $children)) {
					$children[$value['parent']] = &$value['children'];
				} else {
					array_push($children[$value['parent']], $value);
					
					if(!array_key_exists($value['element_srl'], $children)) {
						$children[$value['element_srl']] = 
							&$children[$value['parent']][count($children[$value['parent']])-1]['children'];
					}
				}
			}
		}
		
		return $this->success_code;
	}
}
?>