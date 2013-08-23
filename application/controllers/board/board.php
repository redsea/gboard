<?php
/**
 * Controller for admin
 *
 * @author	dhkim94@gmail.com
 */
class Board extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		//$this->config->load('error_code/member', TRUE);
		$this->load->library('myutil');
		
		$this->load->model('common/common_model', 'cmodel');
		//$this->load->model('service/service_model', 'service_model');
		//$this->load->model('mlanguage/mlanguage_model', 'lang_model');
		$this->load->model('board/board_model', 'model');
		
		$this->config->load('error_code/common', TRUE);
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	/**
	 * 접속자의 authorization 을 체크 한다.
	 *
	 * @param action {string} action name
	 * @param bench_mark_start {string} bench mark start string
	 * @param bench_mark_end {string} bench mark end string
	 * @return {boolean} root 권한이고, authorization 체크 성공하면 TRUE, 아니면 FALSE 를 리턴한다.
	 */
	private function isValidAuthorization($action, $bench_mark_start, $bench_mark_end) {
		$member_srl = $this->session->userdata('member_srl');
		
		// 로그인 하지 않은 경우는 사용 못 하도록 한다.
		if(!$member_srl) {
			$data['notice_text'] = $this->cmodel->getTextByLanguage('__usrLang10');
			$this->load->view('common/no_authorization', $data);
			
			$this->benchmark->mark($bench_mark_end);
			log_message('info', $action.' T['.$this->benchmark->elapsed_time($bench_mark_start, $bench_mark_end).']');
			return FALSE;
		}
		
		// access_token 인증을 확인 한다.
		$result = $this->cmodel->validAuthorization(FALSE, TRUE);
		if($result != $this->success_code) {
			$data['notice_text'] = $this->cmodel->getTextByLanguage('__usrLang9');
			$this->load->view('common/no_authorization', $data);
			
			$this->benchmark->mark($bench_mark_end);
			log_message('info', $action.' T['.$this->benchmark->elapsed_time($bench_mark_start, $bench_mark_end).']');
			return FALSE;
		}
		
		return TRUE;
	}
	
	//--------------------------------------------------------
	// 1. HTML 으로 결과가 나오는 function
	//--------------------------------------------------------
	/**
	 * web page 로 document list 를 보여 준다.
	 * 
	 * @param menu_srl {string} board 의 menu id
	 */
	public function index($menu_srl) {
		$this->benchmark->mark('start_board_index');
		
		// 인증 체크
		if(!$this->isValidAuthorization('board_index', 'start_board_index', 'end_board_index')) {
			return;
		}
		
		// 메뉴의 detail 정보를 구한다.
		$menu_info = array();
		$result = $this->model->getBoardInfo($menu_info, $menu_srl);
		
		if($result != $this->success_code) {
			$this->lang->load('error_message/board');
		
			$data['notice_text'] = $this->lang->line($result);
			$this->load->view('common/no_authorization', $data);
			
			$this->benchmark->mark('end_board_index');
			log_message('info', 'board_index T['.$this->benchmark->elapsed_time('start_board_index', 'end_board_index').']');
		
			return;
		}
		
		$data['board_srl'] = $menu_srl;
		$data['board_name'] = $menu_info['menu_name'];
		$data['categories'] = array();
		
		$result = $this->model->getCategories($data['categories'], $menu_srl);
		if($result != $this->success_code) {
			$this->lang->load('error_message/board');
		
			$data['notice_text'] = $this->lang->line($result);
			$this->load->view('common/no_authorization', $data);
			
			$this->benchmark->mark('end_board_index');
			log_message('info', 'board_index T['.$this->benchmark->elapsed_time('start_board_index', 'end_board_index').']');
			
			return;
		}
		
		$this->load->view('board/index', $data);
		
		$this->benchmark->mark('end_board_index');
		log_message('info', 'board_index T['.$this->benchmark->elapsed_time('start_board_index', 'end_board_index').']');
	}
	
	
	//--------------------------------------------------------
	// 2. JSON 으로 결과가 나오는 function
	//--------------------------------------------------------
	/**
	 * 게시판의 카테고리 리스트를 구한다.
	 */	
	public function categories() {
		$this->benchmark->mark('start_board_categories');
		
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
			$this->benchmark->mark('end_board_categories');
			log_message('info', 'board_categories T['.
					$this->benchmark->elapsed_time('start_board_categories', 'end_board_categories').']');
			return;
		}
		
		$menu_srl = $this->input->post2('board_srl');	// category 를 가져올 board 의 serial number(menu_srl 임)
		
		$data = array();
		
		$data['categories'] = array();
		$result = $this->model->getCategories($data['categories'], $menu_srl);
		if($result != $this->success_code) {
			$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'board'
				)
			);
			$this->benchmark->mark('end_board_categories');
			log_message('info', 'board_categories T['.
					$this->benchmark->elapsed_time('start_board_categories', 'end_board_categories').']');
			return;
		}
		
		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'board',
						'other' => $data
					)
			);
		
		$this->benchmark->mark('end_board_categories');
		log_message('info', 'board_categories T['.
				$this->benchmark->elapsed_time('start_board_categories', 'end_board_categories').']');
	}
	
	/**
	 * 게시판에서 document list 를 구한다.
	 */
	public function document_list1($board_srl=FALSE, $category_srl=FALSE) {
		$this->benchmark->mark('start_board_documents');
		
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
			$this->benchmark->mark('end_board_documents');
			log_message('info', 'board_documents T['.
					$this->benchmark->elapsed_time('start_board_documents', 'end_board_documents').']');
			return;
		}
		
		log_message('debug', '=========>here');
		
		if(!$board_srl)		{ $board_srl = $this->input->post2('board_id'); }
		if(!$category_srl)	{ $category_srl = $this->input->post2('category_id'); }
		
		log_message('debug', '=========>board_id['.$board_srl.'], category_srl['.$category_srl.']');
		
		
		$start_row = $this->input->post2('iDisplayStart');	// 보여줄 row 의 start index
		$row_count = $this->input->post2('iDisplayLength');	// 한 페이지에 보여줄 row count
		$search_value = $this->input->post2('sSearch');		// search value
		
		$iSortCol = $this->input->post2('iSortCol_0');		// sort 할 column number
		$sSortDir = $this->input->post2('sSortDir_0');		// sort 방향(asc, desc)
		
		//if($sSortDir === FALSE) { $sSortDir = 'desc'; }
		
		$data = array('sEcho'=>$this->input->post2('sEcho'));
		
		$result = $this->model->getDocumentList($data, $board_srl, $category_srl, 
				$search_value, $start_row, $row_count, intval($iSortCol), $sSortDir);
		
		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'board',
						'other' => $data
					)
			);
		
		$this->benchmark->mark('end_board_documents');
		log_message('info', 'board_documents T['.
				$this->benchmark->elapsed_time('start_board_documents', 'end_board_documents').']');
	}
}