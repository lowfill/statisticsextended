<?php
/**
 * Global evolution view
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */

  elgg_load_css('statistics_extended:css');
  $users = elgg_get_entities(array('types'=>'user','count'=>true));
  $groups = elgg_get_entities(array('types'=>'group','count'=>true));

?>

<h2><?php echo sprintf(elgg_echo("statistics:users:counter"),$users)?></h2>
<h2><?php echo sprintf(elgg_echo("statistics:groups:counter"),$groups)?></h2>
<br>
<?php
echo elgg_view('output/timeline',array("internalname"=>"cop_statistics",
									   "series"=>array("users"=>elgg_echo("statistics:timeline:users"),
													   "logins"=>elgg_echo("statistics:timeline:logins"),
									   				   "groups"=>elgg_echo("statistics:timeline:groups")),
									   "default_series"=>array("users","logins"),
									   "endpoint"=>"global_stats",
									   "start_date"=>"2009",
									   "width"=>"500",
									   "height"=>"350"

				));
?>