<?php

require_once( INFOGALORE_FOLDERS_PLUGIN_DIR . 'inc/settings-api/load.php' );

/**
 * Plugin settings class.
 */
class InfoGalore_Folders_Settings {
	/**
	 * @var InfoGalore_WP_Settings_API A settings API object.
	 */
	protected $api;

	/**
	 * @var string Options page slug.
	 */
	public $slug = 'infogalore-folders';

	/**
	 * Creates a new settings object.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initializes settings object.
	 */
	public function init() {
		$this->api = $settings = new InfoGalore_WP_Settings_API(
			__( 'InfoGalore Folders Settings', 'infogalore-folders' ),
			__( 'InfoGalore Folders', 'infogalore-folders' ),
			'manage_options',
			$this->slug
		);

		$page = $settings->add_page( array(
			'id'    => 'infogalore_folders_general',
			'title' => __( 'General', 'infogalore-folders' )
		) );

		$section = $page->add_section( array(
			'id'    => 'infogalore_folders',
			'title' => __( 'General Options', 'infogalore-folders' )
		) );

		$section->add_field( array(
			'id'       => 'shortcode_prefix',
			'type'     => 'text',
			'label'    => __( 'Shortcode Prefix', 'infogalore-folders' ),
			'desc'     => __( 'Letters, digits, - or _ symbols', 'infogalore-folders' ),
			'default'  => 'ig-',
			'sanitize' => array( $this, 'normalize_value' ),
			'validate' => array( $this, 'validate_shortcode_prefix' )
		) );

		$page = $settings->add_page( array(
			'id'    => 'infogalore_folders_folder_shortcode',
			'title' => __( 'Folder Shortcode', 'infogalore-folders' )
		) );

		$section = $page->add_section( array(
			'id'    => 'infogalore_folders_folder_shortcode',
			'title' => __( 'Folder Shortcode Default Options', 'infogalore-folders' )
		) );

		$section->add_field( array(
			'id'      => 'layout',
			'type'    => 'radio',
			'label'   => _x( 'Layout', 'shortcode option', 'infogalore-folders' ),
			'default' => 'block',
			'options' => array(
				'block'  => _x( 'block', 'settings value', 'infogalore-folders' ),
				'inline' => _x( 'inline', 'settings value', 'infogalore-folders' )
			)
		) );

		$section->add_field( array(
			'id'      => 'title',
			'type'    => 'radio',
			'label'   => _x( 'Title', 'shortcode option', 'infogalore-folders' ),
			'default' => 'show',
			'options' => array(
				'show' => _x( 'show', 'settings value', 'infogalore-folders' ),
				'hide' => _x( 'hide', 'settings value', 'infogalore-folders' )
			)
		) );

		$section->add_field( array(
			'id'      => 'file_titles',
			'type'    => 'radio',
			'label'   => _x( 'File Titles', 'shortcode option', 'infogalore-folders' ),
			'default' => 'show',
			'options' => array(
				'show' => _x( 'show', 'settings value', 'infogalore-folders' ),
				'hide' => _x( 'hide', 'settings value', 'infogalore-folders' )
			)
		) );

		$section->add_field( array(
			'id'      => 'size',
			'type'    => 'radio',
			'label'   => _x( 'Size', 'shortcode option', 'infogalore-folders' ),
			'default' => 'show',
			'options' => array(
				'show' => _x( 'show', 'settings value', 'infogalore-folders' ),
				'hide' => _x( 'hide', 'settings value', 'infogalore-folders' )
			)
		) );

		$section->add_field( array(
			'id'      => 'date',
			'type'    => 'radio',
			'label'   => _x( 'Date', 'shortcode option', 'infogalore-folders' ),
			'default' => 'hide',
			'options' => array(
				'show' => _x( 'show', 'settings value', 'infogalore-folders' ),
				'hide' => _x( 'hide', 'settings value', 'infogalore-folders' )
			)
		) );

		$section->add_field( array(
			'id'      => 'description',
			'type'    => 'radio',
			'label'   => _x( 'Description', 'shortcode option', 'infogalore-folders' ),
			'default' => 'hide',
			'options' => array(
				'show' => _x( 'show', 'settings value', 'infogalore-folders' ),
				'hide' => _x( 'hide', 'settings value', 'infogalore-folders' )
			)
		) );

		$page = $settings->add_page( array(
			'id'    => 'infogalore_folders_file_shortcode',
			'title' => __( 'File Shortcode', 'infogalore-folders' )
		) );

		$section = $page->add_section( array(
			'id'    => 'infogalore_folders_file_shortcode',
			'title' => __( 'File Shortcode Default Options', 'infogalore-folders' )
		) );

		$section->add_field( array(
			'id'      => 'layout',
			'type'    => 'radio',
			'label'   => _x( 'Layout', 'shortcode option', 'infogalore-folders' ),
			'default' => 'inline',
			'options' => array(
				'block'  => _x( 'block', 'settings value', 'infogalore-folders' ),
				'inline' => _x( 'inline', 'settings value', 'infogalore-folders' )
			)
		) );

		$section->add_field( array(
			'id'      => 'title',
			'type'    => 'radio',
			'label'   => _x( 'Title', 'shortcode option', 'infogalore-folders' ),
			'default' => 'show',
			'options' => array(
				'show' => _x( 'show', 'settings value', 'infogalore-folders' ),
				'hide' => _x( 'hide', 'settings value', 'infogalore-folders' )
			)
		) );

		$section->add_field( array(
			'id'      => 'size',
			'type'    => 'radio',
			'label'   => _x( 'Size', 'shortcode option', 'infogalore-folders' ),
			'default' => 'show',
			'options' => array(
				'show' => _x( 'show', 'settings value', 'infogalore-folders' ),
				'hide' => _x( 'hide', 'settings value', 'infogalore-folders' )
			)
		) );

		$section->add_field( array(
			'id'      => 'date',
			'type'    => 'radio',
			'label'   => _x( 'Date', 'shortcode option', 'infogalore-folders' ),
			'default' => 'hide',
			'options' => array(
				'show' => _x( 'show', 'settings value', 'infogalore-folders' ),
				'hide' => _x( 'hide', 'settings value', 'infogalore-folders' )
			)
		) );

		$section->add_field( array(
			'id'      => 'description',
			'type'    => 'radio',
			'label'   => _x( 'Description', 'shortcode option', 'infogalore-folders' ),
			'default' => 'hide',
			'options' => array(
				'show' => _x( 'show', 'settings value', 'infogalore-folders' ),
				'hide' => _x( 'hide', 'settings value', 'infogalore-folders' )
			)
		) );

		$settings->init();
	}

