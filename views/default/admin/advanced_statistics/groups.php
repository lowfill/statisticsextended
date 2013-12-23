<?php
/**
 * Global groups view
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */

  elgg_load_css('statistics_extended:css');
  elgg_load_library('statistics_extended:lib');

  $groups = elgg_get_entities(array('types'=>'group','count'=>true));

  list($access_types,$access_totals) = statistics_extended_groups_property_count("membership");
  $access_types = statistics_extended_label_generator($access_types,$access_totals);

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

  if(in_array('page', $resources)){
    unset($resources[current(array_keys($resources,'page_top'))]);
  }

?>
<h2><?php echo sprintf(elgg_echo("statistics:groups:counter"),$groups)?></h2>
<div id="statistics_group_graphs">

<?php  echo elgg_view("output/pie",array("internalname"=>'statistics_groups_access_graph',
										"class"=>"statistics_graph",
										"size"=>"180x100",
										"title"=>elgg_echo("statistics:access_membership"),
										"legends"=>$access_types,
										"values"=>$access_totals));

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

$column_config[]=array('display'=>elgg_echo("statistics:global:groups:access"),
	'name'=>'access',
	'width'=> 40,
	'sortable'=>false,
	'align'=>'center'
);

foreach($resources as $resource){
  $column_config[]=array('display'=>elgg_echo('statistics:global:member:'.$resource),
      'name'=>$resource,
      'width'=> 50,
      'sortable'=>false,
      'align'=>'center'
  );
}

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