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
		public string $version = '0.0.1';

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
		}

		/**
		 * Deactivation hook call.
		 */
		public function deactivation() {
		}

		/**
		 * Add actions and filters.
		 */
		private function actions_and_filters() {
			include_once SUC_ABSPATH . 'includes/api/v1/class-api-v1.php';
			include_once SUC_ABSPATH . '/includes/class-sucsettings.php';
			SUCSettings::instance();
			try {
				$suc_api_v1 = new SUCAPIV1();
				$suc_api_v1->define_rest_routes();
			}
			catch (Exception $e) {
				// TODO
			}
		}
	}
}
