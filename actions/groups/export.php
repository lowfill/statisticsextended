<?php
/**
 * Groups export action
 *
 * Action that handle the group related export functions
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */

action_gatekeeper();

statistics_extended_load_library();

$group_guid = get_input("group_guid");
$type = get_input("type","global");

if(!empty($group_guid)){
	$group = get_entity($group_guid);
	if(!empty($group) && $group->canEdit()){

		$contents = "";
		switch($type){
			case "global":
				$contents = cop_statistics_export_group_global_data($group);
				break;
			case "resources":
				$contents = cop_statistics_export_group_resources_data($group);
				break;
		}
		if(!empty($contents)){
			$file_name = date("Y-m-d")."-";
			$file_name.= "knl_statistics_{$type}_";
			$file_name.= cop_statistics_clean_string($group->name)."_";
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
	}
	else{
		register_error(elgg_echo("statistics:error:groups:not_authorized"));
	}
	forward("pg/groups/{$group->guid}/{$group->name}");
}
else{
	register_error(elgg_echo("statistics:error:groups:empty_group"));
}