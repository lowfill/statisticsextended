<?php
/**
 * Group statistics view
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */

  statistics_extended_load_library();

  $group = get_input("group_guid");

  $visits_count = get_views_counter($group);
  $member_visits_count = cop_statistics_members_views($group);
  //Members data
  $members = array("internal","external");
  list($internal,$external) = cop_statistics_members_count($group);
  $members_totals = array("internal"=>$internal,"external"=>$external);
  $members_labels = cop_statistics_label_generator($members,$members_totals);

  // Members activity data
  $active_labels = array("active","inactive");
  list($active,$inactive) = cop_statistics_active_count($group);
  $active_totals = array("active"=>$active,"inactive"=>$inactive);
  $active_labels = cop_statistics_label_generator($active_labels,$active_totals);

  //Resources data
  $resources = array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top");
  $resources_totals = cop_statitics_objects_count($resources,$group);
  $resources_totals["page"]+=$resources_totals["page_top"];

  array_pop($resources);
  array_pop($resources_totals);
  $resources_labels = cop_statistics_label_generator($resources,$resources_totals);

?>
<h2><?php echo sprintf(elgg_echo("statistics:groups:visits"),$visits_count)?>  <small>[<a href="<?php echo $vars["url"]?>pg/group_statistics/<?php echo $group;?>/details"><?php echo elgg_echo("details")?></a>]</small></h2>
<h2><?php echo sprintf(elgg_echo("statistics:groups:members:visits"),$member_visits_count)?></h2>
<br>
<div id="statistics_group_graphs">
<?php echo elgg_view("output/pie",array("internalname"=>'statistics_group_users_graph',
										"class"=>"statistics_graph",
										"size"=>"300x100",
										"title"=>elgg_echo("statistics:members"),
                                        "labels"=>$members_labels,
										"values"=>$members_totals))?>

<?php echo elgg_view("output/pie",array("internalname"=>'statistics_group_active_graph',
										"class"=>"statistics_graph",
										"size"=>"300x100",
										"title"=>elgg_echo("statistics:members"),
                                        "labels"=>$active_labels,
										"values"=>$active_totals))?>

<?php echo elgg_view("output/pie",array("internalname"=>'statistics_group_resources_graph',
										"class"=>"statistics_graph",
										"size"=>"300x100",
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
	'width'=> 80,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:groups:member:actor_type"),
	'name'=>'actor_type',
	'width'=> 100,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:groups:member:experience_theme"),
	'name'=>'experience_theme',
	'width'=> 50,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:groups:member:internal"),
	'name'=>'internal',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:groups:member:blog'),
	'name'=>'blog',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:groups:member:file'),
	'name'=>'file',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo('statistics:groups:member:bookmark'),
	'name'=>'bookmark',
	'width'=> 70,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo('statistics:groups:member:event'),
	'name'=>'event',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo('statistics:groups:member:discussion'),
	'name'=>'discussion',
	'width'=> 70,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:groups:member:page'),
	'name'=>'page',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);

echo elgg_view("output/grid",array('internalname'=>'statistics_groups_global',
                                   'endpoint'=>'group_members_stats',
								   'extra_params'=>array("group_guid"=>$group),
								   'column_configuration'=>$column_config,
								   'width'=>'auto',
								   'nowrap'=>false,
));
?>
