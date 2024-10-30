<div id="igf-adm-subfolders-metabox">
	<ul id="igf-adm-folder-path">
		<li>
			<a href="<?php echo admin_url( "edit.php?post_type=$cpt&f_roots=on" ); ?>"><?php _e( 'Roots', 'infogalore-folders' ); ?></a>
		</li>
		<?php foreach ( array_reverse( $folder->ancestors() ) as $ancestor ) { ?>
			<li>/
				<a href="<?php echo admin_url( "post.php?post={$ancestor->ID}&action=edit" ); ?>"><?php echo htmlspecialchars( $ancestor->title ); ?></a>
				<?php _post_states( $ancestor->post ); ?>
			</li>
		<?php } ?>
		<li>/ <?php echo htmlspecialchars( $folder->title ); ?></li>
		<img src="/wp-admin/images/loading.gif" id="igf-adm-subfolders-loading" style="display: none;">
	</ul>
	<input type="hidden" id="igf-adm-subfolders-nonce"
		   value="<?php echo wp_create_nonce( 'infogalore-subfolders' ); ?>"/>
	<ul id="igf-adm-subfolders" data-parent-id="<?php echo $folder->ID; ?>">
		<?php foreach ( $folder->subfolders() as $subfolder ) { ?>
			<li data-id="<?php echo $subfolder->ID; ?>">
				<span class="igf-adm-subfolder-icon dashicons dashicons-category"></span>
				<a href="<?php echo admin_url( "post.php?post={$subfolder->ID}&action=edit" ); ?>"><?php echo htmlspecialchars( $subfolder->title ); ?></a>
				<?php _post_states( $subfolder->post ); ?>
			</li>
		<?php } ?>
	</ul>
	<a href="#" id="igf-adm-add-subfolder"
	   class="button button-primary button-large"
	   data-parent-id="<?php echo $folder->ID; ?>">
		<?php _e( 'Add Subfolder', 'infogalore-folders' ); ?>
	</a>
</div>
<div id="igf-adm-add-folder-dialog" style="display: none;">
	<input type="text" id="igf-adm-folder-name">
</div>