<?php
/**
 * Group statistics view
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */

  elgg_load_library('statistics_extended:lib');

  elgg_load_css('statistics_extended:css');

  $group = $vars["group_guid"];
  $group_entity = get_entity($group);

  $visits_count = get_views_counter($group);
  $member_visits_count = statistics_extended_members_views($group);

  // Members activity data
  $active_labels = array("active","inactive");
  list($active,$inactive) = statistics_extended_active_count($group);
  $active_totals = array("active"=>$active,"inactive"=>$inactive);
  $active_labels = statistics_extended_label_generator($active_labels,$active_totals);

  //Resources data
  $tools = elgg_get_config('group_tool_options');
  $resources = array();
  if(is_array($tools)){
     foreach($tools as $tool){
       $tool_name = $tool->name."_enable";
       if($group_entity->$tool_name == 'yes' && $tool->name !='activity'){
         $resources=array_merge($resources,statistics_extended_tool_object($tool->name));
       }
     }
  }
  $resources_totals = statistics_extended_objects_count($resources,$group);
  if(array_key_exists('page', $resources_totals)){
    $resources_totals["page"]+=$resources_totals["page_top"];
    unset($resources[current(array_keys($resources,'page_top'))]);
    unset($resources_totals['page_top']);
  }
  $resources_labels = statistics_extended_label_generator($resources,$resources_totals);

?>
<h2><?php echo sprintf(elgg_echo("statistics:groups:visits"),$visits_count)?>  <small>[<a href="<?php echo $vars["url"]?>pg/group_statistics/<?php echo $group;?>/details"><?php echo elgg_echo("statistics:details")?></a>]</small></h2>
<h2><?php echo sprintf(elgg_echo("statistics:groups:members:visits"),$member_visits_count)?></h2>
<br>
<div id="statistics_group_graphs">

<?php echo elgg_view("output/pie",array("internalname"=>'statistics_group_active_graph',
										"class"=>"statistics_graph",
										"size"=>"400x120",
										"title"=>elgg_echo("statistics:members"),
                                        "labels"=>$active_labels,
										"values"=>$active_totals))?>

<?php echo elgg_view("output/pie",array("internalname"=>'statistics_group_resources_graph',
										"class"=>"statistics_graph",
										"size"=>"300x120",
										"title"=>elgg_echo("statistics:resources"),
										"labels"=>$resources_labels,
										"values"=>$resources_totals))?>
</div>

<div class="clear"></div>
<div class="statistics_buttons">
<?php
  $url = $vars['url']."action/export/groups/?group_guid={$group}&type=global";
  $url = elgg_add_action_tokens_to_url($url);
?>
<a href="<?php echo $url?>"><?php echo elgg_echo("export")?></a>
</div>
<div class="clear"></div>

<?php
$column_config = array();
$column_config[]=array('display'=>elgg_echo("statistics:groups:member:name"),
	'name'=>'name',
	'width'=> 180,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo("statistics:groups:member:location"),
    'name'=>'location',
    'width'=> 100,
    'sortable'=>true,
    'align'=>'center'
);

foreach($resources as $resource){
  $column_config[]=array('display'=>elgg_echo('statistics:groups:member:'.$resource),
      'name'=>$resource,
      'width'=> 50,
      'sortable'=>false,
      'align'=>'center'
  );
}

echo elgg_view("output/grid",array('internalname'=>'statistics_groups_global',
                                   'endpoint'=>'group_members_stats',
								   'extra_params'=>array("group_guid"=>$group),
								   'column_configuration'=>$column_config,
								   'width'=>'auto',
								   'nowrap'=>false,
));
?>
