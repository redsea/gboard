<?php
/**
 * Member group Controller
 *
 * member_group/member_group 은 route 를 통해서 mgroup/[function] 으로 라우팅 된다.
 *
 * @author	dhkim94@gmail.com
 */
class Member_Group extends CI_Controller {
	public function index() {
	
		$this->load->helper('html');
		echo meta('Content-type', 'text/html; charset=utf-8');
	
		echo "member group index";
		
		$this->lang->load('default');
		$val = $this->lang->line('default_test');
		
		echo "<br/>$val";
	}
	
	public function hello() {
		$this->load->model('member_group/member_group_model', 'model');
		
		
		
		
		echo $this->model->dhkim();
		
		//echo "hello group";
	}
}
?>