<?php

if ( ! class_exists( 'InfoGalore_WP_Settings_API' ) ) {
	class InfoGalore_WP_Settings_API {
		protected $pages = array();
		public $current_page;
		public $sections = array();

		protected $page_title;
		protected $menu_title;
		protected $capability;
		protected $slug;

		public function __construct( $page_title, $menu_title, $capability, $slug ) {
			$this->page_title = $page_title;
			$this->menu_title = $menu_title;
			$this->capability = $capability;
			$this->slug       = $slug;
		}

		public function init() {
			add_action( 'admin_init', array( $this, 'init_settings' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		}

		public function init_settings( $hook ) {
			$page = $this->get_current_page();

			foreach ( $page->sections as $section ) {
				add_settings_section( $section->id, $section->title, $section->callback, $page->id );
				$section->add_settings_fields();
				register_setting( $page->id, $section->id, array( $section, 'validate_settings' ) );
			}
		}

		public function admin_menu() {
			add_options_page(
				$this->page_title,
				$this->menu_title,
				$this->capability,
				$this->slug,
				array( $this, 'render_settings_page' )
			);
		}

		public function add_admin_submenu( $parent_slug, $menu_title ) {
			add_submenu_page(
				$parent_slug,
				$this->page_title,
				$menu_title,
				$this->capability,
				$this->slug, array( $this, 'render_settings_page' )
			);
		}

		public function render_settings_page() {
			if ( ! current_user_can( $this->capability ) ) {
				return;
			}

			$current_page = $this->get_current_page();
			$first_page   = $this->pages[0];

			$options_url = 'options.php';
			if ( $first_page->id !== $current_page->id ) {
				$options_url = add_query_arg( 'tab', $current_page->id, $options_url );
			}

			?>
			<div class="wrap">
				<h1><?= esc_html( get_admin_page_title() ); ?></h1>
				<h2 class="nav-tab-wrapper">
					<?php
					foreach ( $this->pages as $page ) {
						$active = '';
						if ( $current_page->id === $page->id ) {
							$active = ' nav-tab-active';
						}
						$tab_url = remove_query_arg( array( 'tab', 'settings-updated' ) );
						if ( $first_page->id !== $page->id ) {
							$tab_url = add_query_arg( 'tab', $page->id, $tab_url );
						}

						echo sprintf(
							'<a href="%3$s" class="nav-tab%4$s" id="%1$s-tab">%2$s</a>',
							$page->id,
							$page->title,
							$tab_url,
							$active
						);
					}
					?>
			</div>

			<form action="<?php echo $options_url; ?>" method="post">
				<?php
				settings_fields( $current_page->id );
				do_settings_sections( $current_page->id );
				submit_button( $current_page->button_text );
				?>
			</form>
			</div>
			<?php
		}

		public function add_page( $args ) {
			$page          = new InfoGalore_WP_Settings_API_Page( $this, $args );
			$this->pages[] = $page;

			return $page;
		}

		public function added_section( $section ) {
			$this->sections[ $section->id ] = $section;
		}

		public function get_current_page() {
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				foreach ( $this->pages as $page ) {
					if ( $_GET['tab'] == $page->id ) {
						$current_page = $page;
						break;
					}
				}
			}

			if ( empty( $current_page ) ) {
				$current_page = $this->pages[0];
			}

			return $current_page;
		}

		public function enqueue_admin_assets( $hook ) {
			if ( 'settings_page_' . $this->slug !== $hook ) {
				return;
			}

			$custom_css = "
                tr.error>th>label{ color: red; }
                tr.error p.error { color: red; }
                ";

			wp_add_inline_style( 'forms', $custom_css );
		}
	}
}