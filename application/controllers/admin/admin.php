<?php
/**
 * Service admin Controller
 *
 * @author	dhkim94@gmail.com
 */
class Admin extends CI_Controller {
	public function index() {
		$this->load->model('admin/admin_model', 'model');
		
		if($this->model->isMemberExist() > 0) {
			
		} else {
			// sites 생성 할 수 있는 폼을 보여 준다.
			$data['title'] = 'Create Site';
			$this->load->view('common/header', $data);
			$this->load->view('admin/create_site');
		}
		
	}
	
/*
	public function hello() {
		$this->load->model('member_group/member_group_model', 'model');
		
		
		
		
		echo $this->model->dhkim();
		
		//echo "hello group";
	}
*/
}
?>