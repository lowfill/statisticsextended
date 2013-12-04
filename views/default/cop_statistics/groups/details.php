<?php
/**
 * Displays detailed information from a entity
 * 
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */
$entity = get_input('item_guid');
if(empty($entity)){
	$entity = get_input("group_guid");	
}
$entity = get_entity($entity);
$title = $entity->title;
if(empty($title)){
	$title = $entity->name;	
}
else{
	$title = sprintf(elgg_echo("statistics:visitits_detail"),$title);
}
echo "<h2>{$title}</h2>";
echo elgg_view('views_counter/views_statistics',array('entity'=>$entity));