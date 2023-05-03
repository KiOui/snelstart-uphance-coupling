<?php
/**
 * Snelstart Uphance Coupling Uphance API
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCUphanceRest' ) ) {
	/**
	 * Snelstart Uphance Coupling Uphance Rest
	 */
	class SUCUphanceRest {


		/**
		 * Add REST API endpoint.
		 *
		 * @return void
		 */
		public function add_rest_api_endpoints(): void {
			register_rest_route(
				'snelstart-uphance-coupling/v1',
				'/uphance/pick-ticket-creation',
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'manage_stock' ),
					'args'                => array(
						'pick_ticket'           => array(
							'required'          => true,
							'type'              => 'object',
							'validate_callback' => array( $this, 'validate_actie' ),
							'sanitize_callback' => array( $this, 'sanitize_actie' ),
						),
					),
					'permission_callback' => array( $this, 'check_permission' ),
				)
			);
		}

		/**
		 * Manage stock inventory for Automotive theme.
		 *
		 * @param WP_REST_Request $request The REST request.
		 *
		 * @return WP_REST_Response The response.
		 */
		public function manage_stock( WP_REST_Request $request ): WP_REST_Response {

			return rest_ensure_response( 0 );
		}

		/**
		 * Verify permissions for the API endpoint.
		 *
		 * @return bool Whether the request has the right HTTP permissions set.
		 */
		public function check_permission(): bool {
			if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) || ! isset( $_SERVER['PHP_AUTH_PW'] ) ) {
				return false;
			}

			$option   = get_option( 'autotelex_automotive_settings' );
			$username = $option['authentication_settings_username'];
			$password = $option['authentication_settings_password'];
			return $username === $_SERVER['PHP_AUTH_USER'] && $password === $_SERVER['PHP_AUTH_PW'];
		}
	}
}
