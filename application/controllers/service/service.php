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
						'controller' => 'service'
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
}
?>