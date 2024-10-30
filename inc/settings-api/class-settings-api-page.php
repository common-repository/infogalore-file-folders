<?php

class InfoGalore_WP_Settings_API_Page {
	protected $settings;

	public $sections = array();
	public $id;
	public $title;
	public $button_text;

	public function __construct( $settings, $args ) {
		$this->settings = $settings;
		$defaults       = array(
			'id'          => 'undefined',
			'title'       => 'Undefined Tab',
			'button_text' => ''
		);
		$args           = wp_parse_args( $args, $defaults );

		$this->id          = $args['id'];
		$this->title       = $args['title'];
		$this->button_text = $args['button_text'];
	}

	public function add_section( $args ) {
		$section          = new InfoGalore_WP_Settings_API_Section( $this, $args );
		$this->sections[] = $section;
		$this->settings->added_section( $section );

		return $section;
	}
}