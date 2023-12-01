<?php
/**
 * Settings configuration
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/conditions/class-fieldssetsettingscondition.php';
include_once SUC_ABSPATH . 'includes/suc-functions.php';

if ( ! function_exists( 'suc_get_settings_config' ) ) {
	/**
	 * Get the settings config.
	 *
	 * @return array The settings config.
	 */
	function suc_get_settings_config(): array {
		return array(
			'group_name' => 'suc_settings',
			'name' => 'suc_settings',
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
					'id'          => 'max_pick_tickets_to_synchronize',
					'name'        => __( 'Maximum amount of pick tickets to synchronize', 'snelstart-uphance-coupling' ),
					'default'     => 5,
					'can_be_null' => true,
					'hint'        => __( 'Maximum amount of pick tickets to synchronize per run (leave empty for all)', 'snelstart-uphance-coupling' ),
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
					'id'      => 'synchronize_pick_tickets_to_sendcloud',
					'name'    => __( 'Synchronize pick tickets to Sendcloud', 'snelstart-uphance-coupling' ),
					'default' => false,
					'hint'    => __( 'Whether to synchronize pick tickets from Uphance to Sendcloud', 'snelstart-uphance-coupling' ),
				),
				array(
					'type'    => 'text',
					'id'      => 'send_error_email_to',
					'name'    => __( 'Send admin emails to', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'    => __( 'Which email address to send the emails to when an error occurs, leave empty to not send emails.', 'snelstart-uphance-coupling' ),
				),
				array(
					'type'        => 'text',
					'id'          => 'snelstart_client_key',
					'name'        => __( 'Snelstart Client Key', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'The snelstart API client key', 'snelstart-uphance-coupling' ),
					'subscribers' => array( 'suc_reset_snelstart_token_on_settings_change' ),
				),
				array(
					'type'        => 'text',
					'id'          => 'snelstart_subscription_key',
					'name'        => __( 'Snelstart Subscription Key', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'The snelstart API subscription key', 'snelstart-uphance-coupling' ),
					'subscribers' => array( 'suc_reset_snelstart_token_on_settings_change' ),
				),
				array(
					'type'        => 'callable_choice',
					'id'          => 'snelstart_grootboekcode_debiteuren',
					'name'        => __( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'Snelstart Ledger code for Debiteuren.', 'snelstart-uphance-coupling' ),
					'callable'    => 'suc_get_grootboek_choices',
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'snelstart_client_key', 'snelstart_subscription_key' ) ),
					),
				),
				array(
					'type'        => 'callable_choice',
					'id'          => 'snelstart_grootboekcode_btw_hoog',
					'name'        => __( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'Snelstart Ledger code for BTW hoog.', 'snelstart-uphance-coupling' ),
					'callable'    => 'suc_get_grootboek_choices',
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'snelstart_client_key', 'snelstart_subscription_key' ) ),
					),
				),
				array(
					'type'        => 'callable_choice',
					'id'          => 'snelstart_grootboekcode_btw_geen',
					'name'        => __( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'Snelstart Ledger code for BTW geen.', 'snelstart-uphance-coupling' ),
					'callable'    => 'suc_get_grootboek_choices',
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'snelstart_client_key', 'snelstart_subscription_key' ) ),
					),
				),
				array(
					'type'        => 'callable_choice',
					'id'          => 'snelstart_grootboekcode_shipping_costs_btw_hoog',
					'name'        => __( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'Snelstart Ledger code for Shipping costs (BTW hoog).', 'snelstart-uphance-coupling' ),
					'callable'    => 'suc_get_grootboek_choices',
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'snelstart_client_key', 'snelstart_subscription_key' ) ),
					),
				),
				array(
					'type'        => 'callable_choice',
					'id'          => 'snelstart_grootboekcode_shipping_costs_btw_geen',
					'name'        => __( 'Snelstart Ledger code', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'Snelstart Ledger code for Shipping costs (BTW geen).', 'snelstart-uphance-coupling' ),
					'callable'    => 'suc_get_grootboek_choices',
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'snelstart_client_key', 'snelstart_subscription_key' ) ),
					),
				),
				array(
					'type'        => 'text',
					'id'          => 'sendcloud_public_key',
					'name'        => __( 'Sendcloud Public Key', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'The Sencdloud API public key', 'snelstart-uphance-coupling' ),
				),
				array(
					'type'        => 'text',
					'id'          => 'sendcloud_private_key',
					'name'        => __( 'Sendcloud Private Key', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'The Sendcloud API private key', 'snelstart-uphance-coupling' ),
				),
				array(
					'type'        => 'callable_choice',
					'id'          => 'sendcloud_shipping_method',
					'name'        => __( 'Sendcloud Shipping method', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'Default Sendcloud shipping method.', 'snelstart-uphance-coupling' ),
					'callable'    => 'suc_get_shipping_choices',
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'sendcloud_public_key', 'sendcloud_private_key' ) ),
					),
				),
				array(
					'type'        => 'text',
					'id'          => 'uphance_username',
					'name'        => __( 'Uphance username', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'The Uphance username to connect to the Uphance API', 'snelstart-uphance-coupling' ),
					'subscribers' => array( 'suc_reset_uphance_token_on_settings_change' ),
				),
				array(
					'type'        => 'password',
					'id'          => 'uphance_password',
					'name'        => __( 'Uphance password', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'The Uphance password to connect to the Uphance API', 'snelstart-uphance-coupling' ),
					'subscribers' => array( 'suc_reset_uphance_token_on_settings_change' ),
				),
				array(
					'type'        => 'callable_choice',
					'id'          => 'uphance_organisation',
					'name'        => __( 'Uphance Organisation', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'Uphance Organisation.', 'snelstart-uphance-coupling' ),
					'callable'    => 'suc_get_organisations_choices',
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'uphance_username', 'uphance_password' ) ),
					),
				),
				array(
					'type'        => 'positive_int',
					'id'          => 'uphance_synchronise_invoices_from',
					'name'        => __( 'Uphance Synchronise invoices from', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'Uphance Synchronise invoices from this invoice number onward.', 'snelstart-uphance-coupling' ),
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'uphance_username', 'uphance_password' ) ),
					),
				),
				array(
					'type'        => 'positive_int',
					'id'          => 'uphance_synchronise_credit_notes_from',
					'name'        => __( 'Uphance Synchronise credit notes from', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'Uphance Synchronise credit notes from this credit note number onward.', 'snelstart-uphance-coupling' ),
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'uphance_username', 'uphance_password' ) ),
					),
				),
				array(
					'type'        => 'positive_int',
					'id'          => 'uphance_synchronise_pick_tickets_from',
					'name'        => __( 'Uphance Synchronise pick tickets from', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'Uphance Synchronise pick tickets from this pick ticket number onward.', 'snelstart-uphance-coupling' ),
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'uphance_username', 'uphance_password' ) ),
					),
				),
				array(
					'type'        => 'text',
					'id'          => 'uphance_api_secret',
					'name'        => __( 'Uphance API secret', 'snelstart-uphance-coupling' ),
					'can_be_null' => true,
					'hint'        => __( 'The secret that Uphance should pass to the webhook API endpoints in order to authenticate towards the server. This should be a UUID.', 'snelstart-uphance-coupling' ),
					'conditions'  => array(
						new FieldsSetSettingsCondition( array( 'uphance_username', 'uphance_password' ) ),
					),
				),
			),
		);
	}
}

