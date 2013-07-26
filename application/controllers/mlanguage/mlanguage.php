<?php
/**
 * Language Controller
 *
 * member/member 는 route 를 통해서 member/[function] 으로 라우팅 된다.
 *
 * @author	dhkim94@gmail.com
 */
class Mlanguage extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		$this->config->load('my_conf/common', TRUE);
		
		$this->config->load('error_code/common', TRUE);
		//$this->config->load('error_code/member', TRUE);
		
		$this->load->library('myutil');
		
		$this->load->model('common/common_model', 'cmodel');
		$this->load->model('mlanguage/mlanguage_model', 'model');
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}

	/**
	 * 지원 하는 언어 리스트를 출력 하는 html 페이지를 보여 준다.
	 */	
	public function index() {
		$this->benchmark->mark('start_mlanguage_index');
		
		$result = $this->cmodel->validAuthorization(FALSE, TRUE);
		if($result != $this->success_code) {
			// TODO 에러 web page 를 넣어야 한다.
		
			echo "auth fail";
		}
		
		// 지원하는 언어 리스트를 구한다.
		$lang_list = array('list'=>array());
		$result = $this->model->getSupportLanguage($lang_list['list']);
		$data['language'] = $lang_list['list'];
		
		$this->load->view('mlanguage/index', $data);
		
		$this->benchmark->mark('end_mlanguage_index');
		log_message('info', 'mlanguage_index T['.$this->benchmark->elapsed_time('start_mlanguage_index', 'end_mlanguage_index').']');
	}
	
	/**
	 * 지원하는 언어 리스트를 구한다.(json 출력)
	 */
	public function lang_list() {
		$this->benchmark->mark('start_mlanguage_list');
	
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
			$this->benchmark->mark('end_mlanguage_list');
			log_message('info', 'mlanguage_list T['.$this->benchmark->elapsed_time('start_mlanguage_list', 'end_mlanguage_list').']');
			return;
		}
	
		$lang_list = array('list'=>array());
		$result = $this->model->getSupportLanguage($lang_list['list']);
		
		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'common',
						'other' => $lang_list
				)
			);
		
		$this->benchmark->mark('end_mlanguage_list');
		log_message('info', 'mlanguage_list T['.$this->benchmark->elapsed_time('start_mlanguage_list', 'end_mlanguage_list').']');
	}
}
?>