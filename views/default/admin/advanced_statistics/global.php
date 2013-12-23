<?php
  elgg_load_library('statistics_extended:lib');
  elgg_load_css('statistics_extended:css');

  $users = elgg_get_entities(array('types'=>'user','count'=>true));


  $active_labels = array("active","inactive");
  list($active,$inactive) = statistics_extended_active_count();
  $active_totals = array("active"=>$active,"inactive"=>$inactive);
  $active_labels = statistics_extended_label_generator($active_labels,$active_totals);

  //Resources data
  $tools = elgg_get_config('group_tool_options');
  $resources = array();
  if(is_array($tools)){
    foreach($tools as $tool){
      if($tool->name!='activity'){
        $resources=array_merge($resources,statistics_extended_tool_object($tool->name));
      }
    }
  }

  $resources_totals = statistics_extended_objects_count($resources);
  if(array_key_exists('page', $resources_totals)){
    $resources_totals["page"]+=$resources_totals["page_top"];
    unset($resources[current(array_keys($resources,'page_top'))]);
    unset($resources_totals['page_top']);
  }
  $resources_labels = statistics_extended_label_generator($resources,$resources_totals);

?>
<h2><?php echo sprintf(elgg_echo("statistics:users:counter"),$users)?></h2>

<div id="statistics_group_graphs">

<?php echo elgg_view("output/pie",array("internalname"=>'statistics_users_graph',
										"class"=>"statistics_graph",
										"size"=>"300x100",
										"title"=>elgg_echo("statistics:members"),
                                        "labels"=>$active_labels,
										"values"=>$active_totals))

?>
<?php echo elgg_view("output/pie",array("internalname"=>'statistics_group_resources_graph',
										"class"=>"statistics_graph",
										"size"=>"300x100",
										"title"=>elgg_echo("statistics:resources"),
										"labels"=>$resources_labels,
										"values"=>$resources_totals))
?>
</div>

<div class="clearfloat"></div>
<div class="statistics_buttons">
<?php
  $url = $vars['url']."action/export/global/?type=global";
  $url = elgg_add_action_tokens_to_url($url);
  if($user < 7000){
?>
<a href="<?php echo $url?>"><?php echo elgg_echo("export")?></a>
</div>
<?php }?>
<div class="clear"></div>

<?php
$column_config = array();
$column_config[]=array('display'=>elgg_echo("statistics:global:member:name"),
	'name'=>'name',
	'width'=> 150,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo("statistics:global:member:location"),
	'name'=>'location',
	'width'=> 80,
	'sortable'=>false,
	'align'=>'center'
);

foreach($resources as $resource){
  $column_config[]=array('display'=>elgg_echo('statistics:global:member:'.$resource),
      'name'=>$resource,
      'width'=> 40,
      'sortable'=>false,
      'align'=>'center'
  );
}


 echo elgg_view("output/grid",array('internalname'=>'statistics_users_global',
                                   'endpoint'=>'users_stats',
								   'column_configuration'=>$column_config,
								   'width'=>'auto',
								   'nowrap'=>false,
));
?>
