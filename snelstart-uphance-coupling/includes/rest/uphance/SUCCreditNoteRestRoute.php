<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . '/includes/rest/SUCRestRoute.php';
include_once SUC_ABSPATH . 'includes/class-sucsettings.php';

if ( ! class_exists( 'SUCCreditNoteRestRoute' ) ) {
	/**
	 * Pick Ticket REST Route
	 *
	 * @class SUCCreditNoteRestRoute
	 */
	class SUCCreditNoteRestRoute extends SUCRestRoute {

		/**
		 * Add the REST API endpoint.
		 *
		 * @return void
		 */
		public function add_rest_api_endpoints(): void {
			register_rest_route(
				'snelstart-uphance-coupling/v1',
				'/uphance/credit-note',
				array(
					'methods' => 'POST',
					'callback' => array( $this, 'synchronize_credit_note_to_snelstart' ),
					'args' => array(),
					'permission_callback' => array( $this, 'check_permissions' ),
				)
			);
		}

		/**
		 * Try to synchronize a credit note to Snelstart.
		 *
		 * @param WP_REST_Request $request The REST API request.
		 *
		 * @return WP_REST_Response A REST response with a failed or succeeded status code.
		 */
		public function synchronize_credit_note_to_snelstart( WP_REST_Request $request ): WP_REST_Response {
			return new WP_REST_Response();
		}

		/**
		 * Check whether user can access the REST API endpoint.
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return bool Whether a user can access this REST API endpoint.
		 * @throws SettingsConfigurationException When settings were not configured correctly.
		 */
		public function check_permissions( WP_REST_Request $request ): bool {
			$manager          = SUCSettings::instance()->get_settings();
			$api_secret = $manager->get_value( 'uphance_api_secret' );

			if ( ! $api_secret ) {
				return true;
			}

			if ( ! isset( $request->get_url_params()['secret'] ) ) {
				return false;
			}

			return strval( $request->get_url_params()['secret'] ) === $api_secret;
		}
	}
}