	/**
	 * Adds settings link as submenu item.
	 *
	 * @param string $parent_slug Parent menu slug.
	 * @param string $menu_title Menu item title.
	 */
	public function add_admin_submenu( $parent_slug, $menu_title ) {
		$this->api->add_admin_submenu( $parent_slug, $menu_title );
	}

	/**
	 * Trims string value.
	 *
	 * @param string $value User input value.
	 *
	 * @return string
	 */
	public function normalize_value( $value ) {
		return trim( $value );
	}

	/**
	 * Validates shortcode prefix value.
	 *
	 * @param string $value User input string to be validated.
	 *
	 * @return bool|string False. Error message if input is not valid.
	 */
	public function validate_shortcode_prefix( $value ) {
		if ( '' === $value ) {
			return false;
		}

		if ( preg_match( '/^[a-zA-Z]+[a-zA-Z0-9_-]?$/', $value ) ) {
			return false;
		}

		return __( 'must contain only allowed symbols', 'infogalore-folders' );
	}

	/**
	 * Returns option value.
	 *
	 * @param string $section Settings section name.
	 * @param string $name Settings option name.
	 * @param mixed $default Optional. Default value.
	 *
	 * @return mixed
	 */
	public function get_option( $section, $name, $default = null ) {
		return $this->api->sections[ $section ]->get_option( $name, $default );
	}

	/**
	 * Returns folder shortcode default options.
	 *
	 * @return array
	 */
	public function get_folder_shortcode_atts() {
		$options = $this->api->sections['infogalore_folders_folder_shortcode']->get_options();

		$options['id']    = 0;
		$options['depth'] = 0;

		return $options;
	}

	/**
	 * Returns file shortcode default options.
	 *
	 * @return array
	 */
	public function get_file_shortcode_atts() {
		$options = $this->api->sections['infogalore_folders_file_shortcode']->get_options();

		$options['id'] = 0;

		return $options;
	}
}