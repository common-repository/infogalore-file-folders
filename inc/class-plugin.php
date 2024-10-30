<?php

/**
 * Main plugin class.
 */
class InfoGalore_Folders_Plugin
{
    /**
     * @var InfoGalore_Folders_Settings A settings object.
     */
    public $settings;

    /**
     * @var string Folder post type name.
     */
    public $folder_cpt;

    /**
     * @var string File post type name.
     */
    public $file_cpt;

    /**
     * @var string Folder shortcode name.
     */
    public $folder_shortcode;

    /**
     * @var string File shortcode name.
     */
    public $file_shortcode;

    /**
     * Creates a new plugin object.
     *
     * @param InfoGalore_Folders_Settings $settings
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Returns static plugin object instance.
     *
     * @param InfoGalore_Folders_Settings|null $settings Optional.
     *
     * @return InfoGalore_Folders_Plugin
     */
    public static function factory($settings = null)
    {
        static $instance = false;
        if ( ! $instance) {
            if ( ! $settings) {
                $settings = new InfoGalore_Folders_Settings();
            }
            $instance = new self($settings);
        }

        return $instance;
    }

    /**
     * Adds plugin intialization actions.
     */
    public function run()
    {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Loads plugin textdomain.
     */
    public function load_textdomain()
    {
        $lang_dir = dirname(plugin_basename(INFOGALORE_FOLDERS_PLUGIN_FILE)) . '/languages';
        load_plugin_textdomain('infogalore-folders', false, $lang_dir);
    }

    /**
     * Initializes plugin.
     */
    public function init()
    {

        $this->folder_cpt = apply_filters('infogalore_folders_folder_post_type', 'folder');
        $this->file_cpt   = apply_filters('infogalore_folders_file_post_type', 'file');

        $prefix = $this->settings->get_option('infogalore_folders', 'shortcode_prefix', '');

        $this->folder_shortcode = $prefix . 'folder';
        $this->file_shortcode   = $prefix . 'file';

        add_action('admin_menu', array($this, 'admin_menu'));
        add_filter('plugin_action_links_' . plugin_basename(INFOGALORE_FOLDERS_PLUGIN_FILE), array(
            $this,
            'add_plugin_links'
        ));

        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_shortcodes'));

        add_action('add_attachment', array($this, 'save_attachment_size'));
        add_action('save_post', array($this, 'save_folder'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_media', array($this, 'enqueue_media_assets'));

        add_action('restrict_manage_posts', array($this, 'restrict_manage_folders'));
        add_filter('parse_query', array($this, 'parse_query_filter'));

        add_action('wp_ajax_infogalore_folders_create_folder', array($this, 'ajax_create_folder'));
        add_action('wp_ajax_infogalore_folders_sort_folders', array($this, 'ajax_sort_folders'));
        add_action('wp_ajax_infogalore_folders_folder_lookup', array($this, 'ajax_folder_lookup'));
        add_action('wp_ajax_infogalore_folders_downloads_counter', array($this, 'ajax_downloads_counter'));
        add_action('wp_ajax_nopriv_infogalore_folders_downloads_counter', array($this, 'ajax_downloads_counter'));
    }

    /**
     * Adds settings link to folders menu.
     */
    public function admin_menu()
    {
        $this->settings->add_admin_submenu(
            'edit.php?post_type=' . $this->folder_cpt,
            __('Settings', 'infogalore-folders')
        );
    }

    /**
     * Adds setttings link to plugins page.
     *
     * @param array $links
     *
     * @return array
     */
    public function add_plugin_links($links)
    {
        $links[] = sprintf(
            '<a href="%1$s">%2$s</a>',
            get_admin_url(null, 'options-general.php?page=' . $this->settings->slug),
            esc_html__('Settings', 'infogalore-folders')
        );

        return $links;
    }

    /**
     * Registers folder post type.
     */
    public function register_post_types()
    {
        $labels = array(
            'name'               => _x('Folders', 'post type general name', 'infogalore-folders'),
            'singular_name'      => _x('Folder', 'post type singular name', 'infogalore-folders'),
            'menu_name'          => _x('Folders', 'admin menu', 'infogalore-folders'),
            'name_admin_bar'     => _x('Folder', 'add new on admin bar', 'infogalore-folders'),
            'add_new'            => _x('Add New', 'folder', 'infogalore-folders'),
            'add_new_item'       => __('Add New Folder', 'infogalore-folders'),
            'new_item'           => __('New Folder', 'infogalore-folders'),
            'edit_item'          => __('Edit Folder', 'infogalore-folders'),
            'view_item'          => __('View Folder', 'infogalore-folders'),
            'all_items'          => __('All Folders', 'infogalore-folders'),
            'search_items'       => __('Search Folders', 'infogalore-folders'),
            'parent_item_colon'  => __('Parent Folders:', 'infogalore-folders'),
            'not_found'          => __('No folders found.', 'infogalore-folders'),
            'not_found_in_trash' => __('No folders found in Trash.', 'infogalore-folders')
        );

        $args = array(
            'labels'               => $labels,
            'description'          => __('Hierarchical file folders.', 'infogalore-folders'),
            'public'               => false,
            'show_ui'              => true,
            'query_var'            => false,
            'rewrite'              => array('slug' => 'folder'),
            'capability_type'      => 'page',
            'has_archive'          => false,
            'hierarchical'         => true,
            'menu_position'        => 12,
            'menu_icon'            => 'dashicons-portfolio',
            'supports'             => array('title', 'page-attributes'),
            'register_meta_box_cb' => array($this, 'register_folder_metaboxes')
        );

        register_post_type(
            $this->folder_cpt,
            apply_filters('infogalore_folders_folder_cpt_args', $args)
        );
    }

    /**
     * Registers folder post type metaboxes.
     */
    public function register_folder_metaboxes()
    {
        add_meta_box(
            'infogalore_folders_folder_files',
            __('Files', 'infogalore-folders'),
            array($this, 'output_folder_files_metabox'),
            $this->folder_cpt,
            'normal',
            'high'
        );

        add_meta_box(
            'infogalore_folders_folder_subfolders',
            __('Subfolders', 'infogalore-folders'),
            array($this, 'output_folder_subfolders_metabox'),
            $this->folder_cpt,
            'normal',
            'default'
        );

        add_meta_box(
            'infogalore_folders_folder_shortcode',
            __('Folder Shortcode', 'infogalore-folders'),
            array($this, 'output_folder_shortcode_metabox'),
            $this->folder_cpt,
            'side',
            'default'
        );

        remove_meta_box(
            'pageparentdiv',
            $this->folder_cpt,
            'side'
        );

        add_meta_box(
            'pageparentdiv',
            __('Folder Attributes', 'infogalore-folders'),
            'page_attributes_meta_box',
            $this->folder_cpt,
            'side',
            'default'
        );
    }

    /**
     * Outputs HTML for folder files metabox.
     *
     * @param WP_Post $post
     */
    public function output_folder_files_metabox($post)
    {
        wp_enqueue_media();

        $folder = $this->folder($post);
        $files  = $folder->files();

        require $this->template('folder-files-metabox.php');
    }

    /**
     * Outputs HTML for folder subfolders metabox.
     *
     * @param WP_Post $post
     */
    public function output_folder_subfolders_metabox($post)
    {
        $folder = $this->folder($post);
        $cpt    = $this->folder_cpt;

        require $this->template('folder-subfolders-metabox.php');
    }

    /**
     * Outputs HTML for folder shortcode metabox.
     *
     * @param WP_Post $post
     */
    public function output_folder_shortcode_metabox($post)
    {
        $folder    = $this->folder($post);
        $shortcode = $folder->shortcode();

        require $this->template('folder-shortcode-metabox.php');
    }

    /**
     * Outputs HTML for folder files metabox file item.
     *
     * @param InfoGalore_Folders_File $file
     */
    public function output_folder_file($file)
    {
        if ( ! $file) {
            // template
            $file_id   = '';
            $filename  = '';
            $title     = '';
            $url       = '';
            $size      = '';
            $icon      = wp_mime_type_icon('application/octet-stream');
            $shortcode = '';
            $downloads = '?';
        } else {
            // file
            $file_id   = $file->ID;
            $filename  = $file->filename();
            $title     = $file->title;
            $url       = $file->url();
            $size      = $file->human_filesize();
            $icon      = $file->icon();
            $shortcode = $file->shortcode();
            $downloads = $file->downloads();
        }

        require $this->template('folder-files-file.php');
    }

    /**
     * Registers plugin shortcodes.
     */
    public function register_shortcodes()
    {
        add_shortcode(
            $this->folder_shortcode,
            array($this, 'render_folder_shortcode')
        );

        add_shortcode(
            $this->file_shortcode,
            array($this, 'render_file_shortcode')
        );
    }

    public function render_folder_shortcode($atts)
    {
        $atts = shortcode_atts(
            $this->settings->get_folder_shortcode_atts(),
            $atts,
            $this->folder_shortcode
        );

        if ($atts['id'] > 0) {
            $folder = $this->folder($atts['id']);
            if ($folder && 'publish' == $folder->post->post_status) {
                return $folder->render_html($atts);
            }
        }
    }

    public function render_file_shortcode($atts)
    {
        $atts = shortcode_atts(
            $this->settings->get_file_shortcode_atts(),
            $atts,
            $this->file_shortcode
        );

        if ($atts['id'] > 0) {
            $file = $this->file($atts['id']);
            if ($file) {
                return $file->render_html($atts, true);
            }
        }
    }

    /**
     * Saves file size.
     *
     * @param WP_Post $post_id
     */
    public function save_attachment_size($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $file = get_attached_file($post_id);
        if ($file) {
            update_post_meta($post_id, 'infogalore_folders_file_size', filesize($file));
        }
    }

    /**
     * Saves folder data.
     *
     * @param int $post_id
     */
    public function save_folder($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // verify nonce
        if (array_key_exists('folder_nonce', $_POST) &&
            wp_verify_nonce($_POST['folder_nonce'], 'infogalore-folder')
        ) {
            $attachments = explode(',', $_POST['folder_file_ids']);
            update_post_meta($post_id, 'infogalore_folder_file_ids', $attachments);
        }
    }

    /**
     * Adds folder list filters.
     */
    public function restrict_manage_folders()
    {
        $type = 'post';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }

        if ($this->folder_cpt == $type) {
            $name    = '';
            $checked = false;

            if (isset($_GET['f_roots'])) {
                $checked = $_GET['f_roots'];
            } elseif (isset($_GET['f_parent'])) {
                $parent_id = $this->parse_string_with_id($_GET['f_parent']);
                if ($parent_id > 0) {
                    $parent = get_post($parent_id);
                    if ($this->folder_cpt == $parent->post_type) {
                        $name = $parent->post_title . ' [' . $parent->ID . ']';
                    }
                }
            }

            printf(
                '<label class="list-table-filter-cb"><input type="checkbox" name="f_roots"%s>%s</label>',
                $checked ? ' checked="checked"' : '',
                __('only roots', 'infogalore-folders')
            );

            printf(
                ' <input type="text" name="f_parent" id="igf-adm-filter-folder-parent"%s placeholder="%s">',
                $name ? ' value="' . $name . '"' : '',
                esc_attr__('Parent folder', 'infogalore-folders')
            );
        }
    }

    /**
     * Filters folders query.
     *
     * @param WP_Query $query
     */
    public function parse_query_filter($query)
    {
        global $pagenow;

        if (is_admin() && 'edit.php' == $pagenow) {
            $type = '';

            if (isset($_GET['post_type'])) {
                $type = $_GET['post_type'];
            }

            if ($this->folder_cpt != $type) {
                return;
            }

            if (isset($_GET['f_roots']) && $_GET['f_roots'] != '') {
                $query->query_vars['post_parent'] = 0;
            } elseif (isset($_GET['f_parent']) && ! empty($_GET['f_parent'])) {
                $query->query_vars['post_parent'] = $this->parse_string_with_id($_GET['f_parent']);
            }
        }
    }

    /**
     * Creates new folder and returns folder edit page URL.
     */
    public function ajax_create_folder()
    {
        if (check_admin_referer('infogalore-subfolders', 'nonce')) {
            $args = array(
                'post_type'  => $this->folder_cpt,
                'post_title' => sanitize_text_field($_POST['title'])
            );

            $folder = $this->folder($_POST['parent_id']);
            if ($folder) {
                $args['post_parent'] = $folder->ID;
            }

            $post_id = wp_insert_post($args);

            echo admin_url("post.php?post=$post_id&action=edit");
        }
        wp_die();
    }

    /**
     * Updates subfolders order.
     */
    public function ajax_sort_folders()
    {
        if (check_admin_referer('infogalore-subfolders', 'nonce')) {
            $parent_id = $_POST['parent_id'];

            $folder = null;
            $post   = get_post($parent_id);
            if ($post) {
                $folder = $this->folder($post);
            }
            if ($folder) {
                $sorted = wp_parse_id_list($_POST['sorted']);

                if ( ! empty($sorted)) {
                    $list = join(',', $sorted);

                    global $wpdb;

                    $wpdb->query('SELECT @i:=0');
                    $wpdb->query(
                        "UPDATE wp_posts SET menu_order = ( @i:= @i+1 )
                            WHERE post_parent=$folder->ID AND ID IN ( $list )
                            ORDER BY FIELD( ID, $list );"
                    );
                }
                echo 'ok';
            }
        }
        wp_die();
    }

    /**
     * Returns matching folder names.
     */
    public function ajax_folder_lookup()
    {
        global $wpdb;

        $query = sprintf(
            "SELECT ID,post_title FROM %s 
             WHERE post_type='%s' AND post_title LIKE '%s%%'
             ORDER BY post_title ASC",
            $wpdb->posts,
            $this->folder_cpt,
            $wpdb->esc_like($_REQUEST['q'])
        );

        foreach ($wpdb->get_results($query) as $row) {
            echo sprintf(
                "%s [%s]\n",
                $row->post_title,
                $row->ID
            );
        }
        wp_die();
    }

    /**
     * Increases file downloads counter.
     */
    public function ajax_downloads_counter()
    {
        check_ajax_referer('infogalore-folders', 'security');

        $post = get_post(intval($_POST['fileid']));
        if ($post && 'attachment' == $post->post_type) {
            $count = get_post_meta($post->ID, 'infogalore_folders_downloads', true);
            if (empty($count)) {
                $count = 0;
            }
            $count += 1;
            update_post_meta($post->ID, 'infogalore_folders_downloads', $count);
        }

        wp_die();
    }

    /**
     * Returns folder object for a given post.
     *
     * @param WP_Post $post
     *
     * @return InfoGalore_Folders_Folder|null
     */
    public function folder($post)
    {
        return InfoGalore_Folders_Folder::get($post);
    }

    /**
     * Returns file object for a given post.
     *
     * @param WP_Post $post
     *
     * @return InfoGalore_Folders_File|null
     */
    public function file($post)
    {
        return InfoGalore_Folders_File::get($post);
    }

    /**
     * Returns tempate filename for a given view.
     *
     * @param string $view
     *
     * @return string
     */
    public function template($view)
    {
        return INFOGALORE_FOLDERS_PLUGIN_DIR . 'views/' . $view;
    }

    /**
     * Returns asset URL.
     *
     * @param sting $asset Asset relative path.
     *
     * @return string
     */
    public function assets_url($asset)
    {
        return esc_url(plugins_url($asset, INFOGALORE_FOLDERS_PLUGIN_FILE));
    }

    /**
     * Adds frontend asset files.
     */
    public function enqueue_assets()
    {
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

        wp_enqueue_style(
            'infogalore-folders',
            $this->assets_url('css/frontend' . $suffix . '.css'),
            array(),
            INFOGALORE_FOLDERS_VERSION
        );

        wp_register_script(
            'infogalore-folders',
            $this->assets_url('js/frontend' . $suffix . '.js'),
            array('jquery'),
            INFOGALORE_FOLDERS_VERSION
        );

        wp_localize_script(
            'infogalore-folders',
            'INFOGALORE_FOLDERS',
            array(
                'ajaxurl'  => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('infogalore-folders')
            ));

        wp_enqueue_script('infogalore-folders');
    }

    /**
     * Adds admin asset files.
     *
     * @param string $hook
     */
    public function enqueue_admin_assets($hook)
    {
        $type = null;

        if ('post-new.php' == $hook || 'edit.php' == $hook) {
            if (isset($_GET['post_type'])) {
                $type = $_GET['post_type'];
            }
        } elseif ('post.php' == $hook) {
            $type = get_post()->post_type;
        }

        if ($this->folder_cpt == $type) {
            $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

            wp_register_script(
                'infogalore-folders-admin',
                $this->assets_url('js/admin' . $suffix . '.js'),
                array('jquery', 'jquery-ui-dialog', 'jquery-ui-sortable', 'suggest'),
                INFOGALORE_FOLDERS_VERSION
            );

            wp_localize_script(
                'infogalore-folders-admin',
                'INFOGALORE_FOLDERS_ADMIN',
                array(
                    'prompt_label'           => __('Folder Name:', 'infogalore-folders'),
                    'ok_label'               => __('Create', 'infogalore-folders'),
                    'cancel_label'           => __('Cancel', 'infogalore-folders'),
                    'file_shortcode_tooltip' => __('Click to copy to clipboard', 'infogalore-folders'),
                    'file_shortcode_copied'  => __('COPIED', 'infogalore-folders')
                )
            );

            wp_enqueue_script('infogalore-folders-admin');

            wp_enqueue_style(
                'infogalore-folders-admin',
                $this->assets_url('css/admin' . $suffix . '.css'),
                array('wp-jquery-ui-dialog'),
                INFOGALORE_FOLDERS_VERSION
            );
        }
    }

    /**
     * Adds media selector asset files.
     */
    public function enqueue_media_assets()
    {
        if ( ! function_exists('get_current_screen')) {
            // media used in frontend
            return;
        }

        $screen = get_current_screen();
        if ($screen instanceof WP_Screen && $this->folder_cpt == $screen->id) {
            $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

            wp_register_script(
                'infogalore-folders-media',
                $this->assets_url('js/media' . $suffix . '.js'),
                array('jquery', 'media-views'),
                INFOGALORE_FOLDERS_VERSION
            );

            wp_localize_script(
                'infogalore-folders-media',
                'INFOGALORE_FOLDERS_MEDIA',
                array(
                    'confirmation_text' => __("This file will be removed from folder, but not deleted from media library.\n'Cancel' to stop, 'OK' to remove.",
                        'infogalore-folders'),
                )
            );

            wp_enqueue_script('infogalore-folders-media');
        }
    }

    /**
     * Finds id value from string.
     *
     * @param string $string
     *
     * @return int
     */
    private function parse_string_with_id($string)
    {
        if (preg_match('/\[(\d+)\]$/', $string, $m)) {
            return $m[1];
        }

        return 0;
    }
}