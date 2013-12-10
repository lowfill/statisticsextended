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

elgg_load_library('statistics_extended:lib');
elgg_load_library('statistics_extended:export:lib');
elgg_load_library('statistics_extended:excel:lib');


$type = get_input("type","global");
set_time_limit(0);


switch($type){
	case "global":
		$contents = statistics_extended_export_global_data();
		break;
	case "groups":
		$contents = statistics_extended_export_global_group_data();
		break;
	case "resources":
		$contents = statistics_extended_export_global_resources_data();
		break;
}
if(!empty($contents)){
	$file_name = date("Y-m-d")."-";
	$file_name.= "statistics_{$type}";
	$file_name.= ".xlsx";

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="'.$file_name.'"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($contents, 'Excel2007');
	$objWriter->save('php://output');
	exit;

}
else{
	register_error(elgg_echo("statistics:error:groups:no_data"));
}
forward("admin/advanced_statistics/global");
