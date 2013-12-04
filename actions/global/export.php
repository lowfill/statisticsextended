<?php
/**
 * Global export action
 *
 * Action that handle the global related export functions
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */

action_gatekeeper();
admin_gatekeeper();

statistics_extended_load_library();

$type = get_input("type","global");
set_time_limit(0);

$contents = "";
switch($type){
	case "global":
		$contents = cop_statistics_export_global_data();
		break;
	case "groups":
		$contents = cop_statistics_export_global_group_data();
		break;
	case "resources":
		$contents = cop_statistics_export_global_resources_data();
		break;
}
if(!empty($contents)){
	$file_name = date("Y-m-d")."-";
	$file_name.= "knl_statistics_{$type}";
	$file_name.= ".csv";

	header('Content-Type: application/vnd.ms-excel');
	header('Content-Transfer-Encoding: binary');
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-disposition: attachment;filename=$file_name");
	header("Content-Length: ".strlen($contents));
	$splitString = str_split($contents, 1024);
	foreach($splitString as $chunk){
		echo $chunk;
	}
	exit;
}
else{
	register_error(elgg_echo("statistics:error:groups:no_data"));
}
forward("pg/global_statistics");
