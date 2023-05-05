<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . '/includes/rest/SUCRestRoute.php';

if ( ! class_exists( 'SUCRetryRestRoute' ) ) {
	/**
	 * Retry REST Route
	 *
	 * @class SUCRetryRestRoute
	 */
	class SUCRetryRestRoute extends SUCRestRoute {

		/**
		 * Add the REST API endpoint.
		 *
		 * @return void
		 */
		public function add_rest_api_endpoints(): void {
			register_rest_route(
				'snelstart-uphance-coupling/v1',
				'/synchronized-objects/retry',
				array(
					'methods' => 'POST',
					'callback' => array( $this, 'retry_synchronized_object' ),
					'args' => array(
						'id' => array(
							'required' => true,
							'type' => 'int',
							'validate_callback' => array( $this, 'validate_args_id' ),
							'sanitize_callback' => array( $this, 'sanitize_args_id' ),
						),
						'type' => array(
							'required' => true,
							'type' => 'string',
							'validate_callback' => array( $this, 'validate_args_type' ),
							'sanitize_callback' => array( $this, 'sanitize_args_type' ),
						)
					),
					'permission_callback' => array( $this, 'check_permissions')
				)
			);
		}

		/**
		 * Try to synchronize an object again.
		 *
		 * @param WP_REST_Request $request The REST API request.
		 *
		 * @return WP_REST_Response A REST response with a failed or succeeded status code.
		 */
		public function retry_synchronized_object( WP_REST_Request $request ): WP_REST_Response {
			$synchronizer_class = SUCSynchronizer::get_synchronizer_class( $request->get_param( 'type' ) );
			if ( is_null( $synchronizer_class ) ) {
				return new WP_REST_Response([
					'error_message' => 'Type not found.'
				], 400);
			}
			$synchronizer_class->setup();

			try {
				$object_to_synchronize = $synchronizer_class->retrieve_object( $request->get_param( 'id' ) );
			} catch (SUCAPIException $e) {
				return new WP_REST_Response([
					'error_message' => 'Failed to get object data.'
				], 500);
			}

			try {
				$synchronizer_class->synchronize_one( $object_to_synchronize );
				$synchronizer_class->create_synchronized_object( $object_to_synchronize, true, null );
			} catch (SUCAPIException $e) {
				$synchronizer_class->create_synchronized_object( $object_to_synchronize, false, $e->get_message() );
				return new WP_REST_Response([
					'error_message' => 'Failed to synchronize object: ' . esc_js( $e->get_message() ),
				], 500);
			}

			return new WP_REST_Response();
		}

		/**
		 * Check whether user can access the REST API endpoint.
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return bool Whether a user can access this REST API endpoint.
		 */
		public function check_permissions( WP_REST_Request $request ): bool {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Validate id REST parameter.
		 *
		 * @param mixed           $param   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $key     The key of the parameter.
		 *
		 * @return bool Whether the type parameter was validated correctly.
		 */
		public function validate_args_id( $param, WP_REST_Request $request, string $key ) {
			return $param > 0;
		}

		/**
		 * Sanitize id REST parameter.
		 *
		 * @param mixed           $value   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $param   The parameter name.
		 *
		 * @return int Sanitized REST parameter for id.
		 */
		public function sanitize_args_id( $value, WP_REST_Request $request, string $param ): int {
			return intval( $value );
		}

		/**
		 * Validate type REST parameter.
		 *
		 * @param mixed           $param   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $key     The key of the parameter.
		 *
		 * @return bool Whether the type parameter was validated correctly.
		 */
		public function validate_args_type( $param, WP_REST_Request $request, string $key ): bool {
			return array_key_exists( $param, SUCSynchronizer::$synchronizer_classes );
		}

		/**
		 * Sanitize type REST parameter.
		 *
		 * @param mixed           $value   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $param   The parameter name.
		 *
		 * @return string Sanitized REST parameter for type.
		 */
		public function sanitize_args_type( $value, WP_REST_Request $request, string $param ): string {
			return strval( $value );
		}
	}
}
