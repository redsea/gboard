<?php
/**
 * json 으로 뿌려 줄지 html 로 뿌려 줄지 분기를 해야 하나 말아야 하나.
 * 이건 추후에 고민해 보고, 일단 json 으로만 뿌려 주도록 한다.
 */
header('Content-Type: application/json; charset=utf-8;');
echo json_encode(
		$output->result_for_json(
				$code, 
				isset($other)?$other:FALSE, 
				isset($controller)?$controller:FALSE
			)
		);
?>