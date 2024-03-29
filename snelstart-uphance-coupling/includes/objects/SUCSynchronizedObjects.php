<?php
/**
 * Synchronized objects class.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCSynchronizedObjects' ) ) {
	/**
	 * Synchronized objects class.
	 *
	 * @class SUCSynchronizedObjects
	 */
	class SUCSynchronizedObjects {

		/**
		 * Add actions and filters.
		 *
		 * @return void
		 */
		public static function init() {
			add_action( 'init', array( 'SUCSynchronizedObjects', 'add_custom_post_types' ) );
			add_action( 'init', array( 'SUCSynchronizedObjects', 'register_synchronized_objects_post_meta' ) );
			add_action( 'admin_menu', array( 'SUCSynchronizedObjects', 'add_admin_menu' ) );
			add_action(
				'admin_enqueue_scripts',
				array(
					'SUCSynchronizedObjects',
					'synchronized_objects_scripts',
				)
			);
			add_filter(
				'rest_suc_synchronized_query',
				array(
					'SUCSynchronizedObjects',
					'filter_query',
				),
				10,
				2
			);
		}

		/**
		 * Filter WP_REST_Request query when certain arguments are set.
		 *
		 * @param array           $args The arguments of the query.
		 * @param WP_REST_Request $request The REST Request.
		 *
		 * @return array The adjusted arguments of the query.
		 */
		public static function filter_query( array $args, WP_REST_Request $request ) {
			if ( ! isset( $args['meta_query'] ) ) {
				$args['meta_query'] = array();
			}

			if ( $request->get_param( 'succeeded' ) === 'true' || $request->get_param( 'succeeded' ) === 'false' ) {
				$succeeded            = $request->get_param( 'succeeded' ) === 'true';
				$args['meta_query'][] = array(
					'key'   => 'succeeded',
					'value' => $succeeded,
				);
			}

			if ( $request->get_param( 'type' ) !== null ) {
				$type                 = strval( $request->get_param( 'type' ) );
				$args['meta_query'][] = array(
					'key'   => 'type',
					'value' => $type,
				);
			}

			if ( $request->get_param( 'source' ) !== null ) {
				$source               = strval( $request->get_param( 'source' ) );
				$args['meta_query'][] = array(
					'key'   => 'source',
					'value' => $source,
				);
			}

			if ( $request->get_param( 'method' ) !== null ) {
				$method = strval( $request->get_param( 'method' ) );
				if ( 'create' === $method || 'update' === $method || 'delete' === $method ) {
					$args['meta_query'][] = array(
						'key'   => 'method',
						'value' => $method,
					);
				}
			}

			return $args;
		}

		/**
		 * Add synchronized objects custom post type.
		 *
		 * @return void
		 */
		public static function add_custom_post_types() {
			register_post_type(
				'suc_synchronized',
				array(
					'label'               => __( 'Synchronized objects', 'snelstart-uphance-coupling' ),
					'labels'              => array(
						'name'                     => __( 'Synchronized objects', 'snelstart-uphance-coupling' ),
						'singular_name'            => __( 'Synchronized object', 'snelstart-uphance-coupling' ),
						'add_new'                  => __( 'Add New', 'snelstart-uphance-coupling' ),
						'add_new_item'             => __( 'Add New Synchronized object', 'snelstart-uphance-coupling' ),
						'edit_item'                => __( 'View Synchronized object', 'snelstart-uphance-coupling' ),
						'new_item'                 => __( 'New Synchronized object', 'snelstart-uphance-coupling' ),
						'view_item'                => __( 'View Synchronized object', 'snelstart-uphance-coupling' ),
						'view_items'               => __( 'View Synchronized objects', 'snelstart-uphance-coupling' ),
						'search_items'             => __( 'Search Synchronized objects', 'snelstart-uphance-coupling' ),
						'not_found'                => __( 'No Synchronized objects found', 'snelstart-uphance-coupling' ),
						'not_found_in_trash'       => __( 'No synchronized objects found in trash', 'snelstart-uphance-coupling' ),
						'parent_item_colon'        => __( 'Parent Synchronized object', 'snelstart-uphance-coupling' ),
						'all_items'                => __( 'All Synchronized objects', 'snelstart-uphance-coupling' ),
						'archives'                 => __( 'Synchronized object Archives', 'snelstart-uphance-coupling' ),
						'attributes'               => __( 'Synchronized object Attributes', 'snelstart-uphance-coupling' ),
						'insert_into_item'         => __( 'Insert into synchronized object', 'snelstart-uphance-coupling' ),
						'uploaded_to_this_item'    => __( 'Uploaded to this synchronized object', 'snelstart-uphance-coupling' ),
						'featured_image'           => __( 'Featured image', 'snelstart-uphance-coupling' ),
						'set_featured_image'       => __( 'Set featured image', 'snelstart-uphance-coupling' ),
						'remove_featured_image'    => __( 'Remove featured image', 'snelstart-uphance-coupling' ),
						'use_featured_image'       => __( 'Use as featured image', 'snelstart-uphance-coupling' ),
						'menu_name'                => __( 'Synchronized objects', 'snelstart-uphance-coupling' ),
						'filter_items_list'        => __( 'Filter synchronized objects list', 'snelstart-uphance-coupling' ),
						'filter_by_date'           => __( 'Filter by date', 'snelstart-uphance-coupling' ),
						'items_list_navigation'    => __( 'Synchronized objects list navigation', 'snelstart-uphance-coupling' ),
						'items_list'               => __( 'Synchronized objects list', 'snelstart-uphance-coupling' ),
						'item_published'           => __( 'Synchronized object published', 'snelstart-uphance-coupling' ),
						'item_published_privately' => __( 'Synchronized object published privately', 'snelstart-uphance-coupling' ),
						'item_reverted_to_draft'   => __( 'Synchronized object reverted to draft', 'snelstart-uphance-coupling' ),
						'item_scheduled'           => __( 'Synchronized object scheduled', 'snelstart-uphance-coupling' ),
						'item_updated'             => __( 'Synchronized object updated', 'snelstart-uphance-coupling' ),
					),
					'capabilities'        => array(
						'create_posts'       => false,
						'edit_post'          => 'manage_options',
						'read_post'          => 'manage_options',
						'delete_post'        => 'manage_options',
						'edit_posts'         => 'manage_options',
						'edit_others_posts'  => 'manage_options',
						'publish_posts'      => false,
						'read_private_posts' => 'manage_options',
					),
					'description'         => __( 'Synchronized object post type', 'snelstart-uphance-coupling' ),
					'public'              => false,
					'hierarchical'        => false,
					'exclude_from_search' => true,
					'publicly_queryable'  => false,
					'show_ui'             => false,
					'show_in_menu'        => false,
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => false,
					'show_in_rest'        => current_user_can( 'manage_options' ),
					'menu_position'       => 56,
					'menu_icon'           => 'dashicons-update-alt',
					'taxonomies'          => array(),
					'has_archive'         => false,
					'can_export'          => true,
					'delete_with_user'    => false,
				)
			);
			add_post_type_support( 'suc_synchronized', 'custom-fields' );
		}

		/**
		 * Add synchronized objects menu to the admin bar.
		 *
		 * @return void
		 */
		public static function add_admin_menu() {
			add_menu_page(
				'Synchronized objects',
				'Synchronized objects',
				'manage_options',
				'synchronized-objects',
				array( 'SUCSynchronizedObjects', 'render_synchronized_objects_menu_page' ),
				'dashicons-update-alt',
				57
			);
		}

		/**
		 * Render synchronized objects menu page.
		 *
		 * @return void
		 */
		public static function render_synchronized_objects_menu_page() {
			include_once SUC_ABSPATH . '/views/suc-synchronized-objects-list-view.php';
		}

		/**
		 * Add scripts and styles for synchronized objects overview page.
		 *
		 * @param string $hook_suffix The page that is being loaded.
		 *
		 * @return void
		 */
		public static function synchronized_objects_scripts( string $hook_suffix ) {
			if ( 'toplevel_page_synchronized-objects' === $hook_suffix ) {
				wp_enqueue_script( 'suc-vuejs', 'https://unpkg.com/vue@3/dist/vue.global.js', array(), '3' );
				wp_enqueue_style( 'suc-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css', array(), '5.3.0-alpha3' );
				wp_enqueue_script( 'suc-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js', array(), '5.3.0-alpha3' );
				wp_enqueue_script( 'suc-tata', SUC_PLUGIN_URI . '/assets/js/tata.js', array(), '1.0' );
				wp_enqueue_script( 'suc-shared', SUC_PLUGIN_URI . '/assets/js/shared.js', array(), '1.0' );
				wp_enqueue_style( 'suc-tata-fix', SUC_PLUGIN_URI . '/assets/css/tata-fix.css', array(), '1.0' );
				wp_enqueue_style( 'suc-shared', SUC_PLUGIN_URI . '/assets/css/shared.css', array(), '1.0' );
			}
		}

		/**
		 * Create a synchronized object.
		 *
		 * @param int         $object_id The object ID of the synchronized object.
		 * @param string      $object_type The type of the synchronized object.
		 * @param bool        $succeeded Whether the synchronization succeeded.
		 * @param string      $source The source of the synchronization.
		 * @param string      $method The method of the synchronization (create, update, delete).
		 * @param string|null $url The URL of the object.
		 * @param string|null $error_message The error message that occurred during synchronization.
		 * @param array|null  $extra_data Possible extra data.
		 *
		 * @return void
		 */
		public static function create_synchronized_object( int $object_id, string $object_type, bool $succeeded, string $source, string $method, ?string $url, ?string $error_message = null, ?array $extra_data = null ) {
			if ( null === $extra_data ) {
				$extra_data = array();
			}

			wp_insert_post(
				array(
					'post_type'   => 'suc_synchronized',
					'post_title'  => 'Object #' . $object_id,
					'post_status' => 'publish',
					'meta_input'  => array(
						'succeeded'     => $succeeded,
						'type'          => $object_type,
						'id'            => $object_id,
						'source'        => $source,
						'method'        => $method,
						'url'           => $url,
						'error_message' => $error_message,
						'extra_data'    => $extra_data,
					),
				)
			);
		}

		/**
		 * Register post meta for synchronized objects.
		 *
		 * @return void
		 */
		public static function register_synchronized_objects_post_meta() {
			register_post_meta(
				'suc_synchronized',
				'succeeded',
				array(
					'type'         => 'boolean',
					'single'       => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_synchronized',
				'type',
				array(
					'type'         => 'string',
					'single'       => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_synchronized',
				'id',
				array(
					'type'         => 'number',
					'single'       => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_synchronized',
				'url',
				array(
					'type'         => 'string',
					'single'       => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_synchronized',
				'error_message',
				array(
					'type'         => 'string',
					'single'       => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_synchronized',
				'source',
				array(
					'type'         => 'string',
					'single'       => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_synchronized',
				'method',
				array(
					'type'         => 'string',
					'single'       => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_synchronized',
				'extra_data',
				array(
					'type'         => 'object',
					'single'       => true,
					'show_in_rest' => array(
						'schema' => array(
							'type'                 => 'object',
							'additionalProperties' => true,
						),
					),
				)
			);
		}
	}
}
