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
		//$this->config->load('error_code/service', TRUE);
		
		//$this->yes = $this->config->item('yes', 'my_conf/common');
		//$this->no = $this->config->item('no', 'my_conf/common');
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		//$this->master_db = FALSE;
		$this->slave_db = FALSE;
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