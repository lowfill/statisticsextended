<?php
/**
 * Global statistics controller
 * 
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */


admin_gatekeeper();

$section = get_input('section','index');

set_context('admin');

$body = elgg_view('cop_statistics/admin/invalid_view');
if(isadminloggedin()){
	if(elgg_view_exists("cop_statistics/admin/{$section}")){
		$body = elgg_view("cop_statistics/admin/container",array('view'=>$section));
	}
	else{
		$body = "<p>".sprintf(elgg_echo("statistics:error:invalid_view"),$section)."</p>";
	}
}
else{
		$body = "<p>".elgg_echo("statistics:error:not_access")."</p>";
}

$title = elgg_echo('statistics:admin:manager');
$area2 = elgg_view_title($title);

$area2 .= elgg_view('page_elements/contentwrapper', array('body' => $body));
$body = elgg_view_layout('two_column_left_sidebar',$area1, $area2);

// Finally draw the page
page_draw($title, $body);

?>