<?php

class InfoGalore_Folders_Folder {
	/**
	 * @var InfoGalore_Folders_Plugin A plugin object.
	 */
	public $plugin;

	/**
	 * @var WP_Post A post object.
	 */
	public $post;

	/**
	 * @var int Post ID.
	 */
	public $ID = 0;

	/**
	 * @var string Post title.
	 */
	public $title = '';

	/**
	 * @var array List of folder files ids.
	 */
	public $file_ids = array();

	/**
	 * @var array|false Array of folder file objects.
	 */
	public $files = false;

	/**
	 * Creates a new folder object.
	 *
	 * @param WP_Post $post Folder post object.
	 * @param InfoGalore_Folders_Plugin $plugin Optional. A plugin object.
	 */
	public function __construct( $post, $plugin = null ) {
		$this->plugin = $plugin ? $plugin : InfoGalore_Folders_Plugin::factory();
		$this->load( $post );
	}

	/**
	 * Returns folder instance for a given post.
	 *
	 * @param int|WP_Post $post Post ID or post object.
	 *
	 * @return InfoGalore_Folders_Folder|null Folder object.
	 */
	public static function get( $post ) {
		if ( ! $post instanceof WP_Post ) {
			$post = get_post( $post );
		}

		return new self( $post );
	}

	/**
	 * Loads folder data for a given post or returns null.
	 *
	 * @param WP_Post|null $post A post object.
	 */
	public function load( $post ) {
		if ( null === $post || $this->plugin->folder_cpt !== $post->post_type ) {
			return;
		}

		$this->post     = $post;
		$this->ID       = $post->ID;
		$this->title    = $post->post_title;
		$ids_meta       = get_post_meta( $this->ID, 'infogalore_folder_file_ids', true );
		$this->file_ids = is_array( $ids_meta ) ? array_filter( $ids_meta ) : array();
	}

	/**
	 * Returns folder shortcode.
	 *
	 * @return string
	 */
	public function shortcode() {
		return sprintf(
			'[%s id="%s"]',
			$this->plugin->folder_shortcode,
			$this->ID
		);
	}

	/**
	 * Returns array of folder file objects.
	 *
	 * @return array|false
	 */
	public function files() {
		if ( false == $this->files ) {
			$this->files = array();
			if ( ! empty( $this->file_ids ) ) {
				$files = get_posts( array(
					'post_type'      => 'attachment',
					'posts_per_page' => - 1,
					'post__in'       => $this->file_ids,
					'orderby'        => 'post__in',
					'order_arg'      => 'DESC',
				) );

				$this->files = array_map(
					array( 'InfoGalore_Folders_File', 'get' ),
					$files
				);
			}
		}

		return $this->files;
	}

	/**
	 * Returns comma separated ids of folder files.
	 *
	 * @return string
	 */
	public function file_ids_csv() {
		return implode( ',', $this->file_ids );
	}

	/**
	 * Returns array of subfolder objects.
	 *
	 * @param bool $any
	 *
	 * @return array
	 */
	public function subfolders( $any = true ) {
		return array_map(
			array( 'InfoGalore_Folders_Folder', 'get' ),
			get_posts( array(
				'post_type'      => $this->plugin->folder_cpt,
				'post_status'    => ( $any ? 'any' : 'publish' ),
				'posts_per_page' => - 1,
				'post_parent'    => $this->ID,
				'orderby'        => 'menu_order',
				'order'          => 'ASC'
			) )
		);
	}

	/**
	 * Returns array of ancestor folder objects.
	 *
	 * @return array
	 */
	public function ancestors() {
		return array_map(
			array( 'InfoGalore_Folders_Folder', 'get' ),
			get_post_ancestors( $this->ID )
		);
	}

	public function render_html( $atts = array() ) {
		$depth  = $atts['depth'];
		$inline = 'inline' == $atts['layout'];

		if ( 'show' == $atts['title'] ) {
			$title = $this->title;
		} elseif ( 'hide' == $atts['title'] ) {
			$title = '';
		} else {
			$title = empty( $atts['title'] ) ? $this->title : $atts['title'];
		}

		// fix for subfolder and file titles
		$atts['title'] = 'show';

		if ( $inline ) {
			return $this->render_inline_html( $depth, true, $title, $atts );
		} else {
			return $this->render_block_html( $depth, true, $title, $atts );
		}
	}

	private function render_block_html( $depth, $top_level, $title, $atts ) {
		$files   = array();
		$folders = array();

		$file_atts = wp_parse_args( array( 'title' => $atts['file_titles'] ), $atts );

		foreach ( $this->files() as $file ) {
			$files[] = $file->render_html( $file_atts );
		}

		if ( $depth > 0 ) {
			foreach ( $this->subfolders( false ) as $folder ) {
				$folders[] = $folder->render_block_html( $depth - 1, false, $folder->title, $atts );
			}
		}
		$files   = array_filter( $files );
		$folders = array_filter( $folders );

		if ( empty( $files ) && empty( $folders ) ) {
			return '';
		}

		$type = $top_level ? 'folder' : 'subfolder';

		$title_html = '';
		if ( ! empty( $title ) ) {
			$title_html = sprintf(
				'<header class="igf-%s-header"><div class="igf-%s-title">%s</div></header>',
				$type,
				$type,
				$title
			);
		}

		$files_html = '';
		if ( ! empty( $files ) ) {
			$files_html .= '<ul class="igf-' . $type . '-files">';
			foreach ( $files as $file ) {
				$files_html .= '<li class="igf-' . $type . '-file">' . $file . '</li>';
			}
			$files_html .= '</ul>';
		}

		$folders_html = join( '', $folders );

		return sprintf(
			'<section class="igf-%s">%s%s%s</section>',
			$type,
			$title_html,
			$files_html,
			$folders_html
		);
	}

	private function render_inline_html( $depth, $top_level, $title, $atts ) {
		$items = array();
		foreach ( $this->files() as $file ) {
			$items[] = $file->render_html( wp_parse_args( array( 'title' => $atts['file_titles'] ), $atts ) );
		}

		if ( $depth > 0 ) {
			foreach ( $this->subfolders( false ) as $subfolder ) {
				$items[] = $subfolder->render_inline_html( $depth - 1, false, $subfolder->title, $atts );
			}
		}

		$html = join( '; ', array_filter( $items ) );

		$type = $top_level ? 'folder' : 'subfolder';

		$title_html = '';
		if ( ! empty( $title ) ) {
			$title_html = sprintf(
				'<span class="igf-%s-title igf-%s-title-inline">%s:</span> ',
				$type,
				$type,
				$title
			);
		}

		if ( empty( $html ) ) {
			return '';
		} else {
			return sprintf(
				'<span class="igf-%s-inline">%s%s</span>',
				$type,
				$title_html,
				$html
			);
		}
	}
}