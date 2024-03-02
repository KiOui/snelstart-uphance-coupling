<?php
/**
 * Core class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCCore' ) ) {
	/**
	 * Snelstart Uphance Coupling Core class
	 *
	 * @class SUCCore
	 */
	class SUCCore {
		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		public string $version = '1.3.0';

		/**
		 * The single instance of the class.
		 *
		 * @var SUCCore|null
		 */
		protected static ?SUCCore $_instance = null;

		/**
		 * Snelstart Uphance Coupling Core.
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return SUCCore
		 */
		public static function instance(): SUCCore {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			$this->define_constants();
			$this->init_hooks();
			$this->actions_and_filters();
		}

		/**
		 * Initialise Snelstart Uphance Coupling Core.
		 */
		public function init() {
			$this->initialise_localisation();
			do_action( 'snelstart_uphance_coupling_init' );
		}

		/**
		 * Initialise the localisation of the plugin.
		 */
		private function initialise_localisation() {
			load_plugin_textdomain( 'snelstart-uphance-coupling', false, plugin_basename( dirname( SUC_PLUGIN_FILE ) ) . '/languages/' );
		}

		/**
		 * Define constants of the plugin.
		 */
		private function define_constants() {
			$this->define( 'SUC_ABSPATH', dirname( SUC_PLUGIN_FILE ) . '/' );
			$this->define( 'SUC_VERSION', $this->version );
			$this->define( 'SUC_FULLNAME', 'snelstart-uphance-coupling' );
		}

		/**
		 * Define if not already set.
		 *
		 * @param string $name the name.
		 * @param string $value the value.
		 */
		private static function define( string $name, string $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Initialise activation and deactivation hooks.
		 */
		private function init_hooks() {
			register_activation_hook( SUC_PLUGIN_FILE, array( $this, 'activation' ) );
			register_deactivation_hook( SUC_PLUGIN_FILE, array( $this, 'deactivation' ) );
		}

		/**
		 * Activation hook call.
		 */
		public function activation() {
			if ( ! wp_next_scheduled( 'suc_sync_all' ) ) {
				wp_schedule_event( time(), 'hourly', 'suc_sync_all' );
			}
			if ( ! wp_next_scheduled( 'suc_sync_daily_mail' ) ) {
				wp_schedule_event( time(), 'daily', 'suc_daily_mail' );
			}
		}

		/**
		 * Deactivation hook call.
		 */
		public function deactivation() {
			$timestamp = wp_next_scheduled( 'suc_sync_all' );
			wp_unschedule_event( $timestamp, 'suc_sync_all' );
			$timestamp = wp_next_scheduled( 'suc_daily_mail' );
			wp_unschedule_event( $timestamp, 'suc_daily_mail' );
		}

		/**
		 * Register the REST Routes with the plugin.
		 *
		 * @return void
		 */
		public function register_rest_routes() {
			include_once SUC_ABSPATH . '/includes/rest/SUCRestRoutes.php';
			include_once SUC_ABSPATH . '/includes/rest/SUCRetryRestRoute.php';
			include_once SUC_ABSPATH . '/includes/rest/uphance/SUCPickTicketRestRoute.php';
			include_once SUC_ABSPATH . '/includes/rest/uphance/SUCInvoiceRestRoute.php';
			include_once SUC_ABSPATH . '/includes/rest/uphance/SUCCreditNoteRestRoute.php';

			SUCRestRoutes::register_rest_route( 'retry', new SUCRetryRestRoute() );
			SUCRestRoutes::register_rest_route( 'uphance-pickticket', new SUCPickTicketRestRoute() );
			SUCRestRoutes::register_rest_route( 'uphance-invoice', new SUCInvoiceRestRoute() );
			SUCRestRoutes::register_rest_route( 'uphance-creditnote', new SUCCreditNoteRestRoute() );

			add_action( 'rest_api_init', array( 'SUCRestRoutes', 'register_rest_routes' ) );
		}

		/**
		 * Add actions and filters.
		 */
		private function actions_and_filters() {
			include_once SUC_ABSPATH . '/includes/class-sucsettings.php';
			include_once SUC_ABSPATH . '/includes/objects/SUCSynchronizedObjects.php';
			include_once SUC_ABSPATH . '/includes/objects/SUCObjectMapping.php';
			include_once SUC_ABSPATH . '/includes/suc-functions.php';
			include_once SUC_ABSPATH . '/includes/model/collect/collect.php';
			include_once SUC_ABSPATH . '/includes/synchronizers/class-sucsynchronizer.php';
			include_once SUC_ABSPATH . '/includes/synchronizers/class-succreditnotesynchronizer.php';
			include_once SUC_ABSPATH . '/includes/synchronizers/class-sucinvoicesynchronizer.php';
			include_once SUC_ABSPATH . '/includes/synchronizers/SUCPickTicketSynchronizer.php';
			include_once SUC_ABSPATH . '/includes/uphance/class-sucuphanceclient.php';
			include_once SUC_ABSPATH . '/includes/snelstart/class-sucsnelstartclient.php';
			include_once SUC_ABSPATH . '/includes/sendcloud/SUCSendcloudClient.php';

			SUCSettings::instance();
			$uphance_client = SUCUphanceClient::instance();
			$snelstart_client = SUCSnelstartClient::instance();
			$sendcloud_client = SUCSendcloudClient::instance();
			SUCSynchronizedObjects::init();
			SUCObjectMapping::init();
			add_action( 'suc_sync_all', 'cron_runner_sync_all' );
			add_action( 'suc_daily_mail', 'suc_send_daily_mail' );
			$this->register_rest_routes();

			if ( ! isset( $uphance_client ) || ! isset( $snelstart_client ) ) {
				/**
				 * Add admin notice that the plugin is not configured.
				 */
				function suc_admin_notice_plugin_not_configured_uphance_snelstart() {
					if ( is_admin() && current_user_can( 'edit_plugins' ) ) {
						echo '<div class="notice notice-error"><p>' . esc_html( __( 'Snelstart Uphance coupling requires Uphance and Snelstart settings to be configured in order to work.', 'snelstart-uphance-coupling' ) ) . '</p></div>';
					}
				}

				add_action( 'admin_notices', 'suc_admin_notice_plugin_not_configured_uphance_snelstart' );
			} else {
				SUCSynchronizer::register_synchronizer_class( SUCCreditNoteSynchronizer::$type, new SUCCreditNoteSynchronizer( $uphance_client, $snelstart_client ) );
				SUCSynchronizer::register_synchronizer_class( SUCInvoiceSynchronizer::$type, new SUCInvoiceSynchronizer( $uphance_client, $snelstart_client ) );
			}

			if ( ! isset( $uphance_client ) || ! isset( $sendcloud_client ) ) {
				/**
				 * Add admin notice that the plugin is not configured.
				 */
				function suc_admin_notice_plugin_not_configured_uphance_sendcloud() {
					if ( is_admin() && current_user_can( 'edit_plugins' ) ) {
						echo '<div class="notice notice-error"><p>' . esc_html( __( 'Snelstart Uphance coupling requires Uphance and Sendcloud settings to be configured in order to work.', 'snelstart-uphance-coupling' ) ) . '</p></div>';
					}
				}

				add_action( 'admin_notices', 'suc_admin_notice_plugin_not_configured_uphance_sendcloud' );
			} else {
				SUCSynchronizer::register_synchronizer_class( SUCPickTicketSynchronizer::$type, new SUCPickTicketSynchronizer( $uphance_client, $sendcloud_client ) );
			}
		}
	}
}
