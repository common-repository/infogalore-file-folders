<tr class="igf-adm-file" data-file-id="<?php echo $file_id; ?>">
	<td class="igf-adm-icon">
		<img src="<?php echo $icon; ?>" class="xxigf-adm-file-icon">
	</td>
	<td class="igf-adm-name">
		<div class="igf-adm-title"><?php echo htmlspecialchars( $title ); ?></div>
		<a href="<?php echo $url; ?>"
		   class="igf-adm-filename"><?php echo htmlspecialchars( $filename ); ?></a>
	</td>
	<td class="igf-adm-size"><?php echo $size; ?></td>
	<td class="igf-adm-shortcode">
		<input type="text" size="<?php echo strlen( $shortcode ); ?>" readonly="readonly"
			   value="<?php echo htmlspecialchars( $shortcode ); ?>"></td>
	<td class="igf-adm-downloads"><?php echo $downloads; ?></td>
	<td class="igf-adm-actions">
		<a class="igf-adm-edit" href="#"
		   title="<?php _e( 'Edit', 'infogalore-folders' ); ?>">
			<span class="dashicons dashicons-info"></span>
		</a>
		<a href="#" class="igf-adm-remove"
		   title="<?php _e( 'Remove', 'infogalore-folders' ); ?>">
			<span class="dashicons dashicons-dismiss"></span>
		</a>
	</td>
</tr>