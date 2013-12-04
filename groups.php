<?php
/**
 * Group statistics controller
 * 
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */


gatekeeper();

$group = get_input('group_guid');
$section = get_input('section','index');

set_context('groups');
set_page_owner($group);

$body = elgg_view('cop_statistics/groups/invalid_group');
if(!empty($group) && page_owner_entity()->canEdit()){
	if(elgg_view_exists("cop_statistics/groups/{$section}")){
		$body = elgg_view("cop_statistics/groups/container",array('group_guid'=>$group,'view'=>$section));
	}
	else{
		$body = "<p>".sprintf(elgg_echo("statistics:error:invalid_view"),$section)."</p>";
	}
}
else{
		$body = "<p>".elgg_echo("statistics:error:not_access")."</p>";
}

$title = elgg_echo('statistics:groups:manager');
$area2 = elgg_view_title($title);

$area2 .= elgg_view('page_elements/contentwrapper', array('body' => $body));
$body = elgg_view_layout('two_column_left_sidebar',$area1, $area2);

// Finally draw the page
page_draw($title, $body);

?>