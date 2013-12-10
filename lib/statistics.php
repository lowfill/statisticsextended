<?php


/**
 * Return the number of active and inactive users in the site.
 *
 * If a group is specified the query is restricted to that group only
 *
 * @param $group optional group id
 * @return mixed an array (members_count,non_members_count)
 */
function statistics_extended_active_count($group=null){
	global $CONFIG;
	$query_tpl ="SELECT COUNT(*) as members FROM {$CONFIG->dbprefix}users_entity ue ";
	$query_tpl.="JOIN {$CONFIG->dbprefix}entities e ON e.guid=ue.guid WHERE e.enabled='yes' AND ";

	if(!empty($group)){
		$query_tpl.=" e.guid IN (SELECT guid_one FROM {$CONFIG->dbprefix}entity_relationships WHERE relationship='member' AND guid_two={$group}) AND ";
	}
	$query_tpl.=" last_login {{CONDITION}} 0";
	// Active users
	$query = str_replace("{{CONDITION}}","!=",$query_tpl);
	$count = get_data_row($query);
	$active = $count->members;

	$query = str_replace("{{CONDITION}}","=",$query_tpl);
	$count = get_data_row($query);
	$inactive = $count->members;

	return array($active,$inactive);
}

/**
 * Return the number of visits made from group members
 * @param $group group
 * @return integer
 */
function statistics_extended_members_views($group){
	global $CONFIG;
	if(!empty($group)){
		$query = "SELECT sum(m.string) as count FROM {$CONFIG->dbprefix}annotations a, {$CONFIG->dbprefix}metastrings m ";
		$views_counter_id = get_metastring_id("views_counter");
		$query.= "WHERE name_id={$views_counter_id} ";
		$query.= "AND entity_guid={$group} ";
		$query.= "AND owner_guid IN (SELECT guid_one FROM {$CONFIG->dbprefix}entity_relationships WHERE relationship='member' AND guid_two={$group}) ";
		$query.= "AND a.value_id = m.id";
		$count = get_data_row($query);
		return $count->count;
	}
	return 0;
}
/**
 * Return the groups count that have the specified property
 * @param $property
 * @return mixed (labels,totals)
 */
function statistics_extended_groups_property_count($property){
	global $CONFIG;
	$properties = $CONFIG->group;
	$labels = array();
	$totals = array();
	if(array_key_exists($property,$properties)){
		list($type,$values) = $properties[$property];
		switch($type){
			case "checkboxes":
			case "radio":
				foreach($values as $label=>$value){
					$options = array("types"=>"group","count"=>true,"metadata_names"=>$property,"metadata_values"=>$value);
					$total = elgg_get_entities_from_metadata($options);
					$labels[]=$value;
					$totals[$value]=$total;
				}
				break;
			case "organizational_unit":
				$query = "SELECT string FROM {$CONFIG->dbprefix}metastrings WHERE id IN ";
				$query.="(SELECT value_id FROM {$CONFIG->dbprefix}metadata WHERE name_id=";
				$query.="(SELECT id FROM {$CONFIG->dbprefix}metastrings WHERE string='{$property}'))";
				$categories = get_data($query);
				if(!empty($categories)){
					foreach($categories as $category){
						$category = $category->string;
						$options = array("types"=>"group","count"=>true,"metadata_names"=>$property,"metadata_values"=>$category);
						$total = elgg_get_entities_from_metadata($options);
						list($section,$department,$unit) = explode("||",$category);

						$labels[$section]=$section;
						$totals[$section]+=$total;
					}
				}
				break;
			case "status":
				$options = array("types"=>"group","count"=>true);
				$all_groups = elgg_get_entities($options);
				$values = array("preparation"=>"groups:extras:status:preparation",
								"active"=>"groups:extras:status:active",
								"inactive"=>"groups:extras:status:inactive",
								"closed"=>"groups:extras:status:closed");
				foreach($values as $value=>$label){
					$options = array("types"=>"group","count"=>true,"metadata_names"=>$property,"metadata_values"=>$value);
					$total = elgg_get_entities_from_metadata($options);
					$labels[]=$label;
					$totals[$label]=(int)$total;
				}
				$totals["groups:extras:status:active"]=$all_groups-$totals["groups:extras:status:inactive"]-$totals["groups:extras:status:closed"];

				break;

		}
	}
	else{
		switch($property){
			case "content_privacy":
				$options = array("types"=>"group","count"=>true,"metadata_names"=>$property,"metadata_values"=>"no");
				$count_no = elgg_get_entities_from_metadata($options);
				$options = array("types"=>"group","count"=>true,);
				$all_groups = elgg_get_entities($options);

				// How content_privacy is a new feature it is no available for all groups
				$labels[]="yes";
				$totals["yes"]=$all_groups - $count_no;
				$labels[]="no";
				$totals["no"]=$count_no;
				break;
			case "membership":
				$values = array( ACCESS_PRIVATE => elgg_echo('groups:access:private'), ACCESS_PUBLIC => elgg_echo('groups:access:public'));
				foreach($values as $value=>$label){
					$options = array("types"=>"group","count"=>true,"metadata_names"=>$property,"metadata_values"=>$value);
					$total = elgg_get_entities_from_metadata($options);
					$labels[]=$value;
					$totals[$value]=$total;
				}
		}
	}
	return array($labels,$totals);
}

/**
 * Return an array with total number of objects from the specified subtypes
 *
 * @param $object_types array object subtypes
 * @param $container_guid null or group_guid
 * @param $owner_guid null or owner_guid
 * @return array
 */
