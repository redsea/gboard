<?php
/**
 * Oauth20 Controller
 *
 * oauth20/oauth20 는 route 를 통해서 oauth/[function] 으로 라우팅 된다.
 *
 * @author	dhkim94@gmail.com
 */
class Oauth20 extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		$this->config->load('my_conf/oauth20', TRUE);
		
		$this->config->load('error_code/common', TRUE);
		
		$this->load->library('myutil');
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		$this->load->model('common/common_model', 'cmodel');
		$this->load->model('oauth20/oauth20_model', 'model');
	}

	public function index() {
		echo "oauth20 index";
	}
	
	/**
	 * access_token 을 구하기 위한 code 를 구한다. oauth20 의 authorization code grant 를 변형함.
	 *
	 * 넘겨 받는 파라미터는 다음과 같다.
	 * 1. api_key : 서버에서 생성한 api_key
	 */
	public function authorize() {
		$this->benchmark->mark('start_authorize');
	
		$ret = array();
		$api_key = FALSE;
		
		$connected_ip = $this->input->ip_address();
		if(in_array($connected_ip, $this->config->item('system_api_key_using_ip', 'my_conf/oauth20'))) {
			$api_key = $this->config->item('system_api_key', 'my_conf/oauth20');
		}
				
		$result = $this->model->authorizationRequest($ret, $api_key);
				
		if($result == $this->success_code) {
			$this->load->view(
					'common/output_view', 
					array(
							'output'=>$this->myutil, 
							'code'=>$result,
							'controller' => 'oauth20',
							'other' => $ret
					)
				);
				
		} else {
			$this->load->view(
					'common/output_view', 
					array(
							'output'=>$this->myutil, 
							'code'=>$result,
							'controller' => 'oauth20'
					)
				);
		}
		
		$this->benchmark->mark('end_authorize');
		log_message('info', 'oauth20_authorize T['.$this->benchmark->elapsed_time('start_authorize', 'end_authorize').']');
	}
	
	/**
	 * access_token 을 발급 한다.
	 * 
	 * 넘겨 받는 파라미터는 다음과 같다.
	 * 1. api_key : 서버에서 생성한 api_key
	 * 2. api_secret : 서버에서 생성한 api_secret
	 * 3. code : authorize API 로 발급 한 authorization code
	 */
	public function access_token() {
		$this->benchmark->mark('start_access_token');
		
		$ret = array();
		$api_key = $api_secret =FALSE;
		
		$connected_ip = $this->input->ip_address();
		if(in_array($connected_ip, $this->config->item('system_api_key_using_ip', 'my_conf/oauth20'))) {
			$api_key = $this->config->item('system_api_key', 'my_conf/oauth20');
			$api_secret = $this->config->item('system_api_secret', 'my_conf/oauth20');
		}
		
		$result = $this->model->getAccessToken($ret, $api_key, $api_secret);
		
		if($result == $this->success_code) {
			$this->load->view(
					'common/output_view', 
					array(
							'output'=>$this->myutil, 
							'code'=>$result,
							'controller' => 'oauth20',
							'other' => $ret
					)
				);
		} else {
			$this->load->view(
					'common/output_view', 
					array(
							'output'=>$this->myutil, 
							'code'=>$result,
							'controller' => 'oauth20'
					)
				);
		}
		
		$this->benchmark->mark('end_access_token');
		log_message('info', 'oauth20_access_token T['.$this->benchmark->elapsed_time('start_access_token', 'end_access_token').']');
	}
	
	/**
	 * access_token 의 만료기간을 연장 한다.
	 */
	public function refresh_token() {
		$this->benchmark->mark('start_refresh_token');
	
		$ret = array();
		$api_key = $api_secret =FALSE;
		
		$connected_ip = $this->input->ip_address();
		if(in_array($connected_ip, $this->config->item('system_api_key_using_ip', 'my_conf/oauth20'))) {
			$api_key = $this->config->item('system_api_key', 'my_conf/oauth20');
			$api_secret = $this->config->item('system_api_secret', 'my_conf/oauth20');
		}
		
		$result = $this->model->refreshToken($ret, $api_key, $api_secret);
		
		if($result == $this->success_code) {
			$this->load->view(
					'common/output_view', 
					array(
							'output'=>$this->myutil, 
							'code'=>$result,
							'controller' => 'oauth20',
							'other' => $ret
					)
				);
		} else {
			$this->load->view(
					'common/output_view', 
					array(
							'output'=>$this->myutil, 
							'code'=>$result,
							'controller' => 'oauth20'
					)
				);
		}
		
		$this->benchmark->mark('end_refresh_token');
		log_message('info', 'oauth20_refresh_token T['.$this->benchmark->elapsed_time('start_refresh_token', 'end_refresh_token').']');
	}
	
	/**
	 * 등록된 application list 를 구한다.
	 * jQuery datatable 의 json 데이터 형식으로 출력 된다.
	 */
	public function application_list() {
		$this->benchmark->mark('start_oauth20_application_list');
		
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
			$this->benchmark->mark('end_oauth20_application_list');
			log_message('info', 'oauth20_application_list T['.$this->benchmark->elapsed_time(
					'start_oauth20_application_list', 'end_oauth20_application_list').']');
			return;
		}
		
		$start_row = $this->input->post2('iDisplayStart');	// 보여줄 row 의 start index
		$row_count = $this->input->post2('iDisplayLength');	// 한 페이지에 보여줄 row count
		$search_value = $this->input->post2('sSearch');		// search value
		
		$iSortCol = $this->input->post2('iSortCol_0');		// sort 할 column number
		$sSortDir = $this->input->post2('sSortDir_0');		// sort 방향(asc, desc)
		
		if($sSortDir === FALSE) { $sSortDir = 'desc'; }
		
		if($iSortCol == '4'){ $sSortCol = TRUE; }
		else				{ $sSortCol = FALSE; }
		
		//if($iSortCol !== FALSE) {
			// datatable parameter 를 무시하고 임의로 처리 하도록 한다.
			//$bSort = $this->input->post2('bSortable_'.$iSortCol);		// sort column 할 여부(true, false). string 임.
			//if(strtolower($bSort) == 'true'){ $bSort = TRUE; }
			//else							{ $bSort = FALSE; }
			
			// datatable parameter 를 무시하고 임의로 처리 하도록 한다.
			//$sSortCol = $this->input->post2('mDataProp_'.$iSortCol);	// sort 할 column name. c_date, company_name 중 하나.
			//if(strtolower($sSortCol) == 'c_date')	{ $sSortCol = TRUE; }
			//else									{ $sSortCol = FALSE; }
		//}
		
		$data = array('sEcho'=>$this->input->post2('sEcho'));
		$this->model->getApplicationList($data, $search_value, $start_row, $row_count,
				$sSortCol, $sSortDir);
		
		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'common',
						'other' => $data
				)
			);
		
		$this->benchmark->mark('end_oauth20_application_list');
		log_message('info', 'oauth20_application_list T['.$this->benchmark->elapsed_time(
				'start_oauth20_application_list', 'end_oauth20_application_list').']');
	}
}