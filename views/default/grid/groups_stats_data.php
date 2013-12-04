<?php
/**
 * Grid resources statistics data view
 *
 * Handle the request made from the flexigrid view and returns basic group resources statistics data
 *
 *
 * @param page
 * @param rp
 * @param sortname
 * @param sortorder
 * @param group_guid
 */

global $CONFIG;

statistics_extended_load_library();

$page = get_input('grid_page',1);
$rp = get_input('rp',100);
$sortname = get_input('sortname','e.guid');
$sortorder = get_input('sortorder','desc');
$start = (($page-1) * $rp);

$options = array('types'=>'group',
				 'count'=>true,
				 'limit'=>50);

$count = elgg_get_entities($options);
$options['count']=false;
$options['limit']=$rp;
$options['offset']=$start;
if(!empty($sortname)){
	$options['order_by']="$sortname $sortorder";
}
$entities = elgg_get_entities($options);

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-type: text/x-json");

$items = array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top");

$rows = array();
if(!empty($entities)){
	foreach($entities as $entity){
		$row = array();
		$row['id']=$entity->guid;
		$name = mb_convert_case("$entity->name",MB_CASE_TITLE,'UTF-8');
		$name = "<a href=\"{$entity->getUrl()}\">$name</a>";
		$type = elgg_echo($entity->group_type);
		$organizational_unit=str_replace("||"," ",$entity->organizational_unit);

		list($internal,$external) = cop_statistics_members_count($entity->guid);

		$status = $entity->group_status;
		if(empty($status)){
			$status = "active";
		}
		$status = elgg_echo("groups:extras:status:$status");

		$access = "<input type=\"checkbox\" disabled=\"disabled\" >";
		if($entity->content_privacy=="yes"){
			$access = "<input type=\"checkbox\" disabled=\"disabled\" checked=\"checked\">";
		}

		$values = cop_statitics_objects_count($items,$entity->guid);
		$values["page"]+=$values["page_top"];
		array_pop($values);

		$row['cell']=array($name,$type,$organizational_unit,$internal,$external,$status,$access);
		foreach($values as $value){
			$row['cell'][]=$value;
		}
		$row['cell'][]="<a target=\"_blank\" href=\"{$CONFIG->url}pg/group_statistics/{$entity->guid}\">".elgg_echo("statistics:details")."</a>";
		$rows[]=$row;
	}
}

$data = array(
	'grid_page'=>$page,
	'total'=>$count,
	'rows'=>$rows
);

echo json_encode($data);
?>