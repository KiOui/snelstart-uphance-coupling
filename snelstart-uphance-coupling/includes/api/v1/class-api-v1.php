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
include_once SUC_ABSPATH . 'includes/snelstart/class-snelstart-auth-client.php';
include_once SUC_ABSPATH . 'includes/uphance/class-uphance-client.php';
include_once SUC_ABSPATH . 'includes/uphance/class-uphance-auth-client.php';


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
		private SUCUphanceClient $uphance_client;

		public function __construct(SUCSnelstartClient $snelstart_client, SUCUphanceClient $uphance_client) {
			$this->snelstart_client = $snelstart_client;
			$this->uphance_client = $uphance_client;
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
				//$response = $this->uphance_client->organisations();
				$this->uphance_client->set_current_organisation(36573);
				// $response = $this->snelstart_client->add_verkoopboeking("1234", "5c2557c8-ed7d-4ad8-b950-2ad3ff022545", array());
				$response = $this->snelstart_client->grootboeken();
				return rest_ensure_response( $response );
			} catch ( Exception $e ) {
				return rest_ensure_response( 'Something went wrong' );
			}
		}

	}
}
