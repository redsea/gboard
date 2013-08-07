<?php
/**
 * mlanguage model
 *
 * @author dhkim94@gmail.com
 */
class Mlanguage_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		//$this->load->helper('string');
		//$this->load->helper('date');
		$this->load->model('common/common_model', 'cmodel');
		
		$this->config->load('my_conf/common', TRUE);
		//$this->config->load('my_conf/service', TRUE);
		
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/mlanguage', TRUE);
		
		//$this->yes = $this->config->item('yes', 'my_conf/common');
		//$this->no = $this->config->item('no', 'my_conf/common');
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		$this->master_db = FALSE;
		$this->slave_db = FALSE;
	}
	
	/**
	 * 저장된 텍스트를 변경 한다.
	 * @param code {string} 변경할 다국어 텍스트 name
	 * @param lang {string} 변경할 언어
	 * @param new_value {string} 변경할 새로운 텍스트
	 */
	public function setText($code=FALSE, $lang=FALSE, $new_value='') {
		$lang_code = $this->config->item('support_language', 'my_conf/common');
		
		// 텍스트 변경을 위한 텍스트 name 이 없음
		if(!$code) {
			$err_code = $this->config->item('mlanguage_not_language_name', 'error_code/mlanguage');
			log_message('warn', "setText E[$err_code] not language name");
			return $err_code;
		}
		
		// 텍스트 변경을 위한 언어 코드가 없음
		if(!$lang) {
			$err_code = $this->config->item('mlanguage_not_language_code', 'error_code/mlanguage');
			log_message('warn', "setText E[$err_code] not language code");
			return $err_code;
		}
		
		// 지원 하지 않는 언어에 대해서 수정하려면 에러
		if(!in_array($lang, $lang_code)) {
			$err_code = $this->config->item('mlanguage_not_support_language', 'error_code/mlanguage');
			log_message('warn', "setText E[$err_code] not support language[$lang]");
			return $err_code;
		}
		
		if(strlen($new_value) > 128) {
			$err_code = $this->config->item('mlanguage_too_long_data', 'error_code/mlanguage');
			log_message('warn', "setText E[$err_code] text length too long [".strlen($new_value)."]. max length is 128");
			return $err_code;
		}
		
		if(!$this->master_db) {
			$this->master_db = $this->load->database('master', TRUE);
			$this->master_db->set_dbprefix($this->table_prefix);
		}
		
		if(!$new_value) { $new_value = ''; }
		
		$sql = " UPDATE ".$this->master_db->dbprefix('text_list').
			   " SET `text_value` = '".$this->master_db->escape_str($new_value)."' ".
			   " WHERE lang_code = '".$this->master_db->escape_str($lang)."' AND text_srl = ".
			   "     ( ".
			   "         SELECT `text_srl` ".
			   "         FROM ".$this->master_db->dbprefix('text').
			   "         WHERE `name` = '".$this->master_db->escape_str($code)."'".
			   "     ) ";
		$result = $this->master_db->query($sql);
		
		if(!$result) {
			$err_code = $this->config->item('mlanguage_update_failed', 'error_code/mlanguage');
			log_message('warn', "setText E[$err_code] text update failed. name[$code], lang[$lang], value[$new_value]");
			return $err_code;
		}
		
		return $this->success_code;
	}
	
	/**
	 * 다국어로 설정해 둔 텍스트 리스트를 구한다.
	 *
	 * @param data {array} 텍스트 리스트. list 를 aaData 에 포함 시킨다.
	 * @param search_value {string} 검색(필터링) 할 단어
	 * @param start_row {number} row limit 의 start number
	 * @param row_count {number} row limit 의 row count
	 * @return {number} success_code 만 리턴 된다.
	 */
	public function getTextList(&$data, $search_value=FALSE, $start_row=FALSE, $row_count=FALSE) {
		$lang_code = $this->config->item('support_language', 'my_conf/common');
		
		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$data['iTotalRecords'] = '0';
		$data['iTotalDisplayRecords'] = '0';
		$data['aaData'] = array();
		
		// iTotalRecords 값을 가져 온다
		$all_text_count = $this->slave_db->count_all($this->slave_db->dbprefix('text'));
		if($all_text_count <= 0) {
			// 데이터가 없으므로 바로 리턴한다.
			return $this->success_code;
		}
		
		$sLimit = '';
		if($row_count !== FALSE && $start_row !== FALSE) {
			$sLimit = 'LIMIT '.intval($start_row).', '.intval($row_count);
		}
		
		$sWhere = '';
		if($search_value) {
			$sWhere = " WHERE text_value LIKE '%".$this->slave_db->escape_str($search_value)."%' ";
		}
		
		$sql = ' SELECT '.
			   '     C.name as name, D.lang_code as lang_code, D.text_value as text_value, C.c_date as c_date '.
			   ' FROM '.
			   '     ( '.
			   '         SELECT DISTINCT '.
			   '             B.text_srl as text_srl, B.name as name, B.c_date as c_date '.
			   '         FROM '.
			   '             ( '.
			   '                 SELECT text_srl, lang_code, text_value '.
			   '                 FROM '.$this->slave_db->dbprefix('text_list').$sWhere.
			   '             ) A, '.
			   '             ( '.
			   '                 SELECT text_srl, name, c_date '.
			   '                 FROM '.$this->slave_db->dbprefix('text').
			   '             ) B '.
			   '         WHERE A.text_srl = B.text_srl ORDER BY B.text_srl DESC '.$sLimit.
			   '     ) C, '.
			   '     '.$this->slave_db->dbprefix('text_list').' D '.
			   ' WHERE C.text_srl = D.text_srl';
		$query = $this->slave_db->query($sql);
		
		if($query->num_rows() <= 0) {
			// 데이터가 없으므로 바로 리턴한다.
			return $this->success_code;
		}
		
		$text_data = array();
		foreach($query->result_array() as $row) {
			if(isset($text_data[$row['name']])) {
				$text_data[$row['name']]->text->{$row['lang_code']} = $row['text_value'];
			} else {
				$text_data[$row['name']] = new stdClass();
				$text_data[$row['name']]->text = new stdClass();
			
				$text_data[$row['name']]->name = $row['name'];
				foreach($lang_code as $lang) { $text_data[$row['name']]->text->{$lang} = ''; }
				$text_data[$row['name']]->text->{$row['lang_code']} = $row['text_value'];
				
				$text_data[$row['name']]->DT_RowId = $row['name'];
			}
		}
		$query->free_result();
		
		// 테이블 본문을 채운다
		foreach($text_data as $row) { array_push($data['aaData'], $row); }
		
		// filter row count 를 가져온다
		$sql = ' SELECT COUNT(DISTINCT B.text_srl) as count '.
			   ' FROM '.
			   '     ( '.
			   '         SELECT text_srl, lang_code, text_value '.
			   '         FROM '.$this->slave_db->dbprefix('text_list').$sWhere.
			   '     ) A, '.
			   '     ( '.
			   '         SELECT text_srl, name, c_date '.
			   '         FROM '.$this->slave_db->dbprefix('text').
			   '     ) B '.
			   ' WHERE A.text_srl = B.text_srl';
		$query = $this->slave_db->query($sql);
		
		$row = $query->row_array();
		$query->free_result();
		
		$data['iTotalRecords'] = $all_text_count.'';		// 총 row 갯수
		$data['iTotalDisplayRecords'] = $row['count'].'';	// filter 된 row 갯수
		
		return $this->success_code;
	}
	
	/**
	 * 지원하는 language list 를 구한다.
	 *
	 * @param data {array} language code 가 저장될 array
	 */
	public function getSupportLanguage(&$data) {
		$lang_code = $this->config->item('support_language', 'my_conf/common');

		if(!$this->slave_db) {
			$this->slave_db = $this->load->database('slave', TRUE);
			$this->slave_db->set_dbprefix($this->table_prefix);
		}
		
		$this->slave_db->select('alpha2, description2, image_mark')->from($this->slave_db->dbprefix('language_code'))->where_in('alpha2', $lang_code);
		$query = $this->slave_db->get();
		
		// language table 에 값이 없으면 이름 얻지를 못하기 때문에 code 를 그대로 준다.
		if($query->num_rows() <= 0) {
			log_message('warn', 'not exist language name for lang-codes['.json_encode($lang_code).']');
			foreach($lang_code as $code) {
				$item = array('code'=>$code, 'name'=>$code);
				array_push($data, $item);
			}
			return $this->success_code;
		}
		
		$lang_list = array();
		foreach($query->result_array() as $row) {
			if(!in_array($row['alpha2'], $lang_list)) {
				$element = new stdClass();
				$element->name = $row['description2'];
				
				if($row['image_mark']) {
					$image_mark = $this->cmodel->getFileImageURL(unserialize($row['image_mark']));
					if($image_mark) { $image_mark = $this->cmodel->sortImageURLBySize($image_mark); }
					else			{ $image_mark = new stdClass(); }
				} else {
					$image_mark = new stdClass();
				}
				$element->image_mark = $image_mark;
			
				$lang_list[$row['alpha2']] = $element;
			}
		}
		$query->free_result();
		
		foreach($lang_code as $code) {
			if(array_key_exists($code, $lang_list)) {
				$item = array('code'=>$code, 'name'=>$lang_list[$code]->name, 
						'images'=>$lang_list[$code]->image_mark);
			} else {
				log_message('warn', 'not exist language name for lang-code['.$code.']');
				$item = array('code'=>$code, 'name'=>$code);
			}
			array_push($data, $item);
		}
		
		return $this->success_code;
	}
}
?>