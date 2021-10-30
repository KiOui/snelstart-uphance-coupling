<?php
/**
 * Core class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/snelstart/class-snelstart-client.php';


if ( ! class_exists( 'SUCAPIV1' ) ) {
	/**
	 * Snelstart Uphance Coupling API v1 class
	 *
	 * @class SUCAPIV1
	 */
	class SUCAPIV1 {

		/**
		 * @var SUCSnelstartClient
		 */
		private SUCSnelstartClient $snelstart_client;

		/**
		 * @throws Exception
		 */
		public function __construct() {
			$settings = get_option( 'suc_settings', null );
			if ( ! isset( $settings ) ) {
				throw new Exception( 'Subscription key and Snelstart client key not set.' );
			}
			$snelstart_key = $settings['snelstart_client_key'];
			$subscription_key = $settings['snelstart_subscription_key'];

			if ( ! isset( $subscription_key ) ) {
				throw new Exception( 'Subscription key not set.' );
			}
			if ( ! isset( $snelstart_key ) ) {
				throw new Exception( 'Snelstart key not set.' );
			}

			$this->snelstart_client = new SUCSnelstartClient( $snelstart_key, $subscription_key );
		}

		/**
		 *
		 */
		public function define_rest_routes() {
			add_action(
				'rest_api_init',
				function () {
					register_rest_route(
						'snelstart-uphance-coupling/v1',
						'/test/',
						array(
							'methods' => 'GET',
							'callback' => array( $this, 'get_test' ),
						)
					);
				}
			);
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public function get_test( WP_REST_Request $request ) {
			try {
				$response = $this->snelstart_client->bankboekingen();
				return rest_ensure_response( $response );
			} catch ( Exception $e ) {
				return rest_ensure_response( 'Something went wrong' );
			}
		}

	}
}
