<?php
/**
 * board model
 *
 * @author dhkim94@gmail.com
 */
class Board_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		//$this->load->helper('string');
		//$this->load->helper('date');
		$this->load->model('common/common_model', 'cmodel');
		//$this->load->model('service/service_model', 'service_model');
		
		$this->config->load('my_conf/common', TRUE);
		//$this->config->load('my_conf/service', TRUE);
		
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/board', TRUE);
		
		$this->yes = $this->config->item('yes', 'my_conf/common');
		//$this->no = $this->config->item('no', 'my_conf/common');
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		//$this->master_db = FALSE;
		$this->slave_db = FALSE;
	}
	
	/**
	 * 메뉴의 디테일 정보를 구한다.
	 *
	 * @param data {array} menu detail information 을 저장할 array
	 * @param menu_srl {string|number} 디테일 정보를 구할 menu 의 serial number
	 */
	public function getBoardInfo(&$data, $menu_srl) {
		if(!$menu_srl) {
			$err_code = $this->config->item('board_no_board_srl', 'error_code/board');
			log_message('warn', "getBoardInfo E[$err_code] no menu_srl value");
			return $err_code;
		}
		
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$this->slave_db->select('menu_name, menu_type, menu_controller, menu_action, description')->from($this->slave_db->dbprefix('menus'))->where('menu_srl', $menu_srl);
		$query = $this->slave_db->get();
		
		if($query->num_rows() <= 0) {
			$err_code = $this->config->item('board_invalid_board_srl', 'error_code/board');
			log_message('warn', "getBoardInfo E[$err_code] invalid menu_srl. no board information.");
			return $err_code;
		}
		
		$data = $query->row_array();
		$query->free_result();
		
		$str_arr = array();
		if($data['menu_name'])	{ array_push($str_arr, $data['menu_name']); }
		if($data['description'])	{ array_push($str_arr, $data['description']); }
		
		$str_arr = $this->cmodel->getTextsByLanguage($str_arr);
		
		if($data['menu_name'])	{ $data['menu_name'] = $str_arr[$data['menu_name']]; }
		if($data['description']){ $data['description'] = $str_arr[$data['description']]; }
		
		return $this->success_code;
	}
	
	/**
	 * 게시판의 카테고리 리스트를 가져온다.
	 *
	 * @param data {array} category list 가 저장될 array
	 * @param menu_srl {string|number} board 의 serial number. 실제 menu_srl 임.
	 */
	public function getCategories(&$data, $menu_srl=FALSE) {
		if(!$menu_srl) {
			$err_code = $this->config->item('board_no_board_srl', 'error_code/board');
			log_message('warn', "getCategories E[$err_code] no menu_srl value");
			return $err_code;
		}
		
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$this->slave_db->select('category_srl, menu_srl, category_name, description, list_order, c_date');
		$this->slave_db->from($this->slave_db->dbprefix('board_category'));
		$this->slave_db->where('menu_srl', $menu_srl);
		$this->slave_db->order_by('list_order', 'asc');
		
		$query = $this->slave_db->get();
		
		if($query->num_rows() <= 0) {
			log_message('warn', "getCategories no categories in board_srl(menu_srl)=[$menu_srl]");
			return $this->success_code;
		}
		
		$str_arr = array();
		
		foreach($query->result_array() as $row) {
			if($row['category_name']) {
				array_push($str_arr, $row['category_name']);
			}
			
			if($row['description']) {
				array_push($str_arr, $row['description']);
			}
		
			array_push($data, $row);
		}
		$query->free_result();
		
		$str_arr = $this->cmodel->getTextsByLanguage($str_arr);
		
		foreach($data as $key=>$row) {
			if($data[$key]['category_name']) {
				$data[$key]['category_name'] = $str_arr[$data[$key]['category_name']];
			}
			
			if($data[$key]['description']) {
				$data[$key]['description'] = $str_arr[$data[$key]['description']];
			}
		}
		
		return $this->success_code;
	}
	
	/**
	 * 게시판의 도큐멘트 리스트를 구한다.
	 * datatable 에서 사용하기 위해서 pageing, sort, search 기능도 제공 한다.
	 *
	 * @param data {array} document list 가 저장될 array
	 * @param board_srl {string|number} board 의 srl. menu_srl 임.
	 * @param category_srl {string|number} 게시판의 카테고리 serial number
	 * @param search_value {string} 검색 할 value(제목, 본문, 글쓴이)
	 * @param start_row {string} limit 할 start row number
	 * @param row_count {string} limt 할 row count
	 * @param iSortCol {boolean} sort 할 column 분류. 2(조회수), 3(추천수), 4(등록일)
	 *                           2, 3, 4 값이 아니면 4 로 취급 한다.
	 * @param sSortDir {string} order by 방향. asc, desc 값 중 하나.
	 * @return 항상 success_code 를 리턴한다.(row 가 없으면 empty array 값이 사용되기 때문에 항상 성공임)
	 */
	public function getDocumentList(&$data, $board_srl, $category_srl, 
			$search_value=FALSE, $start_row=FALSE, $row_count=FALSE, $iSortCol=FALSE, $sSortDir=FALSE) {
			
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$data['iTotalRecords'] = '0';
		$data['iTotalDisplayRecords'] = '0';
		$data['aaData'] = array();
		
		// iTotalRecords 값을 가져 온다
		$this->slave_db->where(array('menu_srl'=>$board_srl, 'category_srl'=>$category_srl));
		$this->slave_db->from($this->slave_db->dbprefix('board_document'));
		$all_document_count = $this->slave_db->count_all_results();
		
		if($all_document_count <= 0) {
			// 데이터가 없으므로 바로 리턴한다.
			return $this->success_code;
		}
		
		$sLimit = '';
		if($row_count !== FALSE && $start_row !== FALSE) {
			$sLimit = ' LIMIT '.intval($start_row).', '.intval($row_count);
		}
		
		$sWhere = '';
		if($search_value) {
			$sWhere = " AND ( A.document_title LIKE '%".$this->slave_db->escape_str($search_value)."%' OR ".
					  "       A.document_content LIKE '%".$this->slave_db->escape_str($search_value)."%' OR ".
					  "       B.nick_name LIKE '%".$this->slave_db->escape_str($search_value)."%' ) ";
		}
		
		$sOrder = '';
		if($iSortCol !== FALSE && $sSortDir !== FALSE) {
			$sOrder = ' ORDER BY ';
			switch($iSortCol) {
				case 2:		$sOrder .= 'A.read_count ';		break;
				case 3:		$sOrder .= 'A.like_count ';		break;
				default:	$sOrder .= 'A.c_date ';			break;
			}
			$sOrder .= $sSortDir;
		}
		
		$sql_param = array($board_srl, $category_srl);
		
		$sql = " SELECT ".
			   "     A.document_srl as document_srl, A.document_title as document_title, ".
			   "     B.nick_name as nick_name, A.read_count as read_count, A.like_count as like_count, ".
			   "     A.comment_count as comment_count, A.c_date ".
			   " FROM ".
			   "     ( ".
			   "         SELECT ".
			   "             document_srl, member_srl, menu_srl, category_srl, is_notice, document_title, ".
			   "             document_content, read_count, like_count, file_count, blame_count, ".
			   "             comment_count, secret, ipaddress, block, allow_comment, ".
			   "             list_order, c_date ".
			   "         FROM ".$this->slave_db->dbprefix('board_document').
			   "         WHERE ".
			   "             block != 'Y' AND menu_srl = ? AND category_srl = ? ".
			   "     ) A, ".
			   "     ( ".
			   "         SELECT ".
			   "             member_srl, user_id, email_address, user_name, nick_name, block ".
			   "         FROM ".$this->slave_db->dbprefix('member').
			   "         WHERE block != 'Y' ".
			   "     ) B ".
			   " WHERE ".
			   "     A.member_srl = B.member_srl ".$sWhere.$sOrder.$sLimit;
        $query = $this->slave_db->query($sql, $sql_param);
		
		if($query->num_rows() <= 0) {
			// 데이터가 없으므로 바로 리턴한다.
			return $this->success_code;
		}
		
		foreach($query->result() as $row) {
			$row->DT_RowId = 'document_'.$row->document_srl;
			
			unset($row->document_srl);
			
			array_push($data['aaData'], $row);
		}
		$query->free_result();
		
		// service_name 다국어 변환
		//$str_arr = $this->cmodel->getTextsByLanguage($str_arr);
		//foreach($data['aaData'] as $row) {
		//	$row->service_name = $str_arr[$row->service_name];
		//}
		
		// filter row count 를 가져온다
		$sql = " SELECT COUNT(1) as count ".
			   " FROM ".
			   "     ( ".
			   "         SELECT ".
			   "             document_srl, member_srl, menu_srl, category_srl, is_notice, document_title, ".
			   "             document_content, read_count, like_count, file_count, blame_count, ".
			   "             comment_count, secret, ipaddress, block, allow_comment, ".
			   "             list_order, c_date ".
			   "         FROM ".$this->slave_db->dbprefix('board_document').
			   "         WHERE ".
			   "             block != 'Y' AND menu_srl = ? AND category_srl = ? ".
			   "     ) A, ".
			   "     ( ".
			   "         SELECT ".
			   "             member_srl, user_id, email_address, user_name, nick_name, block ".
			   "         FROM ".$this->slave_db->dbprefix('member').
			   "         WHERE block != 'Y' ".
			   "     ) B ".
			   " WHERE ".
			   "     A.member_srl = B.member_srl ".$sWhere;
		$query = $this->slave_db->query($sql, $sql_param);
		
		$row = $query->row_array();
		$query->free_result();
		
		$data['iTotalRecords'] = $all_document_count.'';	// 총 row 갯수
		$data['iTotalDisplayRecords'] = $row['count'].'';	// filter 된 row 갯수
		
		return $this->success_code;
	}
}