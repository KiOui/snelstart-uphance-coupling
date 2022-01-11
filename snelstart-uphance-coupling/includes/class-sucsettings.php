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
		 * @var mixed
		 */
		private mixed $cached_grootboeken = null;

		/**
		 * Variable for storing invoices.
		 *
		 * Null when not retrieved yet, array if this variable holds valid invoices, false if retrieving failed.
		 *
		 * @var mixed
		 */
		private mixed $cached_invoices = null;

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
			$this->actions_and_filters();
		}

		/**
		 * Add actions and filters.
		 */
		public function actions_and_filters() {
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 99 );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'current_screen', array( $this, 'do_custom_actions' ), 99 );
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
		 * Add Snelstart Uphance Coupling Settings menu page.
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
		 * Register Snelstart Uphance Coupling Settings.
		 */
		public function register_settings() {
			register_setting(
				'suc_settings',
				'suc_settings',
				array( $this, 'suc_settings_validate' )
			);

			add_settings_section(
				'global_settings',
				__( 'Global settings', 'snelstart-uphance-coupling' ),
				array( $this, 'global_settings_callback' ),
				'suc_settings'
			);

			add_settings_field(
				'max_invoices_to_synchronize',
				__( 'Maximum amount of invoices to synchronize', 'snelstart-uphance-coupling' ),
				array( $this, 'max_invoices_to_synchronize_callback' ),
				'suc_settings',
				'global_settings'
			);

            add_settings_field(
                'synchronize_invoices_to_snelstart',
                __( 'Whether to synchronize invoices from Uphance to Snelstart', 'snelstart-uphance-coupling' ),
                array( $this, 'synchronize_invoices_to_snelstart_callback' ),
                'suc_settings',
                'global_settings'

            );

            add_settings_field(
                'synchronize_payments_to_uphance',
                __( 'Whether to synchronize payments from Snelstart to Uphance', 'snelstart-uphance-coupling' ),
                array( $this, 'synchronize_payments_to_uphance_callback' ),
                'suc_settings',
                'global_settings'
            );

			add_settings_section(
				'snelstart_settings',
				__( 'Snelstart Key settings', 'snelstart-uphance-coupling' ),
				array( $this, 'snelstart_settings_callback' ),
				'suc_settings'
			);

			add_settings_field(
				'snelstart_client_key',
				__( 'Snelstart Client Key', 'snelstart-uphance-coupling' ),
				array( $this, 'snelstart_client_key_renderer' ),
				'suc_settings',
				'snelstart_settings'
			);

			add_settings_field(
				'snelstart_subscription_key',
				__( 'Snelstart Subscription Key', 'snelstart-uphance-coupling' ),
				array( $this, 'snelstart_subscription_key_renderer' ),
				'suc_settings',
				'snelstart_settings'
			);

			$snelstart_client = SUCSnelstartClient::instance();
			if ( isset( $snelstart_client ) ) {
				add_settings_field(
					'snelstart_grootboekcode_debiteuren',
					__( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
					array( $this, 'snelstart_grootboekcode_debiteuren_renderer' ),
					'suc_settings',
					'snelstart_settings',
				);

				add_settings_field(
					'snelstart_grootboekcode_btw_hoog',
					__( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
					array( $this, 'snelstart_grootboekcode_btw_hoog_renderer' ),
					'suc_settings',
					'snelstart_settings',
				);

				add_settings_field(
					'snelstart_grootboekcode_btw_geen',
					__( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
					array( $this, 'snelstart_grootboekcode_btw_geen_renderer' ),
					'suc_settings',
					'snelstart_settings',
				);
			}

			add_settings_section(
				'uphance_settings',
				__( 'Uphance settings', 'snelstart-uphance-coupling' ),
				array( $this, 'uphance_settings_callback' ),
				'suc_settings'
			);

			add_settings_field(
				'uphance_username',
				__( 'Uphance username', 'snelstart-uphance-coupling' ),
				array( $this, 'uphance_username_renderer' ),
				'suc_settings',
				'uphance_settings'
			);

			add_settings_field(
				'uphance_password',
				__( 'Uphance password', 'snelstart-uphance-coupling' ),
				array( $this, 'uphance_password_renderer' ),
				'suc_settings',
				'uphance_settings'
			);

			$uphance_client = SUCUphanceClient::instance();
			if ( isset( $uphance_client ) ) {
				add_settings_field(
					'uphance_organisation',
					__( 'Uphance Organisation', 'snelstart-uphance-coupling' ),
					array( $this, 'uphance_organisation_renderer' ),
					'suc_settings',
					'uphance_settings',
				);

				add_settings_field(
					'uphance_synchronise_invoices_from',
					__( 'Uphance Synchronise invoices from', 'snelstart-uphance-coupling' ),
					array( $this, 'uphance_synchronise_invoices_from_renderer' ),
					'suc_settings',
					'uphance_settings',
				);
			}
		}

		/**
		 * Validate Snelstart Uphance Coupling settings.
		 *
		 * @param array $input input settings.
		 *
		 * @return array output settings.
		 */
		public function suc_settings_validate( array $input ): array {
			$output['max_invoices_to_synchronize'] = absint( $input['max_invoices_to_synchronize'] );

			$output['snelstart_client_key']     = esc_attr( $input['snelstart_client_key'] );
			$output['snelstart_subscription_key'] = esc_attr( $input['snelstart_subscription_key'] );
			$output['uphance_username'] = sanitize_email( $input['uphance_username'] );
			$output['uphance_password'] = $input['uphance_password'];

			$output['uphance_organisation'] = '' !== $input['uphance_organisation'] ? absint( $input['uphance_organisation'] ) : '';
			$output['uphance_synchronise_invoices_from'] = '' !== $input['uphance_synchronise_invoices_from'] ? absint( $input['uphance_synchronise_invoices_from'] ) : '';

            $output['snelstart_grootboekcode_debiteuren'] = $input['snelstart_grootboekcode_debiteuren'];
			$output['snelstart_grootboekcode_btw_hoog'] = $input['snelstart_grootboekcode_btw_hoog'];
			$output['snelstart_grootboekcode_btw_geen'] = $input['snelstart_grootboekcode_btw_geen'];

            $output['synchronize_invoices_to_snelstart'] = suc_sanitize_boolean_default_false( $input['synchronize_invoices_to_snelstart'] );
            $output['synchronize_payments_to_uphance'] = suc_sanitize_boolean_default_false( $input['synchronize_payments_to_uphance'] );

			return $output;
		}

		/**
		 * Render the global settings section title.
		 */
		public function global_settings_callback() {
			echo esc_html( __( 'Global settings', 'snelstart-uphance-coupling' ) );
		}

		/**
		 * Render max invoices to synchronize setting.
		 */
		public function max_invoices_to_synchronize_callback() {
			$options = get_option( 'suc_settings' ); ?>
			<p><?php echo esc_html( __( 'Maximum amount of invoices to synchronize per run (leave empty for all)', 'snelstart-uphance-coupling' ) ); ?></p>
			<input type='number' name='suc_settings[max_invoices_to_synchronize]'
				   value="<?php echo esc_attr( $options['max_invoices_to_synchronize'] ); ?>">
			<?php
		}

		/**
		 * Render invoices to snelstart setting.
		 */
		public function synchronize_invoices_to_snelstart_callback() {
			$options = get_option( 'suc_settings' ); ?>
            <input type='checkbox' name='suc_settings[synchronize_invoices_to_snelstart]' <?php checked( $options['synchronize_invoices_to_snelstart'], 1 ); ?> value='1'>
			<?php
		}

		/**
		 * Render payments to uphance setting.
		 */
        public function synchronize_payments_to_uphance_callback() {
	        $options = get_option( 'suc_settings' ); ?>
            <input type='checkbox' name='suc_settings[synchronize_payments_to_uphance]' <?php checked( $options['synchronize_payments_to_uphance'], 1 ); ?> value='1'>
	        <?php
        }

		/**
		 * Render the Snelstart settings section title
		 */
		public function snelstart_settings_callback() {
			echo esc_html( __( 'Snelstart settings', 'snelstart-uphance-coupling' ) );
		}

		/**
		 * Render Snelstart client key setting.
		 */
		public function snelstart_client_key_renderer() {
			$options = get_option( 'suc_settings' );
			?>
			<p><?php echo esc_html( __( 'The snelstart API client key', 'snelstart-uphance-coupling' ) ); ?></p>
			<input type='text' name='suc_settings[snelstart_client_key]'
				   value="<?php echo esc_attr( $options['snelstart_client_key'] ); ?>">
			<?php
		}

		/**
		 * Render Snelstart subscription key setting.
		 */
		public function snelstart_subscription_key_renderer() {
			$options = get_option( 'suc_settings' );
			?>
			<p><?php echo esc_html( __( 'The snelstart API subscription key', 'snelstart-uphance-coupling' ) ); ?></p>
			<input type='text' name='suc_settings[snelstart_subscription_key]'
				   value="<?php echo esc_attr( $options['snelstart_subscription_key'] ); ?>">
			<?php
		}

		/**
		 * Render the Uphance settings section title.
		 */
		public function uphance_settings_callback() {
			echo esc_html( __( 'Uphance settings', 'snelstart-uphance-coupling' ) );
		}

		/**
		 * Render Uphance username setting.
		 */
		public function uphance_username_renderer() {
			$options = get_option( 'suc_settings' );
			?>
			<p><?php echo esc_html( __( 'Uphance username (e-mail address)', 'snelstart-uphance-coupling' ) ); ?></p>
			<input type='text' name='suc_settings[uphance_username]'
				   value="<?php echo esc_attr( $options['uphance_username'] ); ?>">
			<?php
		}

		/**
		 * Render Uphance password setting.
		 */
		public function uphance_password_renderer() {
			$options = get_option( 'suc_settings' );
			?>
			<p><?php echo esc_html( __( 'Uphance password', 'snelstart-uphance-coupling' ) ); ?></p>
			<input type='password' name='suc_settings[uphance_password]'
				   value="<?php echo esc_attr( $options['uphance_password'] ); ?>">
			<?php
		}

		/**
		 * Render Uphance organisation setting.
		 */
		public function uphance_organisation_renderer() {
			?>
			<p><?php echo esc_html( __( 'Uphance organisation', 'snelstart-uphance-coupling' ) ); ?></p>
			<?php
			$options = get_option( 'suc_settings' );
			$selected_value = isset( $options['uphance_organisation'] ) && '' != $options['uphance_organisation'] ? $options['uphance_organisation'] : null;
			$uphance_client = SUCUphanceClient::instance();
			try {
				$selections = $uphance_client->organisations();
			} catch ( SUCAPIException $e ) {
				?>
				 <p class="notice notice-error"><?php echo esc_html( __( 'There was a problem rendering the organisations, please make sure your uphance username and password are correct.', 'snelstart-uphance-coupling' ) ); ?></p>
				<?php
				return;
			}

			$selected_value_in_set = false;
			?>
			<select name="suc_settings[uphance_organisation]">
				<option value="">----------</option>
				<?php foreach ( $selections['organisations'] as $selection ) : ?>
				<option value="<?php echo esc_html( $selection['id'] ); ?>" 
										  <?php
											if ( $selection['id'] == $selected_value ) {
												$selected_value_in_set = true;
												?>
					 selected <?php } ?>>
					<?php echo esc_html( $selection['name'] ); ?>
				</option>
				<?php endforeach; ?>
				<?php if ( ! $selected_value_in_set && isset( $selected_value ) ) : ?>
				<option selected value="<?php echo esc_html( $selected_value ); ?>"><?php echo esc_html( sprintf( __( 'Currently set organisation with ID %s (not in Uphance anymore)', 'snelstart-uphance-coupling' ), $selected_value ) ); ?></option>
				<?php endif; ?>
			</select>
			<?php
		}

		/**
		 * Render Uphance synchronise invoices from setting.
		 */
		public function uphance_synchronise_invoices_from_renderer() {
			?>
			<p><?php echo esc_html( __( 'Uphance Synchronise invoices from this invoice number onward.', 'snelstart-uphance-coupling' ) ); ?></p>
			<?php
			$options = get_option( 'suc_settings' );
			$selected_value = isset( $options['uphance_synchronise_invoices_from'] ) && '' != $options['uphance_synchronise_invoices_from'] ? $options['uphance_synchronise_invoices_from'] : null;
			$selections = $this->get_invoices();
			if ( ! isset( $selections ) ) {
				?>
				 <p class="notice notice-error"><?php echo esc_html( __( 'There was a problem rendering the invoices, please make sure your uphance username and password are correct.', 'snelstart-uphance-coupling' ) ); ?></p>
				<?php
				return;
			}

			$selected_value_in_set = false;
			?>
			<select name="suc_settings[uphance_synchronise_invoices_from]">
				<option value="">----------</option>
				<?php foreach ( $selections['invoices'] as $selection ) : ?>
					<option value="<?php echo esc_html( $selection['id'] ); ?>" 
											  <?php
												if ( $selection['id'] == $selected_value ) {
													$selected_value_in_set = true;
													?>
						 selected <?php } ?>>
						<?php echo esc_html( $selection['invoice_number'] ); ?>
					</option>
				<?php endforeach; ?>
				<?php if ( ! $selected_value_in_set && isset( $selected_value ) ) : ?>
					<option selected value="<?php echo esc_html( $selected_value ); ?>"><?php echo esc_html( sprintf( __( 'Currently set invoice with ID %s (not in Uphance anymore)', 'snelstart-uphance-coupling' ), $selected_value ) ); ?></option>
				<?php endif; ?>
			</select>
			<?php
		}

		/**
		 * Render Snelstart grootboekcode btw hoog setting.
		 */
		public function snelstart_grootboekcode_btw_hoog_renderer() {
			?>
			<p><?php echo esc_html( __( 'Snelstart Ledger code for BTW hoog.', 'snelstart-uphance-coupling' ) ); ?></p>
			<?php
			$options = get_option( 'suc_settings' );
			$selected_value = isset( $options['snelstart_grootboekcode_btw_hoog'] ) && '' != $options['snelstart_grootboekcode_btw_hoog'] ? $options['snelstart_grootboekcode_btw_hoog'] : null;
			$selections = $this->get_grootboeken();
			if ( ! isset( $selections ) ) {
				?>
				 <p class="notice notice-error"><?php echo esc_html( __( 'There was a problem rendering the Ledger codes, please make sure your snelstart key settings are correct.', 'snelstart-uphance-coupling' ) ); ?></p>
				<?php
				return;
			}

			$selected_value_in_set = false;
			?>
			<select name="suc_settings[snelstart_grootboekcode_btw_hoog]">
				<option value="">----------</option>
				<?php foreach ( $selections as $selection ) : ?>
					<option value="<?php echo esc_html( $selection['id'] ); ?>" 
											  <?php
												if ( $selection['id'] == $selected_value ) {
													$selected_value_in_set = true;
													?>
						 selected <?php } ?>>
						<?php echo esc_html( $selection['nummer'] . ' (' . $selection['omschrijving'] . ', ' . $selection['rekeningCode'] . ')' ); ?>
					</option>
				<?php endforeach; ?>
				<?php if ( ! $selected_value_in_set && isset( $selected_value ) ) : ?>
					<option selected value="<?php echo esc_html( $selected_value ); ?>"><?php echo esc_html( sprintf( __( 'Currently set ledger code with ID %s (not in Snelstart anymore)', 'snelstart-uphance-coupling' ), $selected_value ) ); ?></option>
				<?php endif; ?>
			</select>
			<?php
		}

		/**
		 * Render Snelstart grootboekcode debiteuren setting.
		 */
		public function snelstart_grootboekcode_debiteuren_renderer() {
			?>
            <p><?php echo esc_html( __( 'Snelstart Ledger code for Debiteuren.', 'snelstart-uphance-coupling' ) ); ?></p>
			<?php
			$options = get_option( 'suc_settings' );
			$selected_value = isset( $options['snelstart_grootboekcode_debiteuren'] ) && '' != $options['snelstart_grootboekcode_debiteuren'] ? $options['snelstart_grootboekcode_debiteuren'] : null;
			$selections = $this->get_grootboeken();
			if ( ! isset( $selections ) ) {
				?>
                <p class="notice notice-error"><?php echo esc_html( __( 'There was a problem rendering the Ledger codes, please make sure your snelstart key settings are correct.', 'snelstart-uphance-coupling' ) ); ?></p>
				<?php
				return;
			}

			$selected_value_in_set = false;
			?>
            <select name="suc_settings[snelstart_grootboekcode_debiteuren]">
                <option value="">----------</option>
				<?php foreach ( $selections as $selection ) : ?>
                    <option value="<?php echo esc_html( $selection['id'] ); ?>"
						<?php
						if ( $selection['id'] == $selected_value ) {
							$selected_value_in_set = true;
							?>
                            selected <?php } ?>>
						<?php echo esc_html( $selection['nummer'] . ' (' . $selection['omschrijving'] . ', ' . $selection['rekeningCode'] . ')' ); ?>
                    </option>
				<?php endforeach; ?>
				<?php if ( ! $selected_value_in_set && isset( $selected_value ) ) : ?>
                    <option selected value="<?php echo esc_html( $selected_value ); ?>"><?php echo esc_html( sprintf( __( 'Currently set ledger code with ID %s (not in Snelstart anymore)', 'snelstart-uphance-coupling' ), $selected_value ) ); ?></option>
				<?php endif; ?>
            </select>
			<?php
		}

		/**
		 * Render Snelstart grootboekcode btw geen setting.
		 */
		public function snelstart_grootboekcode_btw_geen_renderer() {
			?>
			<p><?php echo esc_html( __( 'Snelstart Ledger code for BTW geen.', 'snelstart-uphance-coupling' ) ); ?></p>
			<?php
			$options = get_option( 'suc_settings' );
			$selected_value = isset( $options['snelstart_grootboekcode_btw_geen'] ) && '' != $options['snelstart_grootboekcode_btw_geen'] ? $options['snelstart_grootboekcode_btw_geen'] : null;
			$selections = $this->get_grootboeken();
			if ( ! isset( $selections ) ) {
				?>
				 <p class="notice notice-error"><?php echo esc_html( __( 'There was a problem rendering the Ledger codes, please make sure your snelstart key settings are correct.', 'snelstart-uphance-coupling' ) ); ?></p>
				<?php
				return;
			}

			$selected_value_in_set = false;
			?>
			<select name="suc_settings[snelstart_grootboekcode_btw_geen]">
				<option value="">----------</option>
				<?php foreach ( $selections as $selection ) : ?>
					<option value="<?php echo esc_html( $selection['id'] ); ?>" 
											  <?php
												if ( $selection['id'] == $selected_value ) {
													$selected_value_in_set = true;
													?>
						 selected <?php } ?>>
						<?php echo esc_html( $selection['nummer'] . ' (' . $selection['omschrijving'] . ', ' . $selection['rekeningCode'] . ')' ); ?>
					</option>
				<?php endforeach; ?>
				<?php if ( ! $selected_value_in_set && isset( $selected_value ) ) : ?>
					<option selected value="<?php echo esc_html( $selected_value ); ?>"><?php echo esc_html( sprintf( __( 'Currently set ledger code with ID %s (not in Snelstart anymore)', 'snelstart-uphance-coupling' ), $selected_value ) ); ?></option>
				<?php endif; ?>
			</select>
			<?php
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
	}
}
