<?php
  elgg_load_library('statistics_extended:lib');

  $users = elgg_get_entities(array('types'=>'user','count'=>true));

  //Members data
  $members = array("internal","external");
  list($internal,$external) = cop_statistics_members_count();
  $members_totals = array("internal"=>$internal,"external"=>$external);
  $members_labels = cop_statistics_label_generator($members,$members_totals);

  $active_labels = array("active","inactive");
  list($active,$inactive) = cop_statistics_active_count();
  $active_totals = array("active"=>$active,"inactive"=>$inactive);
  $active_labels = cop_statistics_label_generator($active_labels,$active_totals);

    //Resources data
  $resources_tpl = array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top");
  $resources = array();
  foreach($resources_tpl as $resource){
    if(get_subtype_id('object', $resource)!=''){
      $resources[]=$resource;
    }
  }
  $resources_totals = cop_statitics_objects_count($resources);
  $resources_totals["page"]+=$resources_totals["page_top"];

  array_pop($resources);
  array_pop($resources_totals);
  $resources_labels = cop_statistics_label_generator($resources,$resources_totals);

?>
<h2><?php echo sprintf(elgg_echo("statistics:users:counter"),$users)?></h2>

<div id="statistics_group_graphs">
<?php echo elgg_view("output/pie",array("internalname"=>'statistics_group_users_graph',
										"class"=>"statistics_graph",
										"size"=>"300x100",
										"title"=>elgg_echo("statistics:members"),
                                        "labels"=>$members_labels,
										"values"=>$members_totals))

?>

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

<div class="clear"></div>
<div class="statistics_buttons">
<?php
  $url = $vars['url']."action/export/global/?type=global";
  $url = elgg_add_action_tokens_to_url($url);
?>
<a href="<?php echo $url?>"><?php echo elgg_echo("export")?></a>
</div>
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

$column_config[]=array('display'=>elgg_echo("statistics:global:member:internal"),
	'name'=>'internal',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:global:member:blog'),
	'name'=>'blog',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:global:member:file'),
	'name'=>'file',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo('statistics:global:member:bookmark'),
	'name'=>'bookmark',
	'width'=> 70,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo('statistics:global:member:event'),
	'name'=>'event',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo('statistics:global:member:discussion'),
	'name'=>'discussion',
	'width'=> 70,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:global:member:page'),
	'name'=>'page',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);

echo elgg_view("output/grid",array('internalname'=>'statistics_users_global',
                                   'endpoint'=>'users_stats',
								   'column_configuration'=>$column_config,
								   'width'=>'auto',
								   'nowrap'=>false,
));
?>