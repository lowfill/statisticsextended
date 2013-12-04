<?php
/**
 * Group resources view
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */

  statistics_extended_load_library();

  $group = get_input("group_guid");

  //Resources data
  $resources = array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top");
  $resources_totals = cop_statitics_objects_view_count($resources,$group);
  $resources_totals["page"]+=$resources_totals["page_top"];

  array_pop($resources);
  array_pop($resources_totals);
  $resources_labels = cop_statistics_label_generator($resources,$resources_totals,'statistics:label:type:');

?>
<div id="statistics_group_graphs" align="center">
<?php echo elgg_view("output/bar",array("internalname"=>'statistics_group_resources_graph',
										"class"=>"statistics_graph",
										"size"=>"500x250",
										"title"=>elgg_echo("statistics:resources_views"),
                                        "labels"=>$resources_labels,
										"values"=>$resources_totals))?>
</div>
<div class="clear"></div>
<div class="statistics_buttons">
<?php
  $url = $vars['url']."action/export/groups/?group_guid={$group}&type=resources";
  $url = elgg_add_action_tokens_to_url($url);
?>
<a href="<?php echo $url?>"><?php echo elgg_echo("export")?></a>
</div>
<div class="clear"></div>

<?php
$column_config = array();
$column_config[]=array('display'=>elgg_echo("statistics:groups:resources:type"),
	'name'=>'e.subtype',
	'width'=> 100,
	'sortable'=>true,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:groups:resources:name"),
	'name'=>'name',
	'width'=> 180,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:groups:resources:visits'),
	'name'=>'visits',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>'',
	'name'=>'details',
	'width'=> 100,
	'sortable'=>false,
	'align'=>'center'
);


echo elgg_view("output/grid",array('internalname'=>'statistics_groups_global',
                                   'endpoint'=>'group_resources_stats',
								   'extra_params'=>array("group_guid"=>$group),
								   'column_configuration'=>$column_config,
								   'width'=>'auto',
								   'nowrap'=>false,
));
?>