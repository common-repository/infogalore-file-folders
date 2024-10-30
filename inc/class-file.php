<?php

class InfoGalore_Folders_File {
	/**
	 * @var WP_Post A post object.
	 */
	public $post;

	/**
	 * @var int Post ID.
	 */
	public $ID = 0;

	/**
	 * @var string File title.
	 */
	public $title = '';

	/**
	 * @var string File description.
	 */
	public $description = '';

	/**
	 * Creates a new file object.
	 *
	 * @param WP_Post $post File post object.
	 */
	public function __construct( $post ) {
		$this->load( $post );
	}

	/**
	 * Returns file instance for a given post.
	 *
	 * @param int|WP_Post $post Post ID or post object.
	 *
	 * @return InfoGalore_Folders_File|null  File object.
	 */
	public static function get( $post ) {
		if ( ! $post instanceof WP_Post ) {
			$post = get_post( $post );
		}

		return new self( $post );
	}

	/**
	 * Loads file data for a given post or returns null.
	 *
	 * @param WP_Post|null $post
	 */
	public function load( $post ) {
		if ( null === $post || 'attachment' !== $post->post_type ) {
			return;
		}
		$this->post        = $post;
		$this->ID          = $post->ID;
		$this->title       = $post->post_title;
		$this->description = $post->post_content;
	}

	/**
	 * Returns file shortcode.
	 *
	 * @return string
	 */
	public function shortcode() {
		return sprintf(
			'[%s id="%s"]',
			InfoGalore_Folders_Plugin::factory()->file_shortcode,
			$this->ID
		);
	}

	/**
	 * Returns filename.
	 *
	 * @return string
	 */
	public function filename() {
		return basename( get_attached_file( $this->ID ) );
	}

	/**
	 * Returns file URL.
	 *
	 * @return false|string
	 */
	public function url() {
		return wp_get_attachment_url( $this->ID );
	}

	/**
	 * Returns file icon URL.
	 *
	 * @return false|string
	 */
	public function icon() {
		return wp_mime_type_icon( $this->ID );
	}

	/**
	 * Returns file size in human readable format.
	 *
	 * @param int $decimals
	 *
	 * @return false|string
	 */
	public function human_filesize( $decimals = 2 ) {
		$bytes = get_post_meta( $this->ID, 'infogalore_folders_file_size', true );
		if ( empty( $bytes ) ) {
			return '';
		}

		return size_format( $bytes, $decimals );
	}

	/**
	 * Returns file downloads count.
	 *
	 * @return int
	 */
	public function downloads() {
		return intval( get_post_meta( $this->ID, 'infogalore_folders_downloads', true ) );
	}

	public function render_html( $atts = array(), $wrap = false ) {
		$title = $atts['title'];

		if ( 'show' == $title ) {
			$title = $this->title;
		} elseif ( 'hide' == $title ) {
			$title = basename( get_attached_file( $this->ID ) );
		}

		$info_order = 'size, date, description';
		$info_keys  = array_map( 'trim', explode( ',', $info_order ) );

		$inline = 'inline' == $atts['layout'];

		if ( ! $inline ) {
			$info_keys = array_diff( $info_keys, array( 'description' ) );
		}

		$info_parts = array();

		foreach ( $info_keys as $k ) {
			$part = null;

			if ( 'show' == $atts[ $k ] ) {
				switch ( $k ) {
					case 'size':
						$part = $this->human_filesize();
						break;
					case 'date':
						$part = mysql2date( get_option( 'date_format' ), $this->post->post_date );
						break;
					case 'description':
						if ( ! empty( $this->description ) ) {
							$part = $this->description;
						}
						break;
				}
			}

			if ( ! empty( $part ) ) {
				$info_parts[] = sprintf(
					'<span class="igf-file-%s">%s</span>',
					$k,
					$part
				);
			}
		}

		$info = '';
		if ( ! empty( $info_parts ) ) {
			$info = ' (' . join( ', ', $info_parts ) . ')';
		}

		if ( $inline ) {
			return sprintf(
				'<span class="igf-file-inline"><a href="%s">%s</a>%s</span>',
				esc_url( $this->url() ),
				esc_html( $title ),
				$info
			);
		} else {
			$description = '';
			if ( 'show' == $atts['description'] && ! empty( $this->description ) ) {
				$description = sprintf(
					'<div class="igf-file-desc">%s</div>',
					esc_html( $this->description )
				);
			}
			$html = sprintf(
				'<a href="%s" class="igf-download" data-id="%s">%s</a>%s%s',
				esc_url( $this->url() ),
				esc_attr( $this->ID ),
				esc_html( $title ),
				$info,
				$description
			);

			if ( $wrap ) {
				return sprintf(
					'<div class="igf-file">%s</div>',
					$html
				);
			} else {
				return $html;
			}
		}
	}
}