function statistics_extended_objects_count($object_types,$container_guid=null,$owner_guid=null){
	if(!is_array($object_types)){
		$object_types = array($object_types);
	}
	$resp = array();
	foreach($object_types as $object_type){
		$resp[$object_type] = statistics_extended_object_count($object_type,$owner_guid,$container_guid);
	}
	return $resp;
}

/**
 * Return the number of object from the specified type
 * @param $object_type Object subtype
 * @param $owner_guid
 * @param $container_guid
 * @return int
 */
function statistics_extended_object_count($object_type,$owner_guid,$container_guid=null){
	$options = array(
		'types'=>'object',
		'subtypes'=>$object_type,
		'count'=>true
	);
	if($owner_guid!=null){
		$options['owner_guids']=$owner_guid;
	}
	if($container_guid!=null){
		$options['container_guids']=$container_guid;
	}
	$count = elgg_get_entities($options);
	$count = trigger_plugin_hook("cop_statistics:object:count", "object",$options,$count);
	return $count;
}

/**
 * Return the number of views from the specified object subtypes
 * @param $object_types array
 * @param $container_guid
 * @param $owner_guid
 * @return array
 */
function statistics_extended_objects_view_count($object_types,$container_guid=null,$owner_guid=null){
	if(!is_array($object_types)){
		$object_types = array($object_types);
	}
	$resp = array();
	foreach($object_types as $object_type){
		$resp[$object_type] = statistics_extended_object_view_count($object_type,$owner_guid,$container_guid);
	}
	return $resp;
}

/**
 * Return the number of views from the specified group type
 * @param $object_type
 * @param $owner_guid
 * @param $container_guid
 * @return int
 */
function statistics_extended_object_view_count($object_type,$owner_guid,$container_guid=null){
	$options = array(
		'types'=>'object',
		'subtypes'=>$object_type,
		'owner_guids'=>$owner_guid,
		'count'=>true
	);
	if($container_guid!=null){
		$options['container_guids']=$container_guid;
	}
	$total_views = 0;
	//TODO Add cache
	$elements_count = elgg_get_entities($options);
	$options['count']=false;
	$options['limit']=100;
	for($i=0;$i<$elements_count;$i+=100){
		$options['offset']=$i;
		$entities = elgg_get_entities($options);
		if(!empty($entities)){
			foreach($entities as $entity){
				$total_views+=get_views_counter($entity->guid);
			}
		}
	}
	$total_views = trigger_plugin_hook("cop_statistics:object:view:count", "object",$options,$total_views);

	return $total_views;
}


/**
 * Return the users registration information throught time
 * @param $zoom
 * @param $start_date
 * @param $finish_date
 * @return mixed
 */
function statistics_extended_users_timeline($zoom="%y-%U",$start_date="",$finish_date=""){
	global $CONFIG;
	$query= "SELECT date_format(from_unixtime(time_created),'{$zoom}')as zoom, time_created, count(*) total,'create' as event ";
	$query.="FROM {$CONFIG->dbprefix}entities ";
	$query.="WHERE type = 'user' ";
	$query.="AND enabled='yes' ";
	$query.="GROUP BY zoom";
	return get_data($query);
}

/**
 * Return the users that loggedin throught time
 * @param $zoom
 * @param $start_date
 * @param $finish_date
 * @return mixed
 */
function statistics_extended_logins_timeline($zoom="%y-%U",$start_date="",$finish_date=""){
	global $CONFIG;
	$query= "SELECT date_format(from_unixtime(time_created),'{$zoom}')as zoom, time_created, count(*) total,event ";
	$query.="FROM {$CONFIG->dbprefix}system_log ";
	$query.="WHERE event IN ('login') ";
	$query.="AND object_type='user' ";
	$query.="GROUP BY zoom,event";
	return get_data($query);
}

/**
 * Return the groups registration information throught time
 * @param $zoom
 * @param $start_date
 * @param $finish_date
 * @return mixed
 */
function statistics_extended_groups_timeline($zoom="%y-%U",$start_date="",$finish_date=""){
	global $CONFIG;
	$query= "SELECT date_format(from_unixtime(time_created),'{$zoom}')as zoom, time_created, count(*) total,event ";
	$query.="FROM {$CONFIG->dbprefix}system_log ";
	$query.="WHERE event IN ('create','delete') ";
	$query.="AND object_type='group' ";
	$query.="GROUP BY zoom,event";

	return get_data($query);
}

/**
 * Helper function for generate table headers
 *
 * @param $labels
 * @param $values
 * @param $prefix
 * @return mixed
 */
function statistics_extended_label_generator($labels,$values=array(),$prefix="statistics:label:"){
	$resp = array();
	foreach($labels as $label){
		if(empty($prefix)){
			$resp[]="{$label} ({$values[$label]})";
		}
		else{
			$resp[]=sprintf(elgg_echo("{$prefix}{$label}"),$values[$label]);
		}
	}
	return $resp;
}

function statistics_extended_tool_object($tool_name){
  $resp = array();
  switch($tool_name){
  	case 'forum':
  	  $resp[]='groupforumtopic';
  	  break;
  	case 'pages':
  	  $resp[]='page';
  	  $resp[]='page_top';
  	  break;
  	case 'photos':
  	  $resp[]='album';
  	  break;
  	case 'polls':
  	  $resp[]='poll';
  	  break;
  	default:
  	  $resp[]=$tool_name;
  }
  return $resp;
}