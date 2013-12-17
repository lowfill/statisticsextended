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



elgg_load_library('statistics_extended:lib');


$page = get_input('grid_page',1);
$rp = get_input('rp',100);
$sortname = get_input('sortname','e.guid');
$sortorder = get_input('sortorder','desc');
$start = (($page-1) * $rp);
$group = get_input("group_guid");
$group_entity = get_entity($group);

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
	$options['order_by_metadata']=array('name'=>$sortname,'direction'=>$sortorder);

}
$entities = elgg_get_entities_from_relationship($options);

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-type: text/x-json");

$tools = elgg_get_config('group_tool_options');
$items = array();
if(is_array($tools)){
  foreach($tools as $tool){
    $tool_name = $tool->name."_enable";
    if($group_entity->$tool_name == 'yes' && $tool->name !='activity'){
      $items=array_merge($items,statistics_extended_tool_object($tool->name));
    }
  }
}

$rows = array();
if(!empty($entities)){
	foreach($entities as $entity){
		$row = array();
		$row['id']=$entity->guid;
		$name = mb_convert_case("$entity->name",MB_CASE_TITLE,'UTF-8');
		$name = "<a href=\"{$entity->getUrl()}\">$name</a>";
		$email = (!empty($entity->contactemail)) ? $entity->contactemail : $entity->email;
		$location = $entity->location;

		$values = statistics_extended_objects_count($items,$group,$entity->guid);
		$values["page"]+=$values["page_top"];
		unset($values['page_top']);

		$row['cell']=array($name,$location);
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