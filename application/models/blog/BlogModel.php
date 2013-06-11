<?php
class BlogModel extends CI_Model {
	function __construct() {
		parent::__construct();
	}
	
	function get_ten_entries() {
		return array(
			'title'=>'title from model',
			'heading'=>'heading from model');
	}
}
?>