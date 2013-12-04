<?php

/**
 * Return the number of internal and external users in the site.
 *
 * If a group is specified the query is restricted to that group only
 *
 * @param $group optional group id
 * @return mixed an array (members_count,non_members_count)
 */
function cop_statistics_members_count($group=null){
	global $CONFIG;
	$query_tpl ="SELECT COUNT(*) as members FROM {$CONFIG->dbprefix}users_entity WHERE ";
	if(!empty($group)){
		$query_tpl.=" guid IN (SELECT guid_one FROM {$CONFIG->dbprefix}entity_relationships WHERE relationship='member' AND guid_two={$group}) AND ";
	}
	$query_tpl.=" email {{CONDITION}} '%iadb.org'";
	// IADB Users
	$query = str_replace("{{CONDITION}}","LIKE",$query_tpl);
	$count = get_data_row($query);
	$members = $count->members;

	$query = str_replace("{{CONDITION}}","NOT LIKE",$query_tpl);
	$count = get_data_row($query);
	$notmembers = $count->members;

	return array($members,$notmembers);
}

/**
 * Return the number of active and inactive users in the site.
 *
 * If a group is specified the query is restricted to that group only
 *
 * @param $group optional group id
 * @return mixed an array (members_count,non_members_count)
 */
function cop_statistics_active_count($group=null){
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
function cop_statistics_members_views($group){
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
function cop_statistics_groups_property_count($property){
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
function cop_statitics_objects_count($object_types,$container_guid=null,$owner_guid=null){
	if(!is_array($object_types)){
		$object_types = array($object_types);
	}
	$resp = array();
	foreach($object_types as $object_type){
		$resp[$object_type] = cop_statistics_object_count($object_type,$owner_guid,$container_guid);
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
function cop_statistics_object_count($object_type,$owner_guid,$container_guid=null){
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
function cop_statitics_objects_view_count($object_types,$container_guid=null,$owner_guid=null){
	if(!is_array($object_types)){
		$object_types = array($object_types);
	}
	$resp = array();
	foreach($object_types as $object_type){
		$resp[$object_type] = cop_statistics_object_view_count($object_type,$owner_guid,$container_guid);
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
function cop_statistics_object_view_count($object_type,$owner_guid,$container_guid=null){
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
 * Return a CSV with the global information from a specified group
 * @param $group
 * @param $cached
 * @return string
 */
function cop_statistics_export_group_global_data($group,$cached=false){
	$resp = "";
	$items = array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top");

	$options = array('types'=>'user',
				 'count'=>true,
				 'limit'=>50,
				 "relationship"=>"member",
				 "relationship_guid"=>$group->guid,
				 "inverse_relationship"=>true);

	$count = elgg_get_entities_from_relationship($options);
	$options['count']=false;

	if($count>0){
		$headers = array("guid","name","email","country","actor_type","experience_theme","internal","blog","file","bookmark","event","discussion","page");
		$headers = cop_statistics_label_generator($headers,null,"statistics:groups:member:");
		$resp=implode(",",array_map('elgg_echo',$headers))."\n";
		for($i=0;$i<$count;$i+=50){
			$options['offset']=$i;
			$entities = elgg_get_entities_from_relationship($options);
			if(!empty($entities)){
				foreach($entities as $entity){
					$row = array();
					$row[]=$entity->guid;
					$name= mb_convert_encoding($entity->name, 'UTF-16LE', 'UTF-8');
					$row[]="\"$name\"";
					$email = (!empty($entity->contactemail)) ? $entity->contactemail : $entity->email;
					$row[]=$email;
					
					$location_var = "cfkn_mpr:country";
					$country = elgg_echo($entity->$location_var);
					$country= mb_convert_encoding($country, 'UTF-16LE', 'UTF-8');
					$row[]=$country;
					
					$actor_type = "cfkn_mpr:actor_type";
					$actor = elgg_echo($entity->$actor_type);
					$actor= mb_convert_encoding($actor, 'UTF-16LE', 'UTF-8');
					$row[]=$actor;

					$experience_theme = "cfkn_mpr:experience_theme";
					$experience_theme = elgg_echo($entity->$experience_theme);
					$experience_theme= mb_convert_encoding($experience_theme, 'UTF-16LE', 'UTF-8');
					$row[]=$experience_theme;

					
					$internal = elgg_echo("option:no");
					if(strpos($email,"@iadb.org")>0){
						$internal = elgg_echo("option:yes");
					}
					$row[] = $internal;

					$values = cop_statitics_objects_count($items,$group->guid,$entity->guid);
					$values["page"]+=$values["page_top"];
					array_pop($values);

					foreach($values as $key=>$value){
						$row[]=$value;
						$total_var_name = "{$key}_count_total";
						$$total_var_name+=$value;
					}
					$resp.=implode(",",$row)."\n";
				}
			}
		}
		$page_count_total+=$page_top_count_total;
		$resp.=",,,,,,,$blog_count_total,$file_count_total,$bookmarks_count_total,$event_calendar_count_total,$groupforumtopic_count_total,$page_count_total\n";
	}
	return $resp;
}

/**
 * Returns a CSV string with the group resources information
 * @param $group
 * @return string
 */
function cop_statistics_export_group_resources_data($group){
	$resp = "";
	$options = array('types'=>'object',
				 'count'=>true,
				 'limit'=>50,
				 "container_guids"=>$group->guid);

	//$count = elgg_get_entities($options);
	$count = get_entities_by_views_counter($options);
	$options['count']=false;
	if($count>0){
		$headers = array("guid","type","title","user","visits");
		$headers = cop_statistics_label_generator($headers,null,"statistics:groups:resources:");
		$resp=implode(",",array_map('elgg_echo',$headers))."\n";
		for($i=0;$i<$count;$i+=50){
			$options['offset']=$i;
			$entities = get_entities_by_views_counter($options);
			if(!empty($entities)){
				foreach($entities as $entity){
					$visits = $entity->countAnnotations('views_counter');
					if($visits>0){
						for($j=0;$j<$visits;$j+=50){
							$visitors = $entity->getAnnotations("views_counter", 50, $j);
							if (!empty($visitors)){
								foreach($visitors as $visitor){
									$row = array();
									$row[]=$entity->guid;
									$row[]=elgg_echo("statistics:label:type:".$entity->getSubtype());
									$title= mb_convert_encoding($entity->title, 'UTF-16LE', 'UTF-8');
									$row[]="\"".$title."\"";
									$visitor_name= mb_convert_encoding($visitor->getOwnerEntity()->name, 'UTF-16LE', 'UTF-8');
									$row[]="\"".$visitor_name."\"";
									$row[]=$visitor->value;
									$resp.=implode(",",$row)."\n";
								}
							}
						}

					}
					else{
						$row = array();
						$row[]=$entity->guid;
						$row[]=elgg_echo("statistics:label:type:".$entity->getSubtype());
						$title= mb_convert_encoding($entity->title, 'UTF-16LE', 'UTF-8');
						$row[]="\"".$title."\"";
						$row[]="";
						$row[]=0;
						$resp.=implode(",",$row)."\n";
					}
				}
			}
		}
	}
	return $resp;
}

/**
 * Returns a CSV string with the global resources information
 * @return string
 */
function cop_statistics_export_global_resources_data(){
	$resp = "";
	$options = array('types'=>'object',
				 'subtypes'=>array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top"),
				 'count'=>true,
				 'limit'=>50);

	$count = get_entities_by_views_counter($options);
	$options['count']=false;
	if($count>0){
		$headers = array("guid","group","type","title","user","visits");
		$headers = cop_statistics_label_generator($headers,null,"statistics:global:resources:");
		$resp=implode(",",array_map('elgg_echo',$headers))."\n";
		for($i=0;$i<$count;$i+=50){
			$options['offset']=$i;
			$entities = get_entities_by_views_counter($options);
			if(!empty($entities)){
				foreach($entities as $entity){
					$visits = $entity->countAnnotations('views_counter');
					if($visits >0 ){
						for($j=0;$j<$visits;$j+=50){
							$visitors = $entity->getAnnotations("views_counter", 50, $j);
							if (!empty($visitors)){
								foreach($visitors as $visitor){
									$row = array();
									$row[]=$entity->guid;
									$group = "";
									$container = get_entity($entity->container_guid);
									if(!empty($container) && $container instanceof ElggGroup){
										$name= mb_convert_encoding($container->name, 'UTF-16LE', 'UTF-8');
										$group = "\"".$name."\"";
									}
									$row[]=$group;
									$row[]=elgg_echo("statistics:label:type:".$entity->getSubtype());
									$title= mb_convert_encoding($entity->title, 'UTF-16LE', 'UTF-8');
									$row[]="\"".$title."\"";
									$visitor_name= mb_convert_encoding($visitor->getOwnerEntity()->name, 'UTF-16LE', 'UTF-8');
									$row[]="\"".$visitor_name."\"";
									$row[]=$visitor->value;
									$resp.=implode(",",$row)."\n";
								}
							}
						}
					}
					else{
						$row = array();
						$row[]=$entity->guid;
						$group = "";
						$container = get_entity($entity->container_guid);
						if(!empty($container) && $container instanceof ElggGroup){
							$name= mb_convert_encoding($container->name, 'UTF-16LE', 'UTF-8');
							$group = "\"".$name."\"";
						}
						$row[]=$group;
						$row[]=elgg_echo("statistics:label:type:".$entity->getSubtype());
						$title= mb_convert_encoding($entity->title, 'UTF-16LE', 'UTF-8');
						$row[]="\"".$title."\"";
						$row[]="";
						$row[]=0;
						$resp.=implode(",",$row)."\n";

					}
				}
			}
		}
	}
	return $resp;
}

/**
 * Return a CSV string with the site information
 * @param $cached
 * @return string
 */
function cop_statistics_export_global_data($cached=false){
	$resp = "";
	$items = array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top");

	$options = array('types'=>'user',
				 'count'=>true,
				 'limit'=>50);

	$count = elgg_get_entities($options);
	$options['count']=false;

	if($count>0){
		$headers = array("guid","name","email","country","actor_type","experience_theme","internal","active","country","state","city","blog","file","bookmark","event","discussion","page");
		$headers = cop_statistics_label_generator($headers,null,"statistics:global:member:");
		$resp=implode(",",array_map('elgg_echo',$headers))."\n";
		for($i=0;$i<$count;$i+=50){
			$options['offset']=$i;
			$entities = elgg_get_entities($options);
			if(!empty($entities)){
				foreach($entities as $entity){
					$row = array();
					$row[]=$entity->guid;
					$name= mb_convert_encoding($entity->name, 'UTF-16LE', 'UTF-8');
					$row[]="\"$name\"";
					$email = (!empty($entity->contactemail)) ? $entity->contactemail : $entity->email;
					$row[]=$email;
					$location_var = "cfkn_mpr:country";
					$country = elgg_echo($entity->$location_var);
					$country= mb_convert_encoding($country, 'UTF-16LE', 'UTF-8');
					$row[]=$country;
					$actor_type = "cfkn_mpr:actor_type";
					$actor = elgg_echo($entity->$actor_type);
					$actor= mb_convert_encoding($actor, 'UTF-16LE', 'UTF-8');
					$row[]=$actor;

					$experience_theme = "cfkn_mpr:experience_theme";
					$experience_theme = elgg_echo($entity->$experience_theme);
					$experience_theme= mb_convert_encoding($experience_theme, 'UTF-16LE', 'UTF-8');
					$row[]=$experience_theme;
					$internal = elgg_echo("option:no");
					if(strpos($email,"@iadb.org")>0){
						$internal = elgg_echo("option:yes");
					}
					$row[] = $internal;
					
					$active = elgg_echo("option:no");
					if($entity->last_login > 0){ // User enter at least one time
						$active = elgg_echo("option:yes");
					}
					$row[] = $active;
						
					list($country,$state,$city) = explode("||",$entity->location);
					$row[]=$country;
					$row[]="\"".$state."\"";
					$row[]="\"".$city."\"";

					$values = cop_statitics_objects_count($items,$group->guid,$entity->guid);
					$values["page"]+=$values["page_top"];
					array_pop($values);

					foreach($values as $key=>$value){
						$row[]=$value;
						$total_var_name = "{$key}_count_total";
						$$total_var_name+=$value;
					}
					$resp.=implode(",",$row)."\n";
				}
			}
		}
		$page_count_total+=$page_top_count_total;
		$resp.=",,,,,,,$blog_count_total,$file_count_total,$bookmarks_count_total,$event_calendar_count_total,$groupforumtopic_count_total,$page_count_total\n";
	}
	return $resp;
}

/**
 * Return a CSV string with the global information for groups
 * @param $cached
 * @return string
 */
function cop_statistics_export_global_group_data($cached=false){
	$resp = "";
	$items = array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top");

	$options = array('types'=>'group',
				 'count'=>true);

	$count = elgg_get_entities($options);
	$options['count']=false;
	$options['limit']=50;

	if($count>0){
		$headers = array("guid","name","type","section","department","unit","impact_contribution","impact_contribution_category","status","access","blog","file","bookmark","event","discussion","page");
		$headers = cop_statistics_label_generator($headers,null,"statistics:global:groups:");
		$resp=implode(",",array_map('elgg_echo',$headers))."\n";
		for($i=0;$i<$count;$i+=50){
			$options['offset']=$i;
			$entities = elgg_get_entities($options);
			if(!empty($entities)){
				foreach($entities as $entity){
					$row = array();
					$type = elgg_echo($entity->group_type);
					list($section,$department,$unit) = explode("||",$entity->organizational_unit);

					$impact_contribution=$entity->impact_contribution;
					if(is_array($impact_contribution)){
						$impact_contribution = array_map(create_function('$item','return "statistics:global:".$item;'),$impact_contribution);
						$impact_contribution = implode(",",array_map("elgg_echo",$impact_contribution));
					}
					else if(!empty($impact_contribution)){
						$impact_contribution="statistics:global:".$impact_contribution;
						$impact_contribution = elgg_echo($impact_contribution);
					}

					$impact_contribution_category=$entity->impact_contribution_category;
					if(is_array($impact_contribution_category)){
						$impact_contribution_category = array_map(create_function('$item','return "statistics:global:".$item;'),$impact_contribution_category);
						$impact_contribution_category = implode(",",array_map("elgg_echo",$impact_contribution_category));
					}
					else if(!empty($impact_contribution_category)){
						$impact_contribution_category="statistics:global:".$impact_contribution_category;
						$impact_contribution_category = elgg_echo($impact_contribution_category);
					}
						
					$status = $entity->group_status;
					if(empty($status)){
						$status = "active";
					}
					$status = elgg_echo("groups:extras:status:$status");

					$access = elgg_echo("option:no");
					if($entity->content_privacy=="yes"){
						$access = elgg_echo("option:yes");
					}

					$row[]=$entity->guid;
					$name= mb_convert_encoding($entity->name, 'UTF-16LE', 'UTF-8');
					$row[]="\"$name\"";
					$row[]="\"$type\"";
					$row[]="\"$section\"";
					$row[]="\"$department\"";
					$row[]="\"$unit\"";
					$row[]="\"$impact_contribution\"";
					$row[]="\"$impact_contribution_category\"";
					$row[]="\"$status\"";
					$row[]="\"$access\"";
					$values = cop_statitics_objects_count($items,$entity->guid);
					$values["page"]+=$values["page_top"];
					array_pop($values);

					foreach($values as $key=>$value){
						$row[]=$value;
						$total_var_name = "{$key}_count_total";
						$$total_var_name+=$value;
					}
					$resp.=implode(",",$row)."\n";
				}
			}
		}
	}
	return $resp;
}

/**
 * Return the users registration information throught time
 * @param $zoom
 * @param $start_date
 * @param $finish_date
 * @return mixed
 */
function cop_statistics_users_timeline($zoom="%y-%U",$start_date="",$finish_date=""){
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
function cop_statistics_logins_timeline($zoom="%y-%U",$start_date="",$finish_date=""){
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
function cop_statistics_groups_timeline($zoom="%y-%U",$start_date="",$finish_date=""){
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
function cop_statistics_label_generator($labels,$values=array(),$prefix="statistics:label:"){
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
