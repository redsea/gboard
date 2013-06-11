<?php
class Blog extends CI_Controller {
	public function index() {
		//echo "Hello world";
		
		
		$data['todo_list'] = array('Clean House', 'Call Mom', 'Run Errands');
		$data['title'] = "My Real Title";
		$data['heading'] = "My Real Heading";
		
/*
		$data = new stdClass();
		$data->title = 'My Title object';
		$data->heading = 'My Heading object';
		$data->message = 'My Message object';
*/
		
/*
		$data = array(
               'title' => 'My Title',
               'heading' => 'My Heading',
               'message' => 'My Message'
          );
*/
		
		$this->load->view('blog/blogview', $data);
	}
	
	public function message($to = 'World') {
		//$this->output->enable_profiler(TRUE);
		echo "Hello {$to}!".PHP_EOL;
		
		$this->load->library('encrypt');
		$msg = 'My secret message';
		$encrypted_string = $this->encrypt->encode($msg);
		echo $encrypted_string;
		echo "\n";
		echo ( ! function_exists('mcrypt_encrypt')) ? 'Nope' : 'Yup';
	}
	
	public function comments() {
		//$this->output->enable_profiler(TRUE);
		
		$this->lang->load('sys_group');
		echo $this->lang->line('sys_group_admin')."<br/>";
		
		
		echo "Look at this<br/>";
		
		log_message('info', 'The purpose of some variable is to provide some value.');
		
		//$this->load->library('unit_test');
		//$this->unit->run('Foo', 'Foo');
		//echo $this->unit->report();
		
		//log_message('info', 'The purpose of some variable is to provide some value.');
	}
	
	public function shoes($sandles, $id) {
		echo $sandles."<br/>";
		echo $id;
	}
	
	public function goModel() {
		//$this->load->model('blog/BlogModel', 'model');
		$data = $this->BlogModel->get_ten_entries();
		
		echo json_encode($data)."<br/>";
		
		$this->load->helper('url');
		
		echo anchor('blog/blog/comments', 'Click Here');
	}
	
/*
	public function _remap($method, $params=array()) {
		$method = '_process_'.$method;
		if(method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		}
		show_404();
	}
	
	public function _output($output) {
		echo "in output<br/>";
		//echo $this->output->cache_expiration;
		echo "[$output]";
	}
*/
	
	private function _show_404() {
		echo "not exist method";
	}
	
	private function _process_index() {
		echo "process_index hello world";
	}
	
	private function _process_shoes($sandles, $id) {
		echo "process_shoes<br/>";
		echo $sandles."<br/>";
		echo $id;
	}
}
?>