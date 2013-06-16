<?php
/**
 * Member Controller
 *
 * member/member 는 route 를 통해서 member/[function] 으로 라우팅 된다.
 *
 * @author	dhkim94@gmail.com
 */
class Mfile extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		$this->config->load('my_conf/file.php', TRUE);
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/mfile', TRUE);
		
		$this->load->library('myutil');
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	public function index() {
		echo "mfile index";
	}
	
	/**
	 * 서버나 원격 디스크(amazon s3 등등)에 파일을 업로드 한다.
	 * local 서버에 저장 디렉토리의 member_srl 디렉토리 하단에 파일로 저장된다.
	 *
	 * 다음의 사항을 지켜야 한다.
	 * 1. form 에서 enctype 이 multipart/form-data 으로 호출 되어야 한다.
	 * 2. input type=file 의 name 이 gboard_file 으로 설정 되어야 한다.(application/config/my_conf/file.php 에서 변경 가능)
	 * 3. post 방식으로 호출 되어야 한다.
	 *
	 * 넘겨 받는 파라미터는 다음과 같다.
	 * 1. gboard_file	: 파일
	 * 2. tid			: transaction id
	 *
	 * 출력되는 값은 다음과 같다.(json 이 출력됨)
	 * 1. 성공
	 *	{
	 *		"error" : "S000001",
	 *		"message" : "성공",
	 *		"tid" : "",
	 *		"file_srl" : "https://s3-ap-northeast-1.amazonaws.com/org.gboard.img/2/ff522befe39d41020abc2d23c32d727a.jpg",
	 *		"width" : "202",
	 *		"height" : "202"
	 *	}
	 *
	 * 2. 실패
	 *	{
	 *		"error" : "E020001",
	 *		"message" : "실패"
	 *	}
	 */
	public function upload() {
		$this->load->model('mfile/mfile_model', 'model');
		
		// 로컬에 파일을 저장한다.
		$file_info = $this->model->save_file_in_local();
		if(!$file_info) {
			$this->load->view(
					'output_view', 
					array(
							'output'=>$this->myutil, 
							'code'=>$this->config->item('mfile_file_upload_fail', 'error_code/mfile'),
							'controller' => 'mfile'
						)
				);
		}
		
		if(!$this->config->item('network_disk_use', 'my_conf/file')) {
			$result_insert = null;
			if(($result_insert=$this->model->save_file_in_db($file_info))) {
				$this->load->view(
						'output_view', 
						array(
							'output' => $this->myutil, 
							'code' => $this->success_code,
							'controller' => 'mfile',
							'other' => array(
									'file_srl' => $result_insert['file_srl'],
									'url' => $result_insert['local_url'],
									'width' => $result_insert['width'].'',
									'height' => $result_insert['height'].''
								)
						)
					);
			} else {
				// table insert 실패
				$this->load->view(
						'output_view', 
						array(
								'output'=>$this->myutil, 
								'code'=>$this->config->item('mfile_insert_table_fail', 'error_code/mfile'),
								'controller' => 'mfile'
						)
					);
			}
			return;
		}
		
		// network disk 에 올리도록 설정 되어 있기 때문에 올린다.
		$result_upload_network_disk = $this->model->save_file_to_network_disk($file_info);
		if($result_upload_network_disk) {
			$result_insert = null;
			if(($result_insert=$this->model->save_file_in_db($file_info, FALSE, FALSE, 
					$result_upload_network_disk))) {
				$this->load->view(
						'output_view', 
						array(
								'output' => $this->myutil, 
								'code' => $this->success_code,
								'controller' => 'mfile',
								'other' => array(
										'file_srl' => $result_insert['file_srl'],
										'url' => $result_insert['network_url'],
										'width' => $result_insert['width'].'',
										'height' => $result_insert['height'].''
									)
						)
				);
			} else {
				// table insert 실패
				$this->load->view(
						'output_view', 
						array(
								'output'=>$this->myutil, 
								'code'=>$this->config->item('mfile_insert_table_fail', 'error_code/mfile'),
								'controller' => 'mfile'
						)
					);
			}
		} else {
			// network disk upload 실패
			$this->load->view(
					'output_view', 
					array(
							'output'=>$this->myutil, 
							'code'=>$this->config->item('mfile_upload_to_network_disk_fail', 'error_code/mfile'),
							'controller' => 'mfile'
					)
				);
		}
		
		// local 에 저장된 파일을 삭제 한다.(이건 디렉토리 삭제 이네....php 기본 unlink 로 바꿈)
		//$this->load->helper('file');
		//$result_delete = delete_files($file_info['full_path']);
		//if(!$result_delete) {
		//	log_message('warn', 'upload failed delete file['.$file_info['full_path'].'] in local-disk');
		//}
		
		// local 에 저장된 파일을 삭제 한다.
		$result_delete = unlink($file_info['full_path']);
		if(!$result_delete) {
			log_message('warn', 'upload failed delete file['.$file_info['full_path'].'] in local-disk');
		}
	}
}
?>