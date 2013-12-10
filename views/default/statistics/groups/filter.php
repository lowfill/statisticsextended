<?php
/**
 * Group statistics container
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 *
 * Parameters
 * @var group_guid
 * @var view
 */

$options = array(
		'index'=>array('href'=>'group_statistics/{{guid}}/','text'=>elgg_echo('statistics:global'),'priority'=>200),
		'resources'=>array('href'=>'group_statistics/{{guid}}/resources/','text'=>elgg_echo('statistics:resources'),'priority'=>300),
);

$view = $vars['selected'];

if($view == "details"){
  $options["details"]=array('href'=>'group_statistics/{{guid}}/','text'=>elgg_echo('statistics:details'),'priority'=>400);
}

foreach ($options as $name => $tab) {
  $tab['name'] = $name;

  $tab['href']=str_replace("{{guid}}",$vars['group_guid'],$tab['href']);
  if ($vars['selected'] == $name) {
    $tab['selected'] = true;
  }

  elgg_register_menu_item('filter', $tab);
}

echo elgg_view_menu('filter', array('sort_by' => 'priority', 'class' => 'elgg-menu-hz'));
?>
