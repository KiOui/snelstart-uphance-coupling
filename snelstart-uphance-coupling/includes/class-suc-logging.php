<?php
/**
* custom post type for email log
*/

include_once SUC_ABSPATH . 'includes/client/class-api-exception.php';

class SUCLogging {

	protected static ?SUCLogging $_instance = null;
	protected ?int $log_id;

	/**
	 * Snelstart Uphance Coupling Core.
	 *
	 * Uses the Singleton pattern to load 1 instance of this class at maximum
	 *
	 * @static
	 * @return SUCLogging
	 */
	public static function instance(): SUCLogging {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

    /**
    * hooks
    */
    public function __construct(int $log_id=null) {
        add_action('admin_init', array($this, 'init'));
        add_action('init', array($this, 'register'));
		$this->log_id = $log_id;
    }

	public function write(string|SUCAPIException $message) {
		if (gettype($message) !== "string") {
			$message = $message->getMessage();
		}
		if ( isset( $this->log_id ) ) {
			$log_messages = get_post_meta( $this->log_id, "suc_log_messages_json". true );
			array_push( $log_messages, $message );
			update_post_meta( $this->log_id, "suc_log_messages_json", $log_messages );
		} else {
			$this->log_id = wp_insert_post(array(
				"post_type" => "suc_log_messages",
				"meta_input" => array(
					"suc_log_messages_json" => json_encode(array(
						$message
					)),
				),
			));
		}
	}

    /**
    * admin_init action
    */
    public function init() {
        global $typenow;

        if (empty($typenow)) {
            // try to pick it up from the query string
            if (!empty($_GET['post'])) {
                $post = get_post($_GET['post']);
                $typenow = $post->post_type;
            }
        }

        if ($typenow == 'suc_log_messages') {
            add_filter('display_post_states', '__return_false');
            add_action('edit_form_after_title', array($this, 'admin_edit_after_title'), 100);
            add_filter('post_row_actions', array($this, 'admin_post_row_actions'), 10, 2);
            add_filter('bulk_actions-edit-suc_log_messages', array($this, 'admin_bulk_actions_edit'));

            add_filter('views_edit-suc_log_messages', array($this, 'admin_views_edit'));

            if (is_admin()) {
                add_filter('gettext', array($this, 'admin_get_text'), 10, 3);
            }
        }
    }

    /**
    * register Custom Post Type
    */
    public function register() {
	    register_post_type('suc_log_messages', array(
		    'label' => __('Log messages', 'snelstart-uphance-coupling'),
		    'labels' => array(
			    'name' => __('Log messages', 'snelstart-uphance-coupling'),
			    'singular_name' => __('Log message', 'snelstart-uphance-coupling'),
			    'add_new' => __('Add New', 'snelstart-uphance-coupling'),
			    'add_new_item' => __('Add New Log message', 'snelstart-uphance-coupling'),
			    'edit_item' => __('View Log message', 'snelstart-uphance-coupling'),
			    'new_item' => __('New Log message', 'snelstart-uphance-coupling'),
			    'view_item' => __('View Log message', 'snelstart-uphance-coupling'),
			    'view_items' => __('View Log messages', 'snelstart-uphance-coupling'),
			    'search_items' => __('Search Log messages', 'snelstart-uphance-coupling'),
			    'not_found' => __('No Log messages found', 'snelstart-uphance-coupling'),
			    'not_found_in_trash' => __('No log messages found in trash', 'snelstart-uphance-coupling'),
			    'parent_item_colon' => __('Parent Log message', 'snelstart-uphance-coupling'),
			    'all_items' => __('All Log messages', 'snelstart-uphance-coupling'),
			    'archives' => __('Log message Archives', 'snelstart-uphance-coupling'),
			    'attributes' => __('Log message Attributes', 'snelstart-uphance-coupling'),
			    'insert_into_item' => __('Insert into log message', 'snelstart-uphance-coupling'),
			    'uploaded_to_this_item' => __('Uploaded to this log message', 'snelstart-uphance-coupling'),
			    'featured_image' => __('Featured image', 'snelstart-uphance-coupling'),
			    'set_featured_image' => __('Set featured image', 'snelstart-uphance-coupling'),
			    'remove_featured_image' => __('Remove featured image', 'snelstart-uphance-coupling'),
			    'use_featured_image' => __('Use as featured image', 'snelstart-uphance-coupling'),
			    'menu_name' => __('Log messages', 'snelstart-uphance-coupling'),
			    'filter_items_list' => __('Filter log messages list', 'snelstart-uphance-coupling'),
			    'filter_by_date' => __('Filter by date', 'snelstart-uphance-coupling'),
			    'items_list_navigation' => __('Log messages list navigation', 'snelstart-uphance-coupling'),
			    'items_list' => __('Log messages list', 'snelstart-uphance-coupling'),
			    'item_published' => __('Log message published', 'snelstart-uphance-coupling'),
			    'item_published_privately' => __('Log message published privately', 'snelstart-uphance-coupling'),
			    'item_reverted_to_draft' => __('Log message reverted to draft', 'snelstart-uphance-coupling'),
			    'item_scheduled' => __('Log message scheduled', 'snelstart-uphance-coupling'),
			    'item_updated' => __('Log message updated', 'snelstart-uphance-coupling'),
		    ),
		    'capabilities' => array (
			    'create_posts' => false,
			    'edit_post' => 'manage_options',
			    'read_post' => 'manage_options',
			    'delete_post' => 'manage_options',
			    'edit_posts' => 'manage_options',
			    'edit_others_posts' => 'manage_options',
			    'publish_posts' => false,
			    'read_private_posts' => 'manage_options',
		    ),
		    'description' => __('Log message post type', 'snelstart-uphance-coupling'),
		    'public' => false,
		    'hierarchical' => false,
		    'exclude_from_search' => true,
		    'publicly_queryable' => false,
		    'show_ui' => true,
		    'show_in_menu' => true,
		    'show_in_nav_menus' => false,
		    'show_in_admin_bar' => true,
		    'show_in_rest' => false,
		    'menu_position' => 56,
		    'menu_icon' => 'dashicons-format-quote',
		    'taxonomies' => array(),
		    'has_archive' => false,
		    'can_export' => true,
		    'delete_with_user' => false
	    ));
	    remove_post_type_support('suc_log_messages', 'editor');
        remove_post_type_support('suc_log_messages', 'title');
    }

    /**
    * change some text on admin pages
    * @param string $translation
    * @param string $text
    * @param string $domain
    * @return string
    */
    public function admin_get_text($translation, $text, $domain) {
        if ($domain == 'default') {
            if ($text == 'Edit &#8220;%s&#8221;') {
                $translation = 'View &#8220;%s&#8221;';
            }
        }

        return $translation;
    }

    /**
    * remove views we don't need from post list
    * @param array $views
    * @return array
    */
    public function admin_views_edit(array $views): array {
        unset($views['publish']);
        unset($views['draft']);

        return $views;
    }

	/**
	 * drop all the metaboxes and output what we want to show
	 */
	public function admin_edit_after_title($post) {
		global $wp_meta_boxes;

		$wp_meta_boxes = array('suc_log_messages' => array(
			'advanced' => array(),
			'side' => array(),
			'normal' => array(),
		));

		require SUC_ABSPATH . 'views/suc-log-details.php';
	}

    /**
    * remove unwanted actions from post list
    * @param array $actions
    * @param WP_Post $post
    * @return array
    */
    public function admin_post_row_actions(array $actions, $post): array {
        unset($actions['inline hide-if-no-js']);
        unset($actions['edit']);

        if ($post && $post->ID) {
            $actions['view'] = sprintf('<a href="%s" title="%s">%s</a>',
                get_edit_post_link($post->ID),
                __('View', 'snelstart-uphance-coupling'), __('View', 'snelstart-uphance-coupling'));
        }

        return $actions;
    }

    /**
    * change the list of available bulk actions
    * @param array $actions
    * @return array
    */
    public function admin_bulk_actions_edit(array $actions): array {
        unset($actions['edit']);
        return $actions;
    }

}