<?php
	/**
	 * @file views/default/annotation/views_counter.php
	 * @brief Displays the annotations "views_counter" while showing the entity views statistics
	 */
?>

<tr>
	<td class="id_column"><?php echo $vars['annotation']->id; ?></td>
	<td class="user_name_column">
		<?php
			$owner = get_entity($vars['annotation']->owner_guid);
			if ($owner) {
		?>
				<a href="<?php echo $owner->getUrl(); ?>"><?php echo $owner->name; ?></a>
		<?php
			} else {
				echo elgg_echo('views_counter:not_loggedin');
			}
		?>
	</td>
	<td class="views_column"><?php echo $vars['annotation']->value; ?></td>
	<td class="first_view_column"><?php echo friendly_time($vars['annotation']->time_created); ?></td>
</tr>