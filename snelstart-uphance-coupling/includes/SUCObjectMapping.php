<?php
/**
 * Object Mapping class.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCObjectMapping' ) ) {
	/**
	 * Object Mapping class.
	 *
	 * @class SUCObjectMapping
	 */
	class SUCObjectMapping {

		/**
		 * Add actions and filters.
		 *
		 * @return void
		 */
		public static function init() {
			add_action( 'init', array( 'SUCObjectMapping', 'add_custom_post_types' ) );
			add_action( 'init', array( 'SUCObjectMapping', 'register_mapped_object_post_meta' ) );
		}

		/**
		 * Add synchronized objects custom post type.
		 *
		 * @return void
		 */
		public static function add_custom_post_types() {
			register_post_type(
				'suc_mapped_object',
				array(
					'label'               => __( 'Mapped objects', 'snelstart-uphance-coupling' ),
					'labels'              => array(
						'name'                     => __( 'Mapped objects', 'snelstart-uphance-coupling' ),
						'singular_name'            => __( 'Mapped object', 'snelstart-uphance-coupling' ),
						'add_new'                  => __( 'Add New', 'snelstart-uphance-coupling' ),
						'add_new_item'             => __( 'Add New Mapped object', 'snelstart-uphance-coupling' ),
						'edit_item'                => __( 'View Mapped object', 'snelstart-uphance-coupling' ),
						'new_item'                 => __( 'New Mapped object', 'snelstart-uphance-coupling' ),
						'view_item'                => __( 'View Mapped object', 'snelstart-uphance-coupling' ),
						'view_items'               => __( 'View Mapped objects', 'snelstart-uphance-coupling' ),
						'search_items'             => __( 'Search Mapped objects', 'snelstart-uphance-coupling' ),
						'not_found'                => __( 'No Mapped objects found', 'snelstart-uphance-coupling' ),
						'not_found_in_trash'       => __( 'No mapped objects found in trash', 'snelstart-uphance-coupling' ),
						'parent_item_colon'        => __( 'Parent Mapped object', 'snelstart-uphance-coupling' ),
						'all_items'                => __( 'All Mapped objects', 'snelstart-uphance-coupling' ),
						'archives'                 => __( 'Mapped object Archives', 'snelstart-uphance-coupling' ),
						'attributes'               => __( 'Mapped object Attributes', 'snelstart-uphance-coupling' ),
						'insert_into_item'         => __( 'Insert into mapped object', 'snelstart-uphance-coupling' ),
						'uploaded_to_this_item'    => __( 'Uploaded to this mapped object', 'snelstart-uphance-coupling' ),
						'featured_image'           => __( 'Featured image', 'snelstart-uphance-coupling' ),
						'set_featured_image'       => __( 'Set featured image', 'snelstart-uphance-coupling' ),
						'remove_featured_image'    => __( 'Remove featured image', 'snelstart-uphance-coupling' ),
						'use_featured_image'       => __( 'Use as featured image', 'snelstart-uphance-coupling' ),
						'menu_name'                => __( 'Mapped objects', 'snelstart-uphance-coupling' ),
						'filter_items_list'        => __( 'Filter mapped objects list', 'snelstart-uphance-coupling' ),
						'filter_by_date'           => __( 'Filter by date', 'snelstart-uphance-coupling' ),
						'items_list_navigation'    => __( 'Mapped objects list navigation', 'snelstart-uphance-coupling' ),
						'items_list'               => __( 'Mapped objects list', 'snelstart-uphance-coupling' ),
						'item_published'           => __( 'Mapped object published', 'snelstart-uphance-coupling' ),
						'item_published_privately' => __( 'Mapped object published privately', 'snelstart-uphance-coupling' ),
						'item_reverted_to_draft'   => __( 'Mapped object reverted to draft', 'snelstart-uphance-coupling' ),
						'item_scheduled'           => __( 'Mapped object scheduled', 'snelstart-uphance-coupling' ),
						'item_updated'             => __( 'Mapped object updated', 'snelstart-uphance-coupling' ),
					),
					'capabilities'        => array(
						'create_posts'       => false,
						'edit_post'          => false,
						'read_post'          => false,
						'delete_post'        => false,
						'edit_posts'         => false,
						'edit_others_posts'  => false,
						'publish_posts'      => false,
						'read_private_posts' => false,
					),
					'description'         => __( 'Mapped object post type', 'snelstart-uphance-coupling' ),
					'public'              => false,
					'hierarchical'        => false,
					'exclude_from_search' => true,
					'publicly_queryable'  => false,
					'show_ui'             => false,
					'show_in_menu'        => false,
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => false,
					'show_in_rest'        => false,
					'menu_position'       => 57,
					'menu_icon'           => 'dashicons-randomize',
					'taxonomies'          => array(),
					'has_archive'         => false,
					'can_export'          => true,
					'delete_with_user'    => false,
				)
			);
			add_post_type_support( 'suc_mapped_object', 'custom-fields' );
		}

		/**
		 * Create a mapped object.
		 *
		 * @return void
		 */
		public static function create_mapped_object( string $type, string $mapped_from_service, string $mapped_to_service, string $mapped_from_object_id, string $mapped_to_object_id ) {
			wp_insert_post(
				array(
					'post_type' => 'suc_mapped_object',
					'post_title' => "Object $type map from $mapped_from_service to $mapped_to_service with ID ($mapped_from_object_id, $mapped_to_object_id)",
					'post_status' => 'publish',
					'meta_input' => array(
						'type' => $type,
						'mapped_from_service' => $mapped_from_service,
						'mapped_to_service' => $mapped_to_service,
						'mapped_from_object_id' => $mapped_from_object_id,
						'mapped_to_object_id' => $mapped_to_object_id,
					),
				)
			);
		}

		/**
		 * Get a mapped object.
		 *
		 * @return WP_Post|null The mapped object if it exists, else null.
		 */
		public static function get_mapped_object( string $type, string $mapped_from_service, string $mapped_to_service, string $mapped_from_object_id ): ?WP_Post {
			$posts = get_posts(
				array(
					'post_type' => 'suc_mapped_object',
					'meta_query' => array(
						array(
							'key'     => 'type',
							'value'   => $type,
							'compare' => '=',
						),
						array(
							'key' => 'mapped_from_service',
							'value' => $mapped_from_service,
							'compare' => '=',
						),
						array(
							'key' => 'mapped_to_service',
							'value' => $mapped_to_service,
							'compare' => '=',
						),
						array(
							'key' => 'mapped_from_object_id',
							'value' => $mapped_from_object_id,
							'compare' => '=',
						),
					),
				)
			);
			if ( count( $posts ) > 0 ) {
				return $posts[0];
			} else {
				return null;
			}
		}

		/**
		 * Register post meta for synchronized objects.
		 *
		 * @return void
		 */
		public static function register_mapped_object_post_meta() {
			register_post_meta(
				'suc_mapped_object',
				'type',
				array(
					'type' => 'string',
					'single' => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_mapped_object',
				'mapped_from_service',
				array(
					'type' => 'string',
					'single' => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_mapped_object',
				'mapped_to_service',
				array(
					'type' => 'string',
					'single' => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_mapped_object',
				'mapped_from_object_id',
				array(
					'type' => 'string',
					'single' => true,
					'show_in_rest' => true,
				)
			);
			register_post_meta(
				'suc_mapped_object',
				'mapped_to_object_id',
				array(
					'type' => 'string',
					'single' => true,
					'show_in_rest' => true,
				)
			);
		}

	}
}
