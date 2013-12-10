<?php
/**
 * Groups export action
 *
 * Action that handle the group related export functions
 *
 * @package cop_statistics
 * @author Diego Ramirez <dramirezaragon@gmail.com>
 * @link http://lowfill.org
 */
elgg_load_library('statistics_extended:lib');
elgg_load_library('statistics_extended:export:lib');
elgg_load_library('statistics_extended:excel:lib');

$group_guid = get_input("group_guid");
$type = get_input("type", "global");

if (! empty($group_guid)) {
  $group = get_entity($group_guid);
  if (! empty($group) && $group->canEdit()) {

    $contents = "";
    switch ($type) {
      case "global" :
        $contents = statistics_extended_export_group_global_data($group);
        break;
      case "resources" :
        $contents = statistics_extended_export_group_resources_data($group);
        break;
    }
    if (! empty($contents)) {
      $file_name = date("Y-m-d") . "-";
      $file_name .= "statistics_{$type}_";
      $file_name .= statistics_extended_clean_string($group->name) . "_";
      $file_name .= ".xlsx";

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $file_name . '"');
      header('Cache-Control: max-age=0');

      $objWriter = PHPExcel_IOFactory::createWriter($contents, 'Excel2007');
      $objWriter->save('php://output');
      exit();
    }
    else {
      register_error(elgg_echo("statistics:error:groups:no_data"));
    }
  }
  else {
    register_error(elgg_echo("statistics:error:groups:not_authorized"));
  }
  forward("groups/{$group->guid}/{$group->name}");
}
else {
  register_error(elgg_echo("statistics:error:groups:empty_group"));
}