<?php

/** * @file views/default/views_counter/views_statistics.php * @brief Displays the views statistics for one entity */
elgg_load_css('views_counter:css');
$entity = ($vars['entity']) ? ($vars['entity']) : (get_entity(get_input('entity_guid')));

if ($entity) {

  $options = array(
      'guid' => $entity->guid,
      'limit' => 20,
      'annotation_names'=>'views_counter',
      'order_by' => "n_table.time_created asc",
      'item_class'=>"statistics-views-item"
  );

  //TODO Revisar por que se esta generando el código de lista
  $html = elgg_list_annotations($options);

    ?><table class='views-counter-display-table'>	<tr>		<th class="id_column"><?php echo elgg_echo('views_counter:id'); ?></th>		<th class="user_name_column"><?php echo elgg_echo('views_counter:user_name'); ?></th>		<th class="views_column"><?php echo elgg_echo('views_counter:views_by_user'); ?></th>		<th class="first_view_column"><?php echo elgg_echo('views_counter:first_view'); ?></th>	</tr>
				<?php echo $html; ?>
			</table><?php
}

?>
