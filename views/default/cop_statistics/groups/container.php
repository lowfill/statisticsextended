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
		'index'=>array('url'=>'pg/group_statistics/{{guid}}/','label'=>elgg_echo('statistics:global')),
		'resources'=>array('url'=>'pg/group_statistics/{{guid}}/resources/','label'=>elgg_echo('statistics:resources')),
);
$view = $vars['view'];
if($view == "details"){
	$options["details"]=array('url'=>'pg/group_statistics/{{guid}}/','label'=>elgg_echo('statistics:details'));
}
?>
<div id="elgg_horizontal_tabbed_nav">
<ul>
<?php
foreach($options as $key=>$option){
	$selected = ($key == $view)? " class=\"selected\" ":"";
	$url = $vars['url'].$option['url'];
	$url = str_replace('{{guid}}',$vars['group_guid'],$url);

?>

	<li <?php echo $selected; ?>><a href="<?php echo $url?>"><?php echo $option['label']; ?></a></li>

	<?php }?>
</ul>
</div>

<?php echo elgg_view("cop_statistics/groups/{$view}",$vars);?>