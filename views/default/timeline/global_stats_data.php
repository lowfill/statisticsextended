<?php
/**
 * Timiline users and groups statistics view
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */


statistics_extended_load_library();

$zoom = get_input('zoom','weekly"');
$serie = get_input('serie');

$resp = array();
$data = array();
$data_source = array();
switch($zoom){
	case "dayly":
		$zoom="%y-%m-%d";
		break;
	case "weekly":
		$zoom="%y-%U";
		break;
	case "monthly":
		$zoom="%y-%m";
		break;
}
switch($serie){
	case "users":
		$data_source = statistics_extended_users_timeline($zoom);
		$color = "blue";
		break;
	case "logins":
		$data_source = statistics_extended_logins_timeline($zoom);
		$color = "red";
		break;
	case "groups":
		$data_source = statistics_extended_groups_timeline($zoom);
		$color = "yellow";
		break;
}
$total=0;
foreach($data_source as $data_line){
	$value = $data_line->total;
	if(in_array($data_line->event,array('create','delete'))){
		$total+=($data_line->event=="create" || $data_line->event=='login')?$data_line->total:($data_line->total * -1);
		$value = $total;
	}
	$data[]=array($data_line->time_created*1000,$value);
}

$resp["label"]=elgg_echo("statistics:timeline:{$serie}");
$resp["data"]=$data;
$resp["color"]=$color;

echo json_encode($resp);
?>