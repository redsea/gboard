<?php
/**
 * Controller for service tab menub
 *
 * @author	dhkim94@gmail.com
 */
class Service extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		$this->load->library('myutil');
		
		$this->load->model('common/common_model', 'cmodel');
		$this->load->model('service/service_model', 'model');
		
		$this->config->load('error_code/common', TRUE);
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	/**
	 * service list 를 구한다.
	 * 전체 리스트를 구하지 않고 active 한 항목만 포함된 list 를 구한다.
	 */
	public function service_list1() {
		$this->benchmark->mark('start_service_service_list1');
		
		$result = $this->cmodel->validAuthorization(FALSE, TRUE);
		if($result != $this->success_code) {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'common'
				)
			);
			$this->benchmark->mark('end_service_service_list1');
			log_message('info', 'service_service_list1 T['.
					$this->benchmark->elapsed_time('start_service_service_list1', 'end_service_service_list1').']');
			return;
		}
		
		$service_list = array();
		$result = $this->model->getServiceList1($service_list);
		
		$result_array = array(
				'output' => $this->myutil, 
				'code' => $result,
				'controller' => 'service'
			);
		
		if($result == $this->success_code) {
			$result_array['other'] = array('menu_list'=>$service_list);
		}
		
		$this->load->view('common/output_view', $result_array);
		
		$this->benchmark->mark('end_service_service_list1');
		log_message('info', 'service_service_list1 T['.
				$this->benchmark->elapsed_time('start_service_service_list1', 'end_service_service_list1').']');
	}
	
	/**
	 *
	 */
	public function service_list2() {
		$this->benchmark->mark('start_service_service_list2');
		
		// access_token 체크
		$result = $this->cmodel->validAuthorization(FALSE, TRUE);
		if($result != $this->success_code) {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'common'
				)
			);
			$this->benchmark->mark('end_service_service_list2');
			log_message('info', 'service_service_list2 T['.
					$this->benchmark->elapsed_time('start_service_service_list2', 'end_service_service_list2').']');
			return;
		}
		
		$start_row = $this->input->post2('iDisplayStart');	// 보여줄 row 의 start index
		$row_count = $this->input->post2('iDisplayLength');	// 한 페이지에 보여줄 row count
		$search_value = $this->input->post2('sSearch');		// search value
		
		$iSortCol = $this->input->post2('iSortCol_0');		// sort 할 column number
		$sSortDir = $this->input->post2('sSortDir_0');		// sort 방향(asc, desc)
		
		if($sSortDir === FALSE) { $sSortDir = 'desc'; }
		
		$data = array('sEcho'=>$this->input->post2('sEcho'));
		$result = $this->model->getServiceList2($data, $search_value, $start_row,
				$row_count, intval($iSortCol), $sSortDir);
		
		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'service',
						'other' => $data
					)
			);
		
		$this->benchmark->mark('end_service_service_list2');
		log_message('info', 'service_service_list2 T['.
				$this->benchmark->elapsed_time('start_service_service_list2', 'end_service_service_list2').']');
	}
	
	/**
	 * 전체 메뉴 리스트를 구한다.
	 */
	public function menu_list() {
		$this->benchmark->mark('start_service_menu_list');
		
		// access_token 체크
		$result = $this->cmodel->validAuthorization(FALSE, TRUE);
		if($result != $this->success_code) {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'common'
				)
			);
			$this->benchmark->mark('end_service_menu_list');
			log_message('info', 'service_menu_list T['.
					$this->benchmark->elapsed_time('start_service_menu_list', 'end_service_menu_list').']');
			return;
		}
		
		$start_row = $this->input->post2('iDisplayStart');	// 보여줄 row 의 start index
		$row_count = $this->input->post2('iDisplayLength');	// 한 페이지에 보여줄 row count
		$search_value = $this->input->post2('sSearch');		// search value
		
		$iSortCol = $this->input->post2('iSortCol_0');		// sort 할 column number
		$sSortDir = $this->input->post2('sSortDir_0');		// sort 방향(asc, desc)
		
		if($sSortDir === FALSE) { $sSortDir = 'desc'; }
		
		$data = array('sEcho'=>$this->input->post2('sEcho'));
		$result = $this->model->getMenuList($data, $search_value, $start_row,
				$row_count, intval($iSortCol), $sSortDir);
				
		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'service',
						'other' => $data
					)
			);
		
		$this->benchmark->mark('end_service_menu_list');
		log_message('info', 'service_menu_list T['.
				$this->benchmark->elapsed_time('start_service_menu_list', 'end_service_menu_list').']');
	}
	
	
	/**
	 * service action 처리(menu tree 를 가져온다)
	 *
	 * @param service_id {string} admin tree 의 service id
	 * @param leaf_id {string} 가져올 leaf 의 id. 값이 없으면 root 부터 가져온다.
	 * @param depth {number} 가져올 depth. 값이 없으면 -1 로 설정 한다. -1 이면 max tree depth 인 10으로 설정 된다.
	 */
	public function tree($service_id, $leaf_id=0, $depth=-1) {
		$this->benchmark->mark('start_service_tree');
		
		//$this->load->helper('string');
		//echo random_string('unique');

		// access_token 체크
		$result = $this->cmodel->validAuthorization(FALSE, TRUE);
		if($result != $this->success_code) {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'common'
				)
			);
			$this->benchmark->mark('end_service_tree');
			log_message('info', 'service_tree T['.$this->benchmark->elapsed_time('start_service_tree', 'end_service_tree').']');
			return;
		}
		
		$menu_tree = array();
		$result = $this->model->getMenuTree($menu_tree, $service_id, $leaf_id, $depth);
		
		if($result == $this->success_code) {
			$tree_part['data'] = $menu_tree[$leaf_id]['children'];
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'service',
						'other' => $tree_part
				)
			);
		} else {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'service'
				)
			);
		}
		
		$this->benchmark->mark('end_service_tree');
		log_message('info', 'service_tree T['.$this->benchmark->elapsed_time('start_service_tree', 'end_service_tree').']');
	}
	
	
}
?>