if ( ! function_exists( 'suc_get_settings_screen_config' ) ) {
	/**
	 * Get the settings screen config.
	 *
	 * @return array The settings screen config.
	 */
	function suc_get_settings_screen_config(): array {
		return array(
			'page_title'        => esc_html__( 'Snelstart Uphance Coupling', 'snelstart-uphance-coupling' ),
			'menu_title'        => esc_html__( 'Snelstart Uphance Coupling', 'snelstart-uphance-coupling' ),
			'capability_needed' => 'edit_plugins',
			'menu_slug'         => 'suc_admin_menu',
			'icon'              => 'dashicons-rest-api',
			'position'          => 56,
			'settings_pages' => array(
				array(
					'page_title'        => esc_html__( 'Snelstart Uphance Coupling Dashboard', 'snelstart-uphance-coupling' ),
					'menu_title'        => esc_html__( 'Dashboard', 'snelstart-uphance-coupling' ),
					'capability_needed' => 'edit_plugins',
					'menu_slug'         => 'suc_admin_menu',
					'renderer'          => function () {
						include_once SUC_ABSPATH . 'views/suc-admin-dashboard-view.php';
					},
					'settings_sections' => array(
						array(
							'id'       => 'global_settings',
							'name'     => __( 'Global settings', 'snelstart-uphance-coupling' ),
							'settings' => array(
								'max_invoices_to_synchronize',
								'max_credit_notes_to_synchronize',
								'max_pick_tickets_to_synchronize',
								'synchronize_invoices_to_snelstart',
								'synchronize_credit_notes_to_snelstart',
								'synchronize_pick_tickets_to_sendcloud',
								'send_error_email_to',
							),
						),
						array(
							'id' => 'snelstart_settings',
							'name'     => __( 'Snelstart settings', 'snelstart-uphance-coupling' ),
							'settings' => array(
								'snelstart_client_key',
								'snelstart_subscription_key',
								'snelstart_grootboekcode_debiteuren',
								'snelstart_grootboekcode_btw_hoog',
								'snelstart_grootboekcode_btw_geen',
								'snelstart_grootboekcode_shipping_costs_btw_hoog',
								'snelstart_grootboekcode_shipping_costs_btw_geen',
							),
						),
						array(
							'id' => 'uphance_settings',
							'name'     => __( 'Uphance settings', 'snelstart-uphance-coupling' ),
							'settings' => array(
								'uphance_username',
								'uphance_password',
								'uphance_organisation',
								'uphance_synchronise_invoices_from',
								'uphance_synchronise_credit_notes_from',
								'uphance_synchronise_pick_tickets_from',
								'uphance_api_secret',
							),
						),
						array(
							'id' => 'sendcloud_settings',
							'name' => __( 'Sendcloud settings', 'snelstart-uphance-coupling' ),
							'settings' => array(
								'sendcloud_public_key',
								'sendcloud_private_key',
								'sendcloud_shipping_method',
							),
						),
					),
				),
			),
		);
	}
}
