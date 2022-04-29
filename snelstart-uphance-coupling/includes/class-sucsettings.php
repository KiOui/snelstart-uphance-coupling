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

		/**
		 * Variable for storing the settings manager.
		 *
		 * @var SettingsManager
		 */
		private SettingsManager $manager;

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
			initialize_settings_fields();

			$settings_configuration = array(
				'group_name'   => 'suc_settings',
				'setting_name' => 'suc_settings',
				'page'         => array(
					'page_title'        => esc_html__( 'Snelstart Uphance Coupling', 'snelstart-uphance-coupling' ),
					'menu_title'        => esc_html__( 'Snelstart Uphance Coupling', 'snelstart-uphance-coupling' ),
					'capability_needed' => 'edit_plugins',
					'menu_slug'         => 'suc_admin_menu',
					'icon'              => 'dashicons-rest-api',
					'position'          => 56,
				),
				'menu_pages'   => array(
					array(
						'page_title'        => esc_html__( 'Snelstart Uphance Coupling Dashboard', 'snelstart-uphance-coupling' ),
						'menu_title'        => esc_html__( 'Dashboard', 'snelstart-uphance-coupling' ),
						'capability_needed' => 'edit_plugins',
						'menu_slug'         => 'suc_admin_menu',
						'renderer'          => array( $this, 'suc_admin_menu_dashboard_callback' ),
					),
				),
				'sections'     => array(
					array(
						'id'       => 'global_settings',
						'name'     => __( 'Global settings', 'snelstart-uphance-coupling' ),
						'settings' => array(
							array(
								'type'        => 'positive_int',
								'id'          => 'max_invoices_to_synchronize',
								'name'        => __( 'Maximum amount of invoices to synchronize', 'snelstart-uphance-coupling' ),
								'default'     => 5,
								'can_be_null' => true,
								'hint'        => __( 'Maximum amount of invoices to synchronize per run (leave empty for all)', 'snelstart-uphance-coupling' ),
								'maximum'     => null,
							),
							array(
								'type'        => 'positive_int',
								'id'          => 'max_credit_notes_to_synchronize',
								'name'        => __( 'Maximum amount of credit notes to synchronize', 'snelstart-uphance-coupling' ),
								'default'     => 5,
								'can_be_null' => true,
								'hint'        => __( 'Maximum amount of credit notes to synchronize per run (leave empty for all)', 'snelstart-uphance-coupling' ),
								'maximum'     => null,
							),
							array(
								'type'        => 'positive_int',
								'id'          => 'max_payments_to_synchronize',
								'name'        => __( 'Maximum amount of payments to synchronize', 'snelstart-uphance-coupling' ),
								'default'     => 5,
								'can_be_null' => true,
								'hint'        => __( 'Maximum amount of payments to synchronize per run (leave empty for all)', 'snelstart-uphance-coupling' ),
								'maximum'     => null,
							),
							array(
								'type'    => 'bool',
								'id'      => 'synchronize_invoices_to_snelstart',
								'name'    => __( 'Synchronize invoices to Snelstart', 'snelstart-uphance-coupling' ),
								'default' => false,
								'hint'    => __( 'Whether to synchronize invoices from Uphance to Snelstart', 'snelstart-uphance-coupling' ),
							),
							array(
								'type'    => 'bool',
								'id'      => 'synchronize_credit_notes_to_snelstart',
								'name'    => __( 'Synchronize credit notes to Snelstart', 'snelstart-uphance-coupling' ),
								'default' => false,
								'hint'    => __( 'Whether to synchronize credit notes from Uphance to Snelstart', 'snelstart-uphance-coupling' ),
							),
							array(
								'type'    => 'bool',
								'id'      => 'synchronize_payments_to_uphance',
								'name'    => __( 'Synchronize payments to Uphance', 'snelstart-uphance-coupling' ),
								'default' => false,
								'hint'    => __( 'Whether to synchronize payments from Snelstart to Uphance', 'snelstart-uphance-coupling' ),
							),
						),
					),
					array(
						'id'       => 'snelstart_settings',
						'name'     => __( 'Snelstart settings', 'snelstart-uphance-coupling' ),
						'settings' => array(
							array(
								'type'        => 'text',
								'id'          => 'snelstart_client_key',
								'name'        => __( 'Snelstart Client Key', 'snelstart-uphance-coupling' ),
								'can_be_null' => true,
								'hint'        => __( 'The snelstart API client key', 'snelstart-uphance-coupling' ),
							),
							array(
								'type'        => 'text',
								'id'          => 'snelstart_subscription_key',
								'name'        => __( 'Snelstart Subscription Key', 'snelstart-uphance-coupling' ),
								'can_be_null' => true,
								'hint'        => __( 'The snelstart API subscription key', 'snelstart-uphance-coupling' ),
							),
						),
					),
					array(
						'id'       => 'uphance_settings',
						'name'     => __( 'Uphance settings', 'snelstart-uphance-coupling' ),
						'settings' => array(
							array(
								'type'        => 'text',
								'id'          => 'uphance_username',
								'name'        => __( 'Uphance username', 'snelstart-uphance-coupling' ),
								'can_be_null' => true,
								'hint'        => __( 'The Uphance username to connect to the Uphance API', 'snelstart-uphance-coupling' ),
							),
							array(
								'type'        => 'password',
								'id'          => 'uphance_password',
								'name'        => __( 'Uphance password', 'snelstart-uphance-coupling' ),
								'can_be_null' => true,
								'hint'        => __( 'The Uphance password to connect to the Uphance API', 'snelstart-uphance-coupling' ),
							),
						),
					),
				),
			);

			$this->manager = SettingsFactory::create_settings( $settings_configuration );

			$snelstart_client = SUCSnelstartClient::instance();
			if ( isset( $snelstart_client ) ) {
				$this->manager->add_settings(
					'snelstart_settings',
					array(
						array(
							'type'        => 'choice',
							'id'          => 'snelstart_grootboekcode_debiteuren',
							'name'        => __( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
							'can_be_null' => true,
							'hint'        => __( 'Snelstart Ledger code for Debiteuren.', 'snelstart-uphance-coupling' ),
							'choices'     => array( $this, 'grootboekcodes_choices' ),
						),
						array(
							'type'        => 'choice',
							'id'          => 'snelstart_grootboekcode_btw_hoog',
							'name'        => __( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
							'can_be_null' => true,
							'hint'        => __( 'Snelstart Ledger code for BTW hoog.', 'snelstart-uphance-coupling' ),
							'choices'     => array( $this, 'grootboekcodes_choices' ),
						),
						array(
							'type'        => 'choice',
							'id'          => 'snelstart_grootboekcode_btw_geen',
							'name'        => __( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
							'can_be_null' => true,
							'hint'        => __( 'Snelstart Ledger code for BTW geen.', 'snelstart-uphance-coupling' ),
							'choices'     => array( $this, 'grootboekcodes_choices' ),
						),
						array(
							'type'        => 'datetime',
							'id'          => 'snelstart_synchronise_payments_from_date',
							'name'        => __( 'Snelstart Synchronise payments from modified at', 'snelstart-uphance-coupling' ),
							'can_be_null' => true,
							'hint'        => __( 'Snelstart Synchronise payments from this modified at date onward.', 'snelstart-uphance-coupling' ),
							'default'     => new DateTime( '@0' ),
						),
					)
				);
			}

			$uphance_client = SUCUphanceClient::instance();
			if ( isset( $uphance_client ) ) {
				$this->manager->add_settings(
					'uphance_settings',
					array(
						array(
							'type'        => 'choice',
							'id'          => 'uphance_organisation',
							'name'        => __( 'Uphance Organisation', 'snelstart-uphance-coupling' ),
							'can_be_null' => true,
							'hint'        => __( 'Uphance Organisation.', 'snelstart-uphance-coupling' ),
							'choices'     => array( $this, 'organisations_choices' ),
						),
						array(
							'type'        => 'positive_int',
							'id'          => 'uphance_synchronise_invoices_from',
							'name'        => __( 'Uphance Synchronise invoices from', 'snelstart-uphance-coupling' ),
							'can_be_null' => true,
							'hint'        => __( 'Uphance Synchronise invoices from this invoice number onward.', 'snelstart-uphance-coupling' ),
						),
						array(
							'type'        => 'positive_int',
							'id'          => 'uphance_synchronise_credit_notes_from',
							'name'        => __( 'Uphance Synchronise credit notes from', 'snelstart-uphance-coupling' ),
							'can_be_null' => true,
							'hint'        => __( 'Uphance Synchronise credit notes from this credit note number onward.', 'snelstart-uphance-coupling' ),
						),
					)
				);
			}

			$this->actions_and_filters();
		}

		/**
		 * Get the SettingsManager.
		 *
		 * @return SettingsManager
		 */
		public function get_manager(): SettingsManager {
			return $this->manager;
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
			add_action( 'current_screen', array( $this, 'do_custom_actions' ), 99 );
			$this->manager->actions_and_filters();
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
		 * Admin menu dashboard callback.
		 */
		public function suc_admin_menu_dashboard_callback() {
			include_once SUC_ABSPATH . 'views/suc-admin-dashboard-view.php';
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
