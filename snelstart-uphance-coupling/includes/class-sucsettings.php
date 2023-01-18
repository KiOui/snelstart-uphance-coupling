<?php
/**
 * Settings class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/uphance/class-sucuphanceclient.php';
include_once SUC_ABSPATH . 'includes/snelstart/class-sucsnelstartclient.php';
include_once SUC_ABSPATH . 'includes/suc-functions.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsfactory.php';

if ( ! class_exists( 'SUCSettings' ) ) {
	/**
	 * Snelstart Uphance Coupling Settings class.
	 *
	 * @class SUCSettings
	 */
	class SUCSettings {
		/**
		 * The single instance of the class.
		 *
		 * @var SUCSettings|null
		 */
		protected static ?SUCSettings $_instance = null;

		/**
		 * Variable for storing grootboeken.
		 *
		 * Null when not retrieved yet, array if this variable holds valid grootboeken, false if retrieving failed.
		 *
		 * @var ?mixed
		 */
		private $cached_grootboeken = null;

		/**
		 * Variable for storing invoices.
		 *
		 * Null when not retrieved yet, array if this variable holds valid invoices, false if retrieving failed.
		 *
		 * @var ?mixed
		 */
		private $cached_invoices = null;

		/**
		 * Variable for storing credit notes.
		 *
		 * Null when not retrieved yet, array if this variable holds valid credit notes, false if retrieving failed.
		 *
		 * @var ?mixed
		 */
		private $cached_credit_notes = null;

		/**
		 * Variable for storing organisations.
		 *
		 * Null when not retrieved yet, array if this variable holds valid credit notes, false if retrieving failed.
		 *
		 * @var ?mixed
		 */
		private $cached_organisations = null;

		private Settings $settings;
		private SettingsGroup $settings_group;

		/**
		 * Uses the Singleton pattern to load 1 instance of this class at maximum.
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
			include_once SUC_ABSPATH . 'includes/settings/settings-init.php';
			include_once SUC_ABSPATH . 'includes/suc-settings-config.php';
			initialize_settings_fields();

			$this->settings = SettingsFactory::create_settings( suc_get_settings_config() );
			$this->settings->initialize_settings();
			$this->settings_group = SettingsFactory::create_settings_group( suc_get_settings_screen_config() );

			$this->actions_and_filters();
		}

		/**
		 * @return Settings
		 */
		public function get_settings(): Settings {
			return $this->settings;
		}

		/**
		 * Add actions and filters.
		 */
		public function actions_and_filters() {
			add_action( 'admin_init', array( $this->settings, 'register' ) );
			add_action( 'admin_menu', array( $this, 'register_settings' ) );
			add_action( 'current_screen', array( $this, 'do_custom_actions' ), 99 );
		}

		public function register_settings() {
			$this->settings_group->register( $this->settings );
		}

		/**
		 * Execute custom actions.
		 */
		public function do_custom_actions() {
			if ( get_current_screen()->id === 'toplevel_page_suc_admin_menu' ) {
				if ( isset( $_GET['do_cron'] ) && 1 == $_GET['do_cron'] ) {
					cron_runner_sync_all();
					wp_redirect( '/wp-admin/admin.php?page=suc_admin_menu' );
					exit;
				} else if ( isset( $_POST['option_page'] ) && isset( $_POST['action'] ) && 'update' == $_POST['action'] && 'suc_settings' === $_POST['option_page'] && wp_verify_nonce( $_POST['_wpnonce'], 'suc_settings-options' ) ) {
					$this->settings->update_settings( $_POST );
					$this->settings->save_settings();
					wp_redirect( '/wp-admin/admin.php?page=suc_admin_menu' );
					exit;
				}
			} else {
				$uphance_client = SUCUphanceClient::instance();
				if ( ! is_null( $uphance_client ) ) {
					$uphance_client->reset_auth_token();
				}
				$snelstart_client = SUCSnelstartClient::instance();
				if ( ! is_null( $snelstart_client ) ) {
					$snelstart_client->reset_auth_token();
				}
			}
		}
	}
}
