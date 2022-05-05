<?php
/**
 * Error Logging class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCErrorLogging' ) ) {
	/**
	 * Class for logging errors
	 *
	 * @class SUCErrorLogging
	 */
	class SUCErrorLogging {

		/**
		 * Wordpress ID of the error.
		 *
		 * This variable is set after the first save.
		 *
		 * @var int|null
		 */
		protected ?int $error_id;

		/**
		 * Type of the occurred error.
		 *
		 * @var string
		 */
		protected string $type;

		/**
		 * Key of the synchronizer that can potentially fix the error.
		 *
		 * @var ?string
		 */
		protected ?string $synchronizer;

		/**
		 * ID of the object that failed to synchronize.
		 *
		 * @var ?string
		 */
		protected ?string $object_id;

		/**
		 * Constructor.
		 *
		 * @param int|null $error_id if set, this error id will be used to save error to, if not set a new Wordpress post will be created.
		 */
		public function __construct( int $error_id = null ) {
			$this->error_id = $error_id;
		}

		/**
		 * Add error log.
		 *
		 * @param string|SUCAPIException|Exception $error the error that occurred.
		 */
		public function set_error( $error, string $error_type, ?string $synchronizer, ?string $object_id ) {
			if ( gettype( $error ) !== 'string' ) {
				$error = $error->__toString();
			}
			if ( isset( $this->error_id ) ) {
				// TODO: Change this.
				update_post_meta( $this->error_id, 'suc_error_type', $error_type );
				update_post_meta( $this->error_id, 'suc_synchronizer', $synchronizer );
				update_post_meta( $this->error_id, 'suc_object_id', $object_id );
			} else {
				$date         = new DateTime( 'now' );
				$this->error_id = wp_insert_post(
					array(
						'post_type'  => 'suc_errors',
						'post_title' => $date->format( 'Y-m-d H:i:s' ),
						'post_status' => 'publish',
						'post_content' => $error,
						'meta_input' => array(
							'suc_error_type' => $error_type,
							'suc_synchronizer' => $synchronizer,
							'suc_object_id' => $object_id,
						),
					)
				);
			}
		}

		/**
		 * Remove some features from the custom post type (to disable adding logs by users).
		 */
		public static function init() {
			global $typenow;

			$set_type = null;

			if ( empty( $typenow ) && array_key_exists( 'post', $_GET ) ) {
				$post_id = empty( absint( wp_unslash( $_GET['post'] ) ) ) ? null : absint( wp_unslash( $_GET['post'] ) );
				if ( isset( $post_id ) ) {
					$post    = get_post( $post_id );
					$set_type = $post->post_type;
				}
			}

			if ( ( isset( $set_type ) && 'suc_errors' == $set_type ) || ( ! isset( $set_type ) && 'suc_errors' == $typenow ) ) {
				add_filter( 'display_post_states', '__return_false' );
				add_filter( 'views_edit-suc_errors', array( 'SUCErrorLogging', 'admin_views_edit' ) );
			}

			if ( is_admin() ) {
				add_filter( 'manage_suc_errors_posts_columns', array( 'SUCErrorLogging', 'manage_post_columns' ) );
				add_action( 'manage_suc_errors_posts_custom_column', array( 'SUCErrorLogging', 'error_logs_column_values' ), 10, 2 );
			}

			self::add_meta_box_support();
			self::register();
		}

		/**
		 * Update the admin columns for suc_errors post type.
		 *
		 * @param array $columns the current admin columns.
		 *
		 * @return array the admin columns with the song price added.
		 */
		public static function manage_post_columns( array $columns ): array {
			$columns['suc_error_log_message'] = __( 'Message', 'snelstart-uphance-coupling' );
			return $columns;
		}

		/**
		 * Output the column value for the requested_songs post type.
		 *
		 * @param string $column the column that is being rendered.
		 * @param int    $post_id the post ID of the post.
		 *
		 * @return void
		 */
		public static function error_logs_column_values( $column, $post_id ) {
			if ( 'suc_error_log_message' === $column ) {
				$message = get_post( $post_id )->post_content;
				echo esc_html( $message );
			}
		}

		/**
		 * Register Custom Post type.
		 */
		public static function register() {
			register_post_type(
				'suc_errors',
				array(
					'label'               => __( 'Errors', 'snelstart-uphance-coupling' ),
					'labels'              => array(
						'name'                     => __( 'Errors', 'snelstart-uphance-coupling' ),
						'singular_name'            => __( 'Error', 'snelstart-uphance-coupling' ),
						'add_new'                  => __( 'Add New', 'snelstart-uphance-coupling' ),
						'add_new_item'             => __( 'Add New Error', 'snelstart-uphance-coupling' ),
						'edit_item'                => __( 'View Error', 'snelstart-uphance-coupling' ),
						'new_item'                 => __( 'New Error', 'snelstart-uphance-coupling' ),
						'view_item'                => __( 'View Error', 'snelstart-uphance-coupling' ),
						'view_items'               => __( 'View Errors', 'snelstart-uphance-coupling' ),
						'search_items'             => __( 'Search Errors', 'snelstart-uphance-coupling' ),
						'not_found'                => __( 'No Errors found', 'snelstart-uphance-coupling' ),
						'not_found_in_trash'       => __( 'No errors found in trash', 'snelstart-uphance-coupling' ),
						'parent_item_colon'        => __( 'Parent Error', 'snelstart-uphance-coupling' ),
						'all_items'                => __( 'All Errors', 'snelstart-uphance-coupling' ),
						'archives'                 => __( 'Error Archives', 'snelstart-uphance-coupling' ),
						'attributes'               => __( 'Error Attributes', 'snelstart-uphance-coupling' ),
						'insert_into_item'         => __( 'Insert into error', 'snelstart-uphance-coupling' ),
						'uploaded_to_this_item'    => __( 'Uploaded to this error', 'snelstart-uphance-coupling' ),
						'featured_image'           => __( 'Featured image', 'snelstart-uphance-coupling' ),
						'set_featured_image'       => __( 'Set featured image', 'snelstart-uphance-coupling' ),
						'remove_featured_image'    => __( 'Remove featured image', 'snelstart-uphance-coupling' ),
						'use_featured_image'       => __( 'Use as featured image', 'snelstart-uphance-coupling' ),
						'menu_name'                => __( 'Errors', 'snelstart-uphance-coupling' ),
						'filter_items_list'        => __( 'Filter errors list', 'snelstart-uphance-coupling' ),
						'filter_by_date'           => __( 'Filter by date', 'snelstart-uphance-coupling' ),
						'items_list_navigation'    => __( 'Errors list navigation', 'snelstart-uphance-coupling' ),
						'items_list'               => __( 'Errors list', 'snelstart-uphance-coupling' ),
						'item_published'           => __( 'Error published', 'snelstart-uphance-coupling' ),
						'item_published_privately' => __( 'Error published privately', 'snelstart-uphance-coupling' ),
						'item_reverted_to_draft'   => __( 'Error reverted to draft', 'snelstart-uphance-coupling' ),
						'item_scheduled'           => __( 'Error scheduled', 'snelstart-uphance-coupling' ),
						'item_updated'             => __( 'Error updated', 'snelstart-uphance-coupling' ),
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
					'description'         => __( 'Error post type', 'snelstart-uphance-coupling' ),
					'public'              => false,
					'hierarchical'        => false,
					'exclude_from_search' => true,
					'publicly_queryable'  => false,
					'show_ui'             => true,
					'show_in_menu'        => true,
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => true,
					'show_in_rest'        => false,
					'menu_position'       => 56,
					'menu_icon'           => 'dashicons-welcome-comments',
					'taxonomies'          => array(),
					'has_archive'         => false,
					'can_export'          => true,
					'delete_with_user'    => false,
				)
			);
		}

		/**
		 * Add meta box support to Custom Post types.
		 */
		public static function add_meta_box_support() {
			include_once SUC_ABSPATH . '/includes/metaboxes/class-metabox.php';
			new Metabox(
				'suc_error_details',
				array(
					array(
						'label' => __( 'Error type', 'snelstart-uphance-coupling' ),
						'desc'  => __( 'The type of the error', 'snelstart-uphance-coupling' ),
						'id'    => 'suc_error_type',
						'type'  => 'text',
						'required' => true,
						'default' => 'default',
					),
					array(
						'label' => __( 'Error synchronizer', 'snelstart-uphance-coupling' ),
						'desc'  => __( 'The key of the synchronizer used to synchronize this error', 'snelstart-uphance-coupling' ),
						'id'    => 'suc_synchronizer',
						'type'  => 'text',
						'required' => false,
					),
					array(
						'label' => __( 'Object ID', 'snelstart-uphance-coupling' ),
						'desc'  => __( 'The object id of the error', 'snelstart-uphance-coupling' ),
						'id'    => 'suc_object_id',
						'type'  => 'text',
						'required' => false,
					),
				),
				'suc_errors',
				__( 'Error details' )
			);
		}

		/**
		 * Remove views we don't need from post list.
		 *
		 * @param array $views views of the post list.
		 *
		 * @return array views of the post list with publish and draft removed.
		 */
		public static function admin_views_edit( array $views ): array {
			unset( $views['draft'] );

			return $views;
		}
	}
}
