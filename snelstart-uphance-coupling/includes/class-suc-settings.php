<?php
/**
 * Settings class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/uphance/class-uphance-client.php';
include_once SUC_ABSPATH . 'includes/snelstart/class-snelstart-client.php';

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
				array( $this, 'snelstart_settings_callback' ),
				'suc_settings'
			);

            // TODO: Rename snelstart_key_settings to snelstart_settings
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

			$snelstart_client = SUCSnelstartClient::instance();
			if ( isset( $snelstart_client ) ) {
				add_settings_field(
					'snelstart_grootboekcode',
					__( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
					array( $this, 'snelstart_grootboekcode_renderer' ),
					'suc_settings',
					'snelstart_key_settings',
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
		 * @param $input
		 *
		 * @return array
		 */
		public function suc_settings_validate( $input ): array {
			$output['snelstart_client_key']     = $input['snelstart_client_key']; // TODO: Add sanitization
            $output['snelstart_subscription_key'] = $input['snelstart_subscription_key'];
            $output['uphance_username'] = $input['uphance_username'];
            $output['uphance_password'] = $input['uphance_password'];

            // TODO: Add sanitization for this setting
			$output['uphance_organisation'] =  $input['uphance_organisation'];
			$output['uphance_synchronise_invoices_from'] =  $input['uphance_synchronise_invoices_from'];

            $output['snelstart_grootboekcode'] =  $input['snelstart_grootboekcode'];

			return $output;
		}

		/**
		 * Render the section title of autotelex url settings.
		 */
		public function snelstart_settings_callback() {
			echo esc_html( __( 'Snelstart settings', 'snelstart-uphance-coupling' ) );
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
		public function uphance_settings_callback() {
			echo esc_html( __( 'Uphance settings', 'snelstart-uphance-coupling' ) );
		}

		/**
		 * Render Snelstart Client Key setting.
		 */
		public function uphance_username_renderer() {
			$options = get_option( 'suc_settings' ); ?>
            <p><?php echo esc_html( __( 'Uphance username (e-mail address)', 'snelstart-uphance-coupling' ) ); ?></p>
            <input type='text' name='suc_settings[uphance_username]'
                   value="<?php echo esc_attr( $options['uphance_username'] ); ?>">
			<?php
		}

		/**
		 * Render Snelstart Subscription key setting.
		 */
		public function uphance_password_renderer() {
			$options = get_option( 'suc_settings' ); ?>
            <p><?php echo esc_html( __( 'Uphance password', 'snelstart-uphance-coupling' ) ); ?></p>
            <input type='password' name='suc_settings[uphance_password]'
                   value="<?php echo esc_attr( $options['uphance_password'] ); ?>">
			<?php
		}

		/**
		 * Render Snelstart Subscription key setting.
		 */
		public function uphance_organisation_renderer() {
			?>
            <p><?php echo esc_html( __( 'Uphance organisation', 'snelstart-uphance-coupling' ) ); ?></p>
            <?php
			$options = get_option( 'suc_settings' );
            $selected_value = isset( $options['uphance_organisation'] ) && $options['uphance_organisation'] != "" ? $options['uphance_organisation'] : null;
            $uphance_client = SUCUphanceClient::instance();
            try {
                $selections = $uphance_client->organisations();
            } catch (SUCAPIException $e) {
                ?> <p class="notice notice-error"><?php echo esc_html( __( "There was a problem rendering the organisations, please make sure your uphance username and password are correct.", "snelstart-uphance-coupling" ) ); ?></p><?php
                return;
            }

            $selected_value_in_set = false;
            // TODO: __() the text below
            ?>
            <select name="suc_settings[uphance_organisation]">
                <option value="">----------</option>
                <?php foreach( $selections['organisations'] as $selection ) : ?>
                <option value="<?php echo esc_html( $selection["id"] ); ?>" <?php if ( $selection['id'] == $selected_value) { $selected_value_in_set = true; ?> selected <?php } ?>>
                    <?php echo esc_html( $selection["name"] ); ?>
                </option>
                <?php endforeach; ?>
                <?php if (! $selected_value_in_set && isset( $selected_value ) ) : ?>
                <option selected value="<?php echo esc_html( $selected_value ); ?>">Currently set organisation with ID <?php echo esc_html( $selected_value ); ?> (not in Uphance anymore)</option>
                <?php endif; ?>
            </select>
			<?php
		}

		/**
		 * Render Snelstart Subscription key setting.
		 */
		public function uphance_synchronise_invoices_from_renderer() {
			?>
            <p><?php echo esc_html( __( 'Uphance Synchronise invoices from this invoice number onward.', 'snelstart-uphance-coupling' ) ); ?></p>
			<?php
			$options = get_option( 'suc_settings' );
			$selected_value = isset( $options['uphance_synchronise_invoices_from'] ) && $options['uphance_synchronise_invoices_from'] != "" ? $options['uphance_synchronise_invoices_from'] : null;
			$uphance_client = SUCUphanceClient::instance();
			try {
				$selections = $uphance_client->invoices();
			} catch (SUCAPIException $e) {
				?> <p class="notice notice-error"><?php echo esc_html( __( "There was a problem rendering the invoices, please make sure your uphance username and password are correct.", "snelstart-uphance-coupling" ) ); ?></p><?php
				return;
			}

			$selected_value_in_set = false;
			// TODO: __() the text below
			?>
            <select name="suc_settings[uphance_synchronise_invoices_from]">
                <option value="">----------</option>
				<?php foreach( $selections->result['invoices'] as $selection ) : ?>
                    <option value="<?php echo esc_html( $selection["id"] ); ?>" <?php if ( $selection['id'] == $selected_value) { $selected_value_in_set = true; ?> selected <?php } ?>>
						<?php echo esc_html( $selection["invoice_number"] ); ?>
                    </option>
				<?php endforeach; ?>
				<?php if (! $selected_value_in_set && isset( $selected_value ) ) : ?>
                    <option selected value="<?php echo esc_html( $selected_value ); ?>">Currently set invoice with ID <?php echo esc_html( $selected_value ); ?> (not in Uphance anymore)</option>
				<?php endif; ?>
            </select>
			<?php
		}

		/**
		 * Render Snelstart Subscription key setting.
		 */
		public function snelstart_grootboekcode_renderer() {
			?>
            <p><?php echo esc_html( __( 'Snelstart Ledger code (code to book all invoices to).', 'snelstart-uphance-coupling' ) ); ?></p>
			<?php
			$options = get_option( 'suc_settings' );
			$selected_value = isset( $options['snelstart_grootboekcode'] ) && $options['snelstart_grootboekcode'] != "" ? $options['snelstart_grootboekcode'] : null;
			$snelstart_client = SUCSnelstartClient::instance();
			try {
				$selections = $snelstart_client->grootboeken();
			} catch (SUCAPIException $e) {
				?> <p class="notice notice-error"><?php echo esc_html( __( "There was a problem rendering the Ledger codes, please make sure your snelstart key settings are correct.", "snelstart-uphance-coupling" ) ); ?></p><?php
				return;
			}

			$selected_value_in_set = false;
			// TODO: __() the text below
			?>
            <select name="suc_settings[snelstart_grootboekcode]">
                <option value="">----------</option>
				<?php foreach( $selections as $selection ) : ?>
                    <option value="<?php echo esc_html( $selection["id"] ); ?>" <?php if ( $selection['id'] == $selected_value) { $selected_value_in_set = true; ?> selected <?php } ?>>
						<?php echo esc_html( $selection["nummer"] . " (" . $selection["omschrijving"] . ", " . $selection["rekeningCode"] . ")" ); ?>
                    </option>
				<?php endforeach; ?>
				<?php if (! $selected_value_in_set && isset( $selected_value ) ) : ?>
                    <option selected value="<?php echo esc_html( $selected_value ); ?>">Currently set ledger code with ID <?php echo esc_html( $selected_value ); ?> (not in Snelstart anymore)</option>
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
	}
}
