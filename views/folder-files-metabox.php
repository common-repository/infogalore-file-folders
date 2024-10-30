<div id="igf-adm-folder-files-metabox">
	<input type="hidden" name="folder_nonce"
	       value="<?php echo wp_create_nonce( 'infogalore-folder' ); ?>">
	<input type="hidden" name="folder_file_ids" id="folder_file_ids"
	       value="<?php echo $folder->file_ids_csv(); ?>">
	<div id="igf-adm-empty-folder-message"<?php if ( ! empty( $files ) ) {
		echo ' style = "display: none"';
	} ?>><?php _e( 'This folder has no files', 'infogalore-folders' ); ?>
	</div>
	<table id="igf-adm-folder-files">
		<tbody>
		<?php
		foreach ( $files as $file ) {
			$this->output_folder_file( $file );
		}
		?>
		</tbody>
	</table>
	<a href="#" id="igf-adm-add-folder-files"
	   class="button button-primary button-large"
	   data-modal-title="<?php _e( 'Add Files to Folder', 'infogalore-folders' ); ?>"
	   data-modal-button-text="<?php _e( 'Add Files', 'infogalore-folders' ); ?>">
		<?php _e( 'Add Files', 'infogalore-folders' ); ?>
	</a>
	<textarea id="igf-adm-folder-file-template"
	          style="display: none"><?php $this->output_folder_file(); ?></textarea>
	<textarea id="igf-adm-file-shortcode-template"
	          style="display: none">[<?php echo $this->file_shortcode; ?> id="0"]</textarea>
</div>