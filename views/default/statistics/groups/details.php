<?php
/**
 * Displays detailed information from a entity
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */
$entity = $vars['item_guid'];
if(empty($entity)){
	$entity = $vars["group_guid"];
}
$entity = get_entity($entity);
$title = $entity->title;
if(empty($title)){
	$title = $entity->name;
}
else{
	$title = sprintf(elgg_echo("statistics:visitits_detail"),$title);
}
echo "<h2><a href='{$entity->getURL()}' target='_blank'>{$title}</a></h2>";
echo "<br>";
echo elgg_view('admin/advanced_statistics/views_statistics',array('entity'=>$entity));