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
			$this->settings_group = SettingsFactory::create_settings_group( suc_get_settings_screen_config() );

			$this->actions_and_filters();
		}

		/**
		 * Get (cached) Grootboek codes.
		 *
		 * @return array|false false when loading failed, an array of Grootboek codes otherwise.
		 */
		public function grootboekcodes_choices() {
			$grootboeken = $this->get_grootboeken();
			if ( false === $grootboeken ) {
				return false;
			}
			$retvalue = array();
			foreach ( $grootboeken as $grootboek ) {
				$retvalue[ strval( $grootboek['id'] ) ] = $grootboek['nummer'] . ' (' . $grootboek['omschrijving'] . ', ' . $grootboek['rekeningCode'] . ')';
			}

			return $retvalue;
		}

		/**
		 * Get (cached) Organisations.
		 *
		 * @return array|false false when loading failed, an array of Organisations otherwise.
		 */
		public function organisations_choices() {
			$organisations = $this->get_organisations();
			if ( false === $organisations ) {
				return false;
			}
			$retvalue = array();
			foreach ( $organisations['organisations'] as $organisation ) {
				$retvalue[ strval( $organisation['id'] ) ] = $organisation['name'];
			}

			return $retvalue;
		}

		/**
		 * Get (cached) Credit notes.
		 *
		 * @return array|false false when loading failed, an array of Credit notes otherwise.
		 */
		public function credit_notes_choices() {
			$credit_notes = $this->get_credit_notes();
			if ( false === $credit_notes ) {
				return false;
			}
			$retvalue = array();
			foreach ( $credit_notes['credit_notes'] as $credit_note ) {
				$retvalue[ $credit_note['id'] ] = $credit_note['credit_note_number'];
			}

			return $retvalue;
		}

		/**
		 * Get (cached) Invoices.
		 *
		 * @return array|false false when loading failed, an array of Invoices otherwise.
		 */
		public function invoices_choices() {
			$invoices = $this->get_invoices();
			if ( false === $invoices ) {
				return false;
			}
			$retvalue = array();
			foreach ( $invoices['invoices'] as $invoice ) {
				$retvalue[ $invoice['id'] ] = $invoice['invoice_number'];
			}

			return $retvalue;
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
			$this->settings->initialize_settings();
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
				} else if ( isset( $_POST['do_save'] ) && 1 == $_POST['do_save'] && wp_verify_nonce( '_wpnonce' ) ) {

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

		/**
		 * Get grootboeken and set cache.
		 *
		 * @return array|null the grootboeken (array) if succeeded, null otherwise.
		 */
		private function get_grootboeken(): ?array {
			if ( isset( $this->cached_grootboeken ) ) {
				if ( false === $this->cached_grootboeken ) {
					return null;
				} else {
					return $this->cached_grootboeken;
				}
			} else {
				$snelstart_client = SUCSnelstartClient::instance();
				if ( isset( $snelstart_client ) ) {
					try {
						$this->cached_grootboeken = $snelstart_client->grootboeken();

						return $this->cached_grootboeken;
					} catch ( SUCAPIException $e ) {
						$this->cached_grootboeken = false;

						return null;
					}
				} else {
					$this->cached_grootboeken = false;

					return null;
				}
			}
		}

		/**
		 * Get invoices and set cache.
		 *
		 * @return array|null the invoices (array) if succeeded, null otherwise.
		 */
		private function get_invoices(): ?array {
			if ( isset( $this->cached_invoices ) ) {
				if ( false === $this->cached_invoices ) {
					return null;
				} else {
					return $this->cached_invoices;
				}
			} else {
				$uphance_client = SUCUphanceClient::instance();
				if ( isset( $uphance_client ) ) {
					try {
						$this->cached_invoices = $uphance_client->invoices()->result;

						return $this->cached_invoices;
					} catch ( SUCAPIException $e ) {
						$this->cached_invoices = false;

						return null;
					}
				} else {
					$this->cached_invoices = false;

					return null;
				}
			}
		}

		/**
		 * Get invoices and set cache.
		 *
		 * @return array|null the invoices (array) if succeeded, null otherwise.
		 */
		private function get_credit_notes(): ?array {
			if ( isset( $this->cached_credit_notes ) ) {
				if ( false === $this->cached_credit_notes ) {
					return null;
				} else {
					return $this->cached_credit_notes;
				}
			} else {
				$uphance_client = SUCUphanceClient::instance();
				if ( isset( $uphance_client ) ) {
					try {
						$this->cached_credit_notes = $uphance_client->credit_notes()->result;

						return $this->cached_credit_notes;
					} catch ( SUCAPIException $e ) {
						$this->cached_credit_notes = false;

						return null;
					}
				} else {
					$this->cached_credit_notes = false;

					return null;
				}
			}
		}


		/**
		 * Get organisations and set cache.
		 *
		 * @return array|null the organisations (array) if succeeded, null otherwise.
		 */
		private function get_organisations(): ?array {
			if ( isset( $this->cached_organisations ) ) {
				if ( false === $this->cached_organisations ) {
					return null;
				} else {
					return $this->cached_organisations;
				}
			} else {
				$uphance_client = SUCUphanceClient::instance();
				if ( isset( $uphance_client ) ) {
					try {
						$this->cached_organisations = $uphance_client->organisations();

						return $this->cached_organisations;
					} catch ( SUCAPIException $e ) {
						$this->cached_organisations = false;
						return null;
					}
				} else {
					$this->cached_organisations = false;

					return null;
				}
			}
		}
	}
}
