<?php

/**
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

    ?>
				<?php echo $html; ?>
			</table>
}

?>