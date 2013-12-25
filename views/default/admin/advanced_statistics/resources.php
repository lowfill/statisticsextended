<?php
/**
 * Global resources statistics view
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */

  elgg_load_css('statistics_extended:css');
  statistics_extended_load_library();

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
  $resources_views_totals = statistics_extended_objects_view_count($resources);

  if(in_array('page',$resources)){
    $resources_totals["page"]+=$resources_totals["page_top"];
    $resources_views_totals["page"]+=$resources_views_totals["page_top"];

    unset($resources[current(array_keys($resources,'page_top'))]);
    unset($resources_totals['page_top']);
    unset($resources_views_totals['page_top']);
  }

  $resources_labels = statistics_extended_label_generator($resources,$resources_totals,'statistics:label:type:');
  $resources_views_labels = statistics_extended_label_generator($resources,$resources_views_totals,'statistics:label:type:');

?>
<div id="statistics_group_graphs" align="center">
<?php echo elgg_view("output/bar",array("internalname"=>'statistics_group_resources_graph',
										"class"=>"statistics_graph",
										"size"=>"500x200",
										"title"=>elgg_echo("statistics:resources"),
                                        "labels"=>$resources_labels,
										"values"=>$resources_totals))?>
<?php echo elgg_view("output/bar",array("internalname"=>'statistics_group_resources_views_graph',
										"class"=>"statistics_graph",
										"size"=>"500x200",
										"title"=>elgg_echo("statistics:resources_views"),
                                        "labels"=>$resources_views_labels,
										"values"=>$resources_views_totals))?>
</div>
<div class="clear"></div>
<div class="statistics_buttons">
<?php
  $url = $vars['url']."action/export/global/?type=resources";
  $url = elgg_add_action_tokens_to_url($url);
  $total_resources = array_sum($resources_totals);
  if($total_resources < 10000){
?>
<a href="<?php echo $url?>"><?php echo elgg_echo("export")?></a>
<?php }?>
</div>
<div class="clear"></div>

<?php
$column_config = array();
$column_config[]=array('display'=>elgg_echo("statistics:global:resources:group"),
	'name'=>'group',
	'width'=> 100,
	'sortable'=>true,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:global:resources:owner"),
	'name'=>'owner',
	'width'=> 100,
	'sortable'=>true,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:global:resources:type"),
	'name'=>'e.subtype',
	'width'=> 100,
	'sortable'=>true,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo("statistics:global:resources:name"),
	'name'=>'name',
	'width'=> 180,
	'sortable'=>false,
	'align'=>'center'
);
$column_config[]=array('display'=>elgg_echo('statistics:global:resources:visits'),
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
                                   'endpoint'=>'resources_stats',
								   'column_configuration'=>$column_config,
								   'width'=>'auto',
								   'nowrap'=>false,
));
?>