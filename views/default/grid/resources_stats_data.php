<?php
/**
 * Grid resources statistics data view
 *
 * Handle the request made from the flexigrid view and returns basic resources statistics data
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
$sortname = get_input('sortname','e.subtype');
if(empty($sortname)){
	$sortname = 'e.subtype';
}
$sortorder = get_input('sortorder','asc');
$start = (($page-1) * $rp);

$options = array('types'=>'object',
				 'subtypes'=>array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top"),
				 'count'=>true,
				 'limit'=>50);

//$count = elgg_get_entities($options);
$count = get_entities_by_views_counter($options);
$options['count']=false;
$options['limit']=$rp;
$options['offset']=$start;
//if(!empty($sortname)){
	$options['order_by']="$sortname $sortorder";
//}
//$entities = elgg_get_entities($options);
$entities = get_entities_by_views_counter($options);

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-type: text/x-json");

$rows = array();
if(!empty($entities)){
	foreach($entities as $entity){
		$row = array();
		$row['id']=$entity->guid;
		$owner="<a href=\"".$entity->getOwnerEntity()->getUrl()."\">{$entity->getOwnerEntity()->name}</a>";
		$group = "";
		$container =get_entity($entity->container_guid);
		if(!empty($container) && $container instanceof ElggGroup){
			$group="<a href=\"".$container->getUrl()."\">{$container->name}</a>";
		}
		$type = elgg_echo("statistics:label:type:".$entity->getSubtype());
		$name = $entity->title;
		$name = "<a href=\"".$entity->getUrl()."\">$name</a>";

		$visits = get_views_counter($entity->guid);
		$detail = "<a href=\"{$CONFIG->url}pg/views_counter/views_statistics/{$entity->guid}\">".elgg_echo("statistics:details")."</a>";
		$row['cell']=array(
			$group,
			$owner,
			$type,
			$name,
			$visits,
			$detail
			);
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