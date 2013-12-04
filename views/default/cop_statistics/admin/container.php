<?php
/**
 * Global statistics container
 * 
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 * Parameters
 * @var view
 */

$options = array(
		'index'=>array('url'=>'pg/global_statistics/index','label'=>elgg_echo('statistics:global')),
		'groups'=>array('url'=>'pg/global_statistics/groups','label'=>elgg_echo('statistics:groups')),
		'resources'=>array('url'=>'pg/global_statistics/resources','label'=>elgg_echo('statistics:resources')),
		'evolution'=>array('url'=>'pg/global_statistics/evolution','label'=>elgg_echo('statistics:evolution')),
);
$view = $vars['view'];
?>
<div id="elgg_horizontal_tabbed_nav">
<ul>
<?php
foreach($options as $key=>$option){
	$selected = ($key == $view)? " class=\"selected\" ":"";
	$url = $vars['url'].$option['url'];

?>

	<li <?php echo $selected; ?>><a href="<?php echo $url?>"><?php echo $option['label']; ?></a></li>

	<?php }?>
</ul>
</div>

<?php echo elgg_view("cop_statistics/admin/{$view}",$vars);?>