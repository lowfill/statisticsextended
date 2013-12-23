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

$tools = elgg_get_config('group_tool_options');
$items = array();
if(is_array($tools)){
  foreach($tools as $tool){
    if($tool->name!='activity'){
      $items=array_merge($items,statistics_extended_tool_object($tool->name));
    }
  }
}

$rows = array();
if(!empty($entities)){
	foreach($entities as $entity){
		$row = array();
		$row['id']=$entity->guid;
		$name = $entity->name;
		$name = "<a href=\"{$entity->getUrl()}\">$name</a>";

		$access = "<input type=\"checkbox\" disabled=\"disabled\" >";
		if($entity->membership==ACCESS_PUBLIC){
			$access = "<input type=\"checkbox\" disabled=\"disabled\" checked=\"checked\">";
		}

		$values = statistics_extended_objects_count($items,$entity->guid);
		if(array_key_exists('page',$values)){
    	  $values["page"]+=$values["page_top"];
  		  unset($values['page_top']);
		}

		$row['cell']=array($name,$access);
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