<?php
/**
 * 가입시 발생하는 에러 코드
 *
 * @author dhkim94@gmail.com
 */
$config['mfile_file_upload_fail']				= 'E020001';		// 파일 업로드 실패
$config['mfile_insert_table_fail']				= 'E020002';		// 파일 테이블에 insert 실패
$config['mfile_upload_to_network_disk_fail']	= 'E020003';		// 네트워크 디스크에 업로드 실패
$config['mfile_file_upload_no_selected_file']	= 'E020004';		// You did not select a file to upload.
$config['mfile_file_upload_big_size_by_php']	= 'E020005';		// The uploaded file exceeds the maximum allowed size in your PHP configuration file.
$config['mfile_file_upload_big_size_by_form']	= 'E020006';		// The uploaded file exceeds the maximum size allowed by the submission form.
$config['mfile_file_upload_partial']			= 'E020007';		// The file was only partially uploaded.
$config['mfile_file_upload_no_tmp_directory']	= 'E020008';		// The temporary folder is missing.
$config['mfile_file_upload_unable_to_write']	= 'E020009';		// The file could not be written to disk.
$config['mfile_file_upload_stop_by_extension']	= 'E020010';		// The file upload was stopped by extension.
$config['mfile_file_upload_invalid_filetype']	= 'E020011';		// The filetype you are attempting to upload is not allowed.
$config['mfile_file_upload_invalid_filesize']	= 'E020012';		// The file you are attempting to upload is larger than the permitted size.
$config['mfile_file_upload_invalid_dimensions']	= 'E020013';		// The image you are attempting to upload exceedes the maximum height or width.
$config['mfile_file_upload_destination_error']	= 'E020014';		// A problem was encountered while attempting to move the uploaded 
																	// file to the final destination.
$config['mfile_file_upload_no_upload_config']	= 'E020015';		// my_conf/file.php 에  upload_path 설정이 없음
$config['mfile_file_upload_mkdir']				= 'E020016';		// local 서버에 파일 저장하기 위해 디렉토리 생성 실패
?>