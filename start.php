<?php
/**
 * Statistics plugin
 *
 * This plugin provides many views to display advanced statistics about a Elgg site
 *
 * @package statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */

function statistics_extended_init(){

    elgg_register_library('statistics_extended:lib', dirname(__FILE__)."/lib/statistics.php");

	elgg_extend_view('css','statistics/css');

	elgg_register_page_handler("group_statistics","statistics_extended_group_handler");
	elgg_register_page_handler("global_statistics","statistics_extended_global_handler");

	elgg_register_plugin_hook_handler("format","friendly:title","statistics_extended_friendly_title");

	elgg_register_action('export/groups',dirname(__FILE__)."/actions/groups/export.php");
	elgg_register_action('export/global',dirname(__FILE__)."/actions/global/export.php");
}

function statistics_extended_pagesetup(){
	global $CONFIG;

	if (elgg_in_context('admin')) {
		elgg_register_admin_menu_item('administer', 'global','advanced_statistics');
		elgg_register_admin_menu_item('administer', 'groups','advanced_statistics');
		elgg_register_admin_menu_item('administer', 'resources','advanced_statistics');
		elgg_register_admin_menu_item('administer', 'evolution','advanced_statistics');

	}

	//Group related statistics
	$page_owner = page_owner_entity();
	// Submenu items for all group pages
	if ($page_owner instanceof ElggGroup && get_context() == 'groups' && $page_owner->canEdit()) {
		add_submenu_item(elgg_echo('statistics:groups'),$CONFIG->url."group_statistics/{$page_owner->getGUID()}", '2groupsadminactions');
	}

}

function statistics_extended_group_handler($page){
	if(isset($page[0])){
		set_input("group_guid",$page[0]);
	}
	if(isset($page[1])){
		set_input("section",$page[1]);
	}
	if(isset($page[2])){
		set_input("item_guid",$page[2]);
	}
	require_once dirname(__FILE__)."/groups.php";
}

function statistics_extended_global_handler($page){
	if(isset($page[0])){
		set_input("section",$page[0]);
	}
	require_once dirname(__FILE__)."/admin.php";
}
/**
 * Hoot for remove latin characters from the title before add it to the title url
 * @param $hook
 * @param $type
 * @param $returnvalue
 * @param $params
 * @return string
 */
function statistics_extended_friendly_title($hook, $type, $returnvalue, $params){
	if($type=="friendly:title"){
		$title = statistics_extended_clean_string($params["title"]);
		$title = preg_replace("/[^\w ]/","",$title);
		$title = str_replace(" ","-",$title);
		$title = str_replace("--","-",$title);
		$title = trim($title);
		$title = strtolower($title);
		return $title;
	}
}

/**
 * Helper function to clean an string from latin characters
 * @param $string
 * @return string
 */
function statistics_extended_clean_string($string){

	$string = str_replace("á","a",$string);
	$string = str_replace("é","e",$string);
	$string = str_replace("í","i",$string);
	$string = str_replace("ó","o",$string);
	$string = str_replace("ú","u",$string);
	$string = str_replace("Á","A",$string);
	$string = str_replace("É","E",$string);
	$string = str_replace("Í","I",$string);
	$string = str_replace("Ó","O",$string);
	$string = str_replace("Ú","U",$string);
	$string = str_replace("ñ","n",$string);
	$string = str_replace("Ñ","N",$string);
	$string = str_replace("ã","a",$string);
	$string = str_replace("Ã","A",$string);
	$string = str_replace("õ","o",$string);
	$string = str_replace("Õ","O",$string);
	$string = str_replace("à","a",$string);
	$string = str_replace("À","a",$string);
	$string = str_replace("ç","c",$string);
	$string = str_replace("Ç","C",$string);
	$string = str_replace("ê","e",$string);
	$string = str_replace("ê","e",$string);
	$string = str_replace("â","a",$string);
	$string = str_replace("Â","a",$string);

	return $string;
}
/**
 * Helper function for lazy load the statistic library in the related views
 */
function statistics_extended_load_library(){
	require_once dirname(__FILE__)."/lib/statistics.php";
}

register_elgg_event_handler('init', 'system', 'statistics_extended_init');
register_elgg_event_handler('pagesetup','system','statistics_extended_pagesetup');
