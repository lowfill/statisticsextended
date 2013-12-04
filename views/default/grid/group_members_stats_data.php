<?php
/**
 * Grid group members statistics data view
 *
 * Handle the request made from the flexigrid view and returns basic group members statistics data
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
$group = get_input("group_guid");

$options = array('types'=>'user',
				 'count'=>true,
				 'limit'=>50,
				 "relationship"=>"member",
				 "relationship_guid"=>$group,
				 "inverse_relationship"=>true);

$count = elgg_get_entities_from_relationship($options);
$options['count']=false;
$options['limit']=$rp;
$options['offset']=$start;
if(!empty($sortname)){
	$options['order_by']="$sortname $sortorder";
}
$entities = elgg_get_entities_from_relationship($options);

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
		$email = (!empty($entity->contactemail)) ? $entity->contactemail : $entity->email;

		$internal = "<input type=\"checkbox\" disabled=\"disabled\" >";
		if(strpos($email,"@iadb.org")>0){
			$internal = "<input type=\"checkbox\" disabled=\"disabled\" checked=\"checked\">";
		}

		$values = statistics_extended_objects_count($items,$group,$entity->guid);
		$values["page"]+=$values["page_top"];
		array_pop($values);

		$row['cell']=array($name,$internal);
		foreach($values as $value){
			$row['cell'][]=$value;
		}
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