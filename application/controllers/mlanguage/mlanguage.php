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
		//$this->config->load('error_code/mlanguage', TRUE);
		
		$this->load->library('myutil');
		
		$this->load->model('common/common_model', 'cmodel');
		$this->load->model('mlanguage/mlanguage_model', 'model');
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}

	/**
	 * 다국어로 설정되어 있는 텍스트 리스트를 출력 하는 html 페이지를 보여 준다.
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
	 * 다국어로 사용하려고 설정 해둔 텍스트를 수정 한다.
	 * jQuery datatable + jeditable 을 사용하여 수정 하는 것을 지원하는 API 이다.
	 */
	public function text_change() {
		$this->benchmark->mark('start_text_change');
		
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
			$this->benchmark->mark('end_text_change');
			log_message('info', 'mlanguage_text_change T['.
					$this->benchmark->elapsed_time('start_text_change', 'end_text_change').']');
			return;
		}
		
		$code = $this->input->post2('row_id');
		$lang = $this->input->post2('lang');
		$new_value = $this->input->post2('value');
		
		$result = $this->model->setText($code, $lang, $new_value);
		if($result != $this->success_code) {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'mlanguage'
				)
			);
			$this->benchmark->mark('end_text_change');
			log_message('info', 'mlanguage_text_change T['.
					$this->benchmark->elapsed_time('start_text_change', 'end_text_change').']');
			return;
		}
		
		$data = new stdClass();
		$data->name = $code;
		$data->code = $lang;
		$data->text = $new_value;
		
		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'mlanguage',
						'other' => $data
				)
			);
		
		$this->benchmark->mark('end_text_change');
		log_message('info', 'mlanguage_text_change T['.$this->benchmark->elapsed_time('start_text_change', 'end_text_change').']');
	}
	
	/**
	 * 다국어로 사용하려고 설정 해둔 텍스트 리스트를 구한다.(json 출력)
	 * jQuery datatable 의 json 데이터 형식으로 출력 된다.
	 */
	public function text_list() {
		$this->benchmark->mark('start_text_list');
		
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
			$this->benchmark->mark('end_text_list');
			log_message('info', 'mlanguage_text_list T['.
					$this->benchmark->elapsed_time('start_text_list', 'end_text_list').']');
			return;
		}
		
		$start_row = $this->input->post2('iDisplayStart');	// 보여줄 row 의 start index
		$row_count = $this->input->post2('iDisplayLength');	// 한 페이지에 보여줄 row count
		$search_value = $this->input->post2('sSearch');		// search value
		
		$data = array('sEcho'=>$this->input->post2('sEcho'));
		$this->model->getTextList($data, $search_value, $start_row, $row_count);
		
		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'common',
						'other' => $data
				)
			);
		
		$this->benchmark->mark('end_text_list');
		log_message('info', 'mlanguage_text_list T['.$this->benchmark->elapsed_time('start_text_list', 'end_text_list').']');
	}
	
	/**
	 * 지원하는 언어 리스트를 구한다.(json 출력)
	 */
	public function support_lang() {
		$this->benchmark->mark('start_mlanguage_support_lang');
	
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
			$this->benchmark->mark('end_mlanguage_support_lang');
			log_message('info', 'mlanguage_support_lang T['.
					$this->benchmark->elapsed_time('start_mlanguage_support_lang', 'end_mlanguage_support_lang').']');
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
		
		$this->benchmark->mark('end_mlanguage_support_lang');
		log_message('info', 'mlanguage_support_lang T['.
				$this->benchmark->elapsed_time('start_mlanguage_support_lang', 'end_mlanguage_support_lang').']');
	}
}
?>