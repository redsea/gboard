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
	 */
	public function index() {
		$this->benchmark->mark('start_service_list');
		
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
			$this->benchmark->mark('end_service_list');
			log_message('info', 'service_list T['.$this->benchmark->elapsed_time('start_service_list', 'end_service_list').']');
			return;
		}
		
		$service_list = array();
		$result = $this->model->getServiceMenu($service_list);
		
		$result_array = array(
				'output' => $this->myutil, 
				'code' => $result,
				'controller' => 'service'
			);
		
		if($result == $this->success_code) {
			$result_array['other'] = array('menu_list'=>$service_list);
		}
		
		$this->load->view('common/output_view', $result_array);
		
		$this->benchmark->mark('end_service_list');
		log_message('info', 'service_list T['.$this->benchmark->elapsed_time('start_service_list', 'end_service_list').']');
	}
	
	/**
	 * service action 처리(menu tree 를 가져온다)
	 *
	 * @param service_id {string} admin tree 의 service id
	 * @param leaf_id {string} 가져올 leaf 의 id. 값이 없으면 root 부터 가져온다.
	 * @param depth {number} 가져올 depth. 값이 없으면 -1 로 설정 한다. -1 이면 max tree depth 인 10으로 설정 된다.
	 */
	public function tree($service_id, $leaf_id=0, $depth=-1) {
		$this->benchmark->mark('start_service_menu');
		
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
			$this->benchmark->mark('end_service_menu');
			log_message('info', 'service_menu T['.$this->benchmark->elapsed_time('start_service_menu', 'end_service_menu').']');
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
		
		$this->benchmark->mark('end_service_menu');
		log_message('info', 'service_menu T['.$this->benchmark->elapsed_time('start_service_menu', 'end_service_menu').']');
	}
	
	//public
}
?>