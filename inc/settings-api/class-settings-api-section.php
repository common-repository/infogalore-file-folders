<?php

class InfoGalore_WP_Settings_API_Section {
	protected $page;
	protected $options = false;
	protected $errors = array();

	public $fields = array();
	public $defaults = array();
	public $id;
	public $title;
	public $callback;

	public function __construct( $page, $args ) {
		$this->page = $page;

		$defaults = array(
			'id'       => 'undefined',
			'title'    => 'Undefined Section',
			'callback' => array( $this, 'render_section' )
		);
		$args     = wp_parse_args( $args, $defaults );

		$this->id       = $args['id'];
		$this->title    = $args['title'];
		$this->callback = $args['callback'];

		$errors = get_transient( 'settings_errors_' . $this->id );
		if ( false !== $errors ) {
			$this->errors = $errors;
			delete_transient( 'settings_errors_' . $this->id );
		}
	}

	public function add_field( $args ) {
		$field = new InfoGalore_WP_Settings_API_Field( $this, $args );

		$this->defaults[ $field->id ] = $field->default;

		if ( isset( $this->errors[ $field->id ] ) ) {
			$field->invalid_value = $this->errors[ $field->id ]['value'];
			$field->error         = $this->errors[ $field->id ]['message'];
		}

		$this->fields[] = $field;

		return $field;
	}

	public function add_settings_fields() {
		foreach ( $this->fields as $field ) {
			$field->add_settings_field( $this->page );
		}
	}

	public function render_section() {

	}

	public function get_options() {
		if ( false === $this->options ) {
			$this->options = wp_parse_args( get_option( $this->id ), $this->defaults );
		}

		return $this->options;
	}

	public function get_option( $name, $default ) {
		$options = $this->get_options();

		return isset( $options[ $name ] ) ? $options[ $name ] : $default;
	}


	public function validate_settings( $input ) {
		$errors = array();

		foreach ( $this->fields as $field ) {
			if ( ! isset( $input[ $field->id ] ) ) {
				continue;
			}
			if ( $field->sanitize ) {
				$input[ $field->id ] = call_user_func( $field->sanitize, $input[ $field->id ] );
			}

			if ( $field->validate ) {
				$error = call_user_func( $field->validate, $input[ $field->id ] );

				if ( false === $error ) {
					continue;
				}

				$errors[ $field->id ] = array(
					'value'   => $input[ $field->id ],
					'message' => $error
				);
				add_settings_error( $field->id, esc_attr( $field->id ), $field->label . ': ' . $error );

				$input[ $field->id ] = $this->get_option( $field->id, '' );
			}
		}

		if ( $errors ) {
			set_transient( 'settings_errors_' . $this->id, $errors, 10 );
		}

		return $input;
	}
}