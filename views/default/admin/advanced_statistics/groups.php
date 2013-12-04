<?php
/**
 * Global groups view
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */

  statistics_extended_load_library();

  $groups = elgg_get_entities(array('types'=>'group','count'=>true));

  list($group_types,$group_types_totals) = cop_statistics_groups_property_count("group_type");
  $group_types = cop_statistics_label_generator($group_types,$group_types_totals);

  list($organizational_unit_types,$organizational_unit_totals) = cop_statistics_groups_property_count("organizational_unit");
  $organizational_unit_types = cop_statistics_label_generator($organizational_unit_types,$organizational_unit_totals,"");

  list($impact_contribution_types,$impact_contribution_totals) = cop_statistics_groups_property_count("impact_contribution");
  $impact_contribution_types = cop_statistics_label_generator($impact_contribution_types,$impact_contribution_totals);

  list($impact_contribution_category_types,$impact_contribution_category_totals) = cop_statistics_groups_property_count("impact_contribution_category");
  $impact_contribution_category_types = cop_statistics_label_generator($impact_contribution_category_types,$impact_contribution_category_totals);

  list($public_types,$public_totals) = cop_statistics_groups_property_count("content_privacy");
  $public_types = cop_statistics_label_generator($public_types,$public_totals);

  list($access_types,$access_totals) = cop_statistics_groups_property_count("membership");
  $access_types = cop_statistics_label_generator($access_types,$access_totals);

  list($status_types,$status_totals) = cop_statistics_groups_property_count("group_status");
  $status_types = cop_statistics_label_generator($status_types,$status_totals);

  //TODO Change the id container for custom CSS layout
?>
<h2><?php echo sprintf(elgg_echo("statistics:groups:counter"),$groups)?></h2>
<div id="statistics_group_graphs">
<?php echo elgg_view("output/pie",array("internalname"=>'statistics_groups_types_graph',
										"class"=>"statistics_graph",
										"size"=>"320x130",
										"title"=>elgg_echo("statistics:groups:types"),
                                        "legends"=>$group_types,
										"values"=>$group_types_totals));

?>

<?php  echo elgg_view("output/pie",array("internalname"=>'statistics_groups_organizational_unit_graph',
										"class"=>"statistics_graph",
										"size"=>"320x130",
										"title"=>elgg_echo("statistics:organizational_unit"),
										"legends"=>$organizational_unit_types,
										"values"=>$organizational_unit_totals));

?>
<?php  echo elgg_view("output/pie",array("internalname"=>'statistics_groups_impact_contribution_graph',
										"class"=>"statistics_graph",
										"size"=>"320x130",
										"title"=>elgg_echo("statistics:impact_contribution"),
										"legends"=>$impact_contribution_types,
										"values"=>$impact_contribution_totals));

?>
<?php  echo elgg_view("output/pie",array("internalname"=>'statistics_groups_impact_contribution_category_graph',
										"class"=>"statistics_graph",
										"size"=>"320x130",
										"title"=>elgg_echo("statistics:impact_contribution_category"),
										"legends"=>$impact_contribution_category_types,
										"values"=>$impact_contribution_category_totals));

?>
<?php  echo elgg_view("output/pie",array("internalname"=>'statistics_groups_public_graph',
										"class"=>"statistics_graph",
										"size"=>"180x100",
										"title"=>elgg_echo("statistics:public"),
										"legends"=>$public_types,
										"values"=>$public_totals));

?>
<?php  echo elgg_view("output/pie",array("internalname"=>'statistics_groups_access_graph',
										"class"=>"statistics_graph",
										"size"=>"180x100",
										"title"=>elgg_echo("statistics:access_membership"),
										"legends"=>$access_types,
										"values"=>$access_totals));

?>

<?php  echo elgg_view("output/pie",array("internalname"=>'statistics_groups_status_graph',
										"class"=>"statistics_graph",
										"size"=>"180x100",
										"title"=>elgg_echo("statistics:status"),
										"legends"=>$status_types,
										"values"=>$status_totals));

?>

</div>

<div class="clear"></div>
<div class="statistics_buttons">
<?php
  $url = $vars['url']."action/export/global/?type=groups";
  $url = elgg_add_action_tokens_to_url($url);
?>
<a href="<?php echo $url?>"><?php echo elgg_echo("export")?></a>
</div>
<div class="clear"></div>

<?php
$column_config = array();
$column_config[]=array('display'=>elgg_echo("statistics:global:groups:name"),
	'name'=>'name',
	'width'=> 100,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo("statistics:global:groups:type"),
	'name'=>'type',
	'width'=> 80,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo("statistics:global:groups:organizational_unit"),
	'name'=>'organizational_unit',
	'width'=> 80,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:global:groups:internal"),
	'name'=>'internal',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:global:groups:external"),
	'name'=>'external',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:global:groups:status"),
	'name'=>'status',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:global:groups:access"),
	'name'=>'access',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:global:groups:blog'),
	'name'=>'blog',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:global:groups:file'),
	'name'=>'file',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo('statistics:global:groups:bookmark'),
	'name'=>'bookmark',
	'width'=> 70,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo('statistics:global:groups:event'),
	'name'=>'event',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);

$column_config[]=array('display'=>elgg_echo('statistics:global:groups:discussion'),
	'name'=>'discussion',
	'width'=> 70,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:global:groups:page'),
	'name'=>'page',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>"",
	'name'=>'detail',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);

echo elgg_view("output/grid",array('internalname'=>'statistics_groups_global',
                                   'endpoint'=>'groups_stats',
								   'column_configuration'=>$column_config,
								   'width'=>'auto',
								   'nowrap'=>false,
));
?>