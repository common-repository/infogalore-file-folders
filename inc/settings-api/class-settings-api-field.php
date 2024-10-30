<?php

class InfoGalore_WP_Settings_API_Field {
	protected $section;

	public $id;
	public $type;
	public $label;
	public $desc;
	public $options;
	public $default;
	public $callback;
	public $sanitize;
	public $validate;
	public $error;
	public $invalid_value;

	public function __construct( $section, $args ) {
		$this->section = $section;
		$defaults      = array(
			'id'            => 'undefined',
			'type'          => 'text',
			'label'         => '',
			'desc'          => '',
			'options'       => array(),
			'default'       => '',
			'callback'      => null,
			'sanitize'      => null,
			'validate'      => null,
			'error'         => '',
			'invalid_value' => null
		);
		$args          = wp_parse_args( $args, $defaults );

		$this->id       = $args['id'];
		$this->type     = $args['type'];
		$this->label    = $args['label'];
		$this->desc     = $args['desc'];
		$this->options  = $args['options'];
		$this->default  = $args['default'];
		$this->callback = $args['callback'];
		$this->sanitize = $args['sanitize'];
		$this->validate = $args['validate'];
	}

	public function add_settings_field( $page ) {
		$callback = $this->callback;
		if ( empty( $callback ) ) {
			$callback = array( $this, 'render_' . $this->type );
		}

		add_settings_field(
			$this->id,
			$this->label,
			$callback,
			$page->id,
			$this->section->id,
			$this->prepare_args()
		);
	}

	protected function prepare_args() {
		$args = array();

		$args['id']        = esc_attr( $this->id );
		$args['section']   = esc_attr( $this->section->id );
		$args['label_for'] = sprintf( '%1$s_%2$s', $this->section->id, $this->id );
		$args['type']      = esc_attr( $this->type );
		$args['attr']      = '';
		$args['class']     = $this->error ? 'error' : '';

		return $args;
	}

	protected function value() {
		if ( $this->error ) {
			return $this->invalid_value;
		}

		return $this->section->get_option( $this->id, $this->default );
	}

	protected function description() {
		if ( $this->desc ) {
			return sprintf( '<p class="description">%s</p>', $this->desc );
		}
	}

	protected function error() {
		if ( $this->error ) {
			return sprintf( '<p class="error">%s</p>', $this->error );
		}
	}

	public function render_text( $args ) {
		$html = sprintf(
			'<input type="%1$s" id="%2$s_%3$s" name="%2$s[%3$s]" value="%4$s"%5$s/>',
			$args['type'],
			$args['section'],
			$args['id'],
			esc_attr( $this->value() ),
			$args['attr']
		);

		echo $html . $this->error() . $this->description();
	}

	public function render_textarea( $args ) {
		$html = sprintf(
			'<textarea id="%1$s_%2$s" name="%1$s[%2$s]"%4$s>%3$s</textarea>',
			$args['section'],
			$args['id'],
			esc_attr( $this->value() ),
			$args['attr']
		);

		echo $html . $this->error() . $this->description();
	}

	public function render_checkbox( $args ) {

	}

	public function render_multicheckbox( $args ) {

	}

	public function render_radio( $args ) {
		$html = '';

		foreach ( $this->options as $value => $label ) {
			$html .= sprintf(
				'<label><input type="radio" id="%1$s_%2$s_%s3$s" name="%1$s[%2$s]" value="%3$s"%5$s%6$s>%4$s</label>',
				$args['section'],
				$args['id'],
				esc_attr( $value ),
				esc_html( $label ),
				$args['attr'],
				checked( $value, $this->value(), false )
			);
			$html .= '&nbsp;&nbsp;&nbsp;';
		}

		echo $html . $this->error() . $this->description();
	}

	public function render_select( $args ) {

	}
}