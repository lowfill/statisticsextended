<?php
/**
 * Return a CSV with the global information from a specified group
 * @param $group
 * @param $cached
 * @return string
 */
function statistics_extended_export_group_global_data($group,$cached=false){
  //TODO Migrate this to PHPExcel
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
    $headers = statistics_extended_label_generator($headers,null,"statistics:groups:member:");
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

          $values = statistics_extended_objects_count($items,$group->guid,$entity->guid);
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
function statistics_extended_export_group_resources_data($group){
  //TODO Migrate this to PHPExcel
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
    $headers = statistics_extended_label_generator($headers,null,"statistics:groups:resources:");
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
function statistics_extended_export_global_resources_data(){
  //TODO Migrate this to PHPExcel
  $resp = "";
  $options = array('types'=>'object',
      'subtypes'=>array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top"),
      'count'=>true,
      'limit'=>50);

  $count = get_entities_by_views_counter($options);
  $options['count']=false;
  if($count>0){
    $headers = array("guid","group","type","title","user","visits");
    $headers = statistics_extended_label_generator($headers,null,"statistics:global:resources:");
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
 * Return a PHPExcel object with the site information
 * @param $cached
 * @return object
 */
function statistics_extended_export_global_data($cached=false){
  $output = new PHPExcel();
  $output->getProperties()->setCreator(elgg_get_site_entity()->name)
  ->setLastModifiedBy(elgg_get_site_entity()->name)
  ->setTitle(elgg_echo('statistics:global'))
  ->setSubject(elgg_echo('statistics:global'))
  ->setDescription(elgg_echo('statistics:global'));

  $output->setActiveSheetIndex(0);

  $items = array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top");

  $options = array('types'=>'user',
      'count'=>true,
      'limit'=>50);

  $count = elgg_get_entities($options);
  $options['count']=false;

  if($count>0){
    $headers = array("guid","name","email","location","active","blog","file","bookmark","event","discussion","page");
    $headers = statistics_extended_label_generator($headers,null,"statistics:global:member:");
    $headers=array_map('elgg_echo',$headers);
    statistics_extended_export_generate_cell($output,$headers);
    for($i=0,$j=2;$i<$count;$i+=50){
      $options['offset']=$i;
      $entities = elgg_get_entities($options);
      if(!empty($entities)){
        foreach($entities as $entity){
          $row = array();
          $row[]=$entity->guid;
          $row[]=$entity->name;
          $email = (!empty($entity->contactemail)) ? $entity->contactemail : $entity->email;
          $row[]=$email;
          $row[]=$entity->location;

          $active = elgg_echo("option:no");
          if($entity->last_login > 0){ // User enter at least one time
            $active = elgg_echo("option:yes");
          }
          $row[] = $active;

          $values = statistics_extended_objects_count($items,null,$entity->guid);
          $values["page"]+=$values["page_top"];
          array_pop($values);

          $row = array_merge($row,array_values($values));

          statistics_extended_export_generate_cell($output,$row,$j);
          $j++;
        }
      }
    }
  }
  return $output;
}

/**
 * Return a CSV string with the global information for groups
 * @param $cached
 * @return string
 */
function statistics_extended_export_global_group_data($cached=false){
  //TODO Migrate this to PHPExcel
  $resp = "";
  $items = array("blog","file","bookmarks","event_calendar","groupforumtopic","page","page_top");

  $options = array('types'=>'group',
      'count'=>true);

  $count = elgg_get_entities($options);
  $options['count']=false;
  $options['limit']=50;

  if($count>0){
    $headers = array("guid","name","type","section","department","unit","impact_contribution","impact_contribution_category","status","access","blog","file","bookmark","event","discussion","page");
    $headers = statistics_extended_label_generator($headers,null,"statistics:global:groups:");
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
          $values = statistics_extended_objects_count($items,$entity->guid);
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

function statistics_extended_export_generate_cell(&$output,$values,$row=1){
  for($i=0;$i<count($values);$i++){
    $output->getActiveSheet()->setCellValueByColumnAndRow($i,$row,$values[$i]);
  }
}

