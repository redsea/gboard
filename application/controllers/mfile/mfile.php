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
		
		$this->config->load('my_conf/mfile', TRUE);
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/mfile', TRUE);
		
		$this->load->model('common/common_model', 'cmodel');
		$this->load->model('mfile/mfile_model', 'model');
		
		$this->load->library('myutil');
		
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	public function index() {
		echo "mfile index";
	}
	
	/**
	 * file_srl 로 서버에 올려진 file 의 detail 정보를 구한다.
	 */
	public function file_detail() {
		$this->benchmark->mark('start_mfile_file_detail');
		
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
			$this->benchmark->mark('end_mfile_file_detail');
			log_message('info', 'mfile_file_detail T['.$this->benchmark->elapsed_time(
					'start_mfile_file_detail', 'end_mfile_file_detail').']');
			return;
		}
		
		$file_srl = $this->input->post2('file_id');
		
		$data = array();
		$result = $this->model->getFileInfo($data, $file_srl);
		if($result != $this->success_code) {
			$this->load->view(
					'common/output_view', 
					array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'mfile'
					)
				);
		} else {
			$this->load->view(
					'common/output_view', 
					array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'mfile',
						'other' => $data
					)
				);
		}
		
		$this->benchmark->mark('end_mfile_file_detail');
		log_message('info', 'mfile_file_detail T['.
				$this->benchmark->elapsed_time('start_mfile_file_detail', 'end_mfile_file_detail').']');
	}
	
	/**
	 * 로컬 서버나 네트웍 서버에 저장된 파일 리스트를 구한다.
	 */
	public function file_list() {
		$this->benchmark->mark('start_mfile_file_list');
	
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
			$this->benchmark->mark('end_mfile_file_list');
			log_message('info', 'mfile_file_list T['.$this->benchmark->elapsed_time(
					'start_mfile_file_list', 'end_mfile_file_list').']');
			return;
		}

		$start_row = $this->input->post2('iDisplayStart');	// 보여줄 row 의 start index
		$row_count = $this->input->post2('iDisplayLength');	// 한 페이지에 보여줄 row count
		$search_value = $this->input->post2('sSearch');		// search value
		
		$iSortCol = $this->input->post2('iSortCol_0');		// sort 할 column number
		$sSortDir = $this->input->post2('sSortDir_0');		// sort 방향(asc, desc)
		
		if($sSortDir === FALSE) { $sSortDir = 'desc'; }
		
		$data = array('sEcho'=>$this->input->post2('sEcho'));
		$this->model->getFileList($data, $search_value, $start_row, $row_count,
				intval($iSortCol), $sSortDir);

		$this->load->view(
				'common/output_view', 
				array(
						'output'=>$this->myutil, 
						'code'=>$result,
						'controller' => 'mfile',
						'other' => $data
					)
			);
	
		$this->benchmark->mark('end_mfile_file_list');
		log_message('info', 'mfile_file_list T['.$this->benchmark->elapsed_time('start_mfile_file_list', 'end_mfile_file_list').']');
	}
	
	/**
	 * 서버나 원격 디스크(amazon s3 등등)에 이미지 파일을 업로드 한다.
	 * local 서버에 저장 디렉토리의 member_srl 디렉토리 하단에 파일로 저장된다.
	 *
	 * 다음의 사항을 지켜야 한다.
	 * 1. form 에서 enctype 이 multipart/form-data 으로 호출 되어야 한다.
	 * 2. input type=file 의 name 이 gboard_file 으로 설정 되어야 한다.(application/config/my_conf/file.php 에서 변경 가능)
	 * 3. post 방식으로 호출 되어야 한다.
	 *
	 * 넘겨 받는 파라미터는 다음과 같다.
	 * 1. gboard_file		: 파일
	 * 2. tid				: transaction id
	 * 3. thumbnail			: y, Y 인 경우는 thumbnail 을 생성하고 아니면 생성하지 않는다.(물론 이미지 파일에 한해...)
	 * 4. thumbnail_type	: 생성 할 thumbnail 의 type. crop 인 경우는 crop 으로 생성하고, 아니면 ratio 로 생성한다.
	 * 5. thumbnail_width	: 생성 할 thumbnail 의 너비(서버 설정된 최대 너비 보다 크면 width, height 를 서버 설정 값으로 강제 변경한다)
	 * 6. thumbnail_height	: 생성 할 thumbnail 의 높이(서버 설정된 최대 높이 보다 크면 width, height 를 서버 설정 값으로 강제 변경한다)
	 *
	 * 출력되는 값은 다음과 같다.(json 이 출력됨)
	 * 1. 성공
	 *	{
     *		"error": "S000001",
     *		"message": "\uc131\uacf5",
     *		"tid": "10",
     *		"file_srl": 15,
     *		"url": "https:\/\/s3-ap-northeast-1.amazonaws.com\/org.gboard.img\/2\/aa87315d555efe4d8105e1f648680fe4.jpg",
     *		"width": "202",
     *		"height": "202",
     *		"thumbnail_url": "https:\/\/s3-ap-northeast-1.amazonaws.com\/org.gboard.img\/2\/aa87315d555efe4d8105e1f648680fe4_thumb.jpg",
     *		"thumbnail_width": 200,
     *		"thumbnail_height": 200
     *	}
	 *
	 * 2. 실패
	 *	{
	 *		"error" : "E020001",
	 *		"message" : "실패"
	 *	}
	 */
	public function upload() {
		$this->benchmark->mark('start_upload');
		
		$file_type = $this->input->post2('type', TRUE);
		
		// 로컬에 파일을 저장한다.
		$file_info = array();
		$result = $this->model->saveFileInLocal($file_info, FALSE, $file_type);
		if($result != $this->success_code) {
			$this->load->view(
					'common/output_view', 
					array(
							'output' => $this->myutil, 
							'code' => $result,
							'controller' => 'mfile'
						)
				);
			$this->benchmark->mark('end_upload');
			log_message('info', 'upload T['.$this->benchmark->elapsed_time('start_upload', 'end_upload').']');
			return;
		}
		
		// thumbnail 을 생성할 조건이 있는지 체크 해서 thumbnail 을 생성 한다.
		$is_make_thumbnail = $this->input->post2('thumbnail', TRUE);
		$thumbnail_info = array();
		if($is_make_thumbnail && strtoupper($is_make_thumbnail) == 'Y') {
			$result = $this->model->saveThumbnailFileInLocal($thumbnail_info, $file_info, FALSE, $file_type);
			if($result != $this->success_code) {
				$this->load->view(
						'common/output_view', 
						array(
								'output' => $this->myutil, 
								'code' => $result,
								'controller' => 'mfile'
							)
					);
				$this->benchmark->mark('end_upload');
				log_message('info', 'upload T['.$this->benchmark->elapsed_time('start_upload', 'end_upload').']');
				return;
			}
		}
		
		$file_config = $this->config->item($file_type, 'my_conf/mfile');
		if(!$file_config['network_disk_use']) {
			$result_insert = array();
			$result = $this->model->saveFileInDB($result_insert, $file_info, $thumbnail_info, FALSE, $file_type);
			if($result == $this->success_code) {
				$this->load->view(
						'common/output_view', 
						array(
							'output' => $this->myutil, 
							'code' => $this->success_code,
							'controller' => 'mfile',
							'other' => array(
									'file_srl' => $result_insert['file_srl'],
									'url' => $result_insert['local_url'],
									'width' => $result_insert['width'] ? $result_insert['width'].'' : '0',
									'height' => $result_insert['height'] ? $result_insert['height'].'' : '0',
									'thumbnail_url' => $result_insert['thumbnail_local_url'],
									'thumbnail_width' => $result_insert['thumbnail_width'],
									'thumbnail_height' => $result_insert['thumbnail_height']
								)
						)
					);
			} else {
				$this->load->view(
						'common/output_view', 
						array(
								'output'=>$this->myutil, 
								'code'=>$result,
								'controller' => 'mfile'
						)
					);
					
				// local 에 저장된 파일을 삭제 한다.
				$result_delete = unlink($file_info['full_path']);
				if(!$result_delete) {
					log_message('warn', 'upload failed delete file['.$file_info['full_path'].'] in local-disk');
				}
				
				if($thumbnail_info) {
					$result_delete = unlink($thumbnail_info['full_path']);
					if(!$result_delete) {
						log_message('warn', 'upload failed delete(thumbnail) file['.$thumbnail_info['full_path'].'] in local-disk');
					}
				}
			}
			$this->benchmark->mark('end_upload');
			log_message('info', 'upload T['.$this->benchmark->elapsed_time('start_upload', 'end_upload').']');
			return;
		}
		
		// network disk 에 올리도록 설정 되어 있기 때문에 올린다.
		$result_upload_network_disk = array();
		$result = $this->model->saveFileToNetworkDisk($result_upload_network_disk, $file_info, 
				$thumbnail_info, FALSE, $file_type);
		if($result == $this->success_code) {
			$result = $this->model->saveFileInDB($result_insert, $file_info, $thumbnail_info, FALSE, $file_type, 
					FALSE, $result_upload_network_disk);
			if($result == $this->success_code) {
				$this->load->view(
						'common/output_view', 
						array(
								'output' => $this->myutil, 
								'code' => $result,
								'controller' => 'mfile',
								'other' => array(
										'file_srl' => $result_insert['file_srl'],
										'url' => $result_insert['network_url'],
										'width' => $result_insert['width'] ? $result_insert['width'].'' : '0',
										'height' => $result_insert['height'] ? $result_insert['height'].'' : '0',
										'thumbnail_url' => $result_insert['thumbnail_network_url'],
										'thumbnail_width' => $result_insert['thumbnail_width'],
										'thumbnail_height' => $result_insert['thumbnail_height']
									)
						)
				);
			} else {
				$this->load->view(
						'common/output_view', 
						array(
								'output'=>$this->myutil, 
								'code'=>$result,
								'controller' => 'mfile'
						)
					);
			}
		} else {
			// network disk upload 실패
			$this->load->view(
					'common/output_view', 
					array(
							'output'=>$this->myutil, 
							'code'=>$result,
							'controller' => 'mfile'
					)
				);
		}
		
		// local 에 저장된 파일을 삭제 한다.
		// CI 의 delete_files 는 디렉토리 삭제임.
		$result_delete = unlink($file_info['full_path']);
		if(!$result_delete) {
			log_message('warn', 'upload failed delete(orig) file['.$file_info['full_path'].'] in local-disk');
		}
		
		if($thumbnail_info) {
			$result_delete = unlink($thumbnail_info['full_path']);
			if(!$result_delete) {
				log_message('warn', 'upload failed delete(thumbnail) file['.$thumbnail_info['full_path'].'] in local-disk');
			}
		}
		$this->benchmark->mark('end_upload');
		log_message('info', 'upload T['.$this->benchmark->elapsed_time('start_upload', 'end_upload').']');
	}
}
?>