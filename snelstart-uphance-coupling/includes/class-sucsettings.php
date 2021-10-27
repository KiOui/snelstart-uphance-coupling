<?php
/**
 * Settings class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCSettings' ) ) {
	/**
	 * Snelstart Uphance Coupling Settings class
	 *
	 * @class SUCSettings
	 */
	class SUCSettings {
		/**
		 * The single instance of the class
		 *
		 * @var SUCSettings|null
		 */
		protected static ?SUCSettings $_instance = null;

		/**
		 * Snelstart Uphance Coupling Settings
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return SUCSettings
		 */
		public static function instance(): SUCSettings {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * SUCSettings constructor.
		 */
		public function __construct() {
			$this->actions_and_filters();
		}

		/**
		 * Add actions and filters.
		 */
		public function actions_and_filters() {
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 99 );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}

		/**
		 * Add Autotelex Inventory Settings menu page.
		 */
		public function add_menu_page() {
			add_menu_page(
				esc_html__( 'Snelstart Uphance Coupling', 'snelstart-uphance-coupling' ),
				esc_html__( 'Snelstart Uphance Coupling', 'snelstart-uphance-coupling' ),
				'edit_plugins',
				'suc_admin_menu',
				null,
				'dashicons-rest-api',
				56
			);
			add_submenu_page(
				'suc_admin_menu',
				esc_html__( 'Snelstart Uphance Coupling Dashboard', 'snelstart-uphance-coupling' ),
				esc_html__( 'Dashboard', 'snelstart-uphance-coupling' ),
				'edit_plugins',
				'suc_admin_menu',
				array( $this, 'suc_admin_menu_dashboard_callback' )
			);
		}

		/**
		 * Register Autotelex Settings.
		 */
		public function register_settings() {
			register_setting(
				'suc_settings',
				'suc_settings',
				array( $this, 'suc_settings_validate' )
			);

			add_settings_section(
				'snelstart_key_settings',
				__( 'Snelstart Key settings', 'snelstart-uphance-coupling' ),
				array( $this, 'suc_settings_callback' ),
				'suc_settings'
			);

			add_settings_field(
				'snelstart_client_key',
				__( 'Snelstart Client Key', 'snelstart-uphance-coupling' ),
				array( $this, 'snelstart_client_key_renderer' ),
				'suc_settings',
				'snelstart_key_settings'
			);

			add_settings_field(
				'snelstart_subscription_key',
				__( 'Snelstart Subscription Key', 'snelstart-uphance-coupling' ),
				array( $this, 'snelstart_subscription_key_renderer' ),
				'suc_settings',
				'snelstart_key_settings'
			);
		}

		/**
		 * Validate Snelstart Uphance Coupling settings.
		 *
		 * @param $input
		 *
		 * @return array
		 */
		public function suc_settings_validate( $input ): array {
			$output['snelstart_client_key']     = $input['snelstart_client_key']; // TODO: Add sanitization
            $output['snelstart_subscription_key'] = $input['snelstart_subscription_key'];

			return $output;
		}

		/**
		 * Render Snelstart Client Key setting.
		 */
		public function snelstart_client_key_renderer() {
			$options = get_option( 'suc_settings' ); ?>
			<p><?php echo esc_html( __( 'The snelstart API client key', 'snelstart-uphance-coupling' ) ); ?></p>
			<input type='text' name='suc_settings[snelstart_client_key]'
				   value="<?php echo esc_attr( $options['snelstart_client_key'] ); ?>">
			<?php
		}

		/**
		 * Render Snelstart Subscription key setting.
		 */
		public function snelstart_subscription_key_renderer() {
			$options = get_option( 'suc_settings' ); ?>
            <p><?php echo esc_html( __( 'The snelstart API subscription key', 'snelstart-uphance-coupling' ) ); ?></p>
            <input type='text' name='suc_settings[snelstart_subscription_key]'
                   value="<?php echo esc_attr( $options['snelstart_subscription_key'] ); ?>">
			<?php
		}

		/**
		 * Render the section title of autotelex url settings.
		 */
		public function suc_settings_callback() {
			echo esc_html( __( 'Snelstart Uphance Coupling settings', 'snelstart-uphance-coupling' ) );
		}

		/**
		 * Admin menu dashboard callback.
		 */
		public function suc_admin_menu_dashboard_callback() {
			include_once SUC_ABSPATH . 'views/suc-admin-dashboard-view.php';
		}
	}
}
