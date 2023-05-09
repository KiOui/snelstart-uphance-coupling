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
						),
						'method' => array(
							'required' => true,
							'type' => 'string',
							'validate_callback' => array( $this, 'validate_args_method' ),
							'sanitize_callback' => array( $this, 'sanitize_args_method' ),
						),
					),
					'permission_callback' => array( $this, 'check_permissions' ),
				)
			);
		}

		/**
		 * Retry creating an object.
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return WP_REST_Response The response.
		 */
		private function retry_create_object( WP_REST_Request $request ): WP_REST_Response {
			$synchronizer_class = SUCSynchronizer::get_synchronizer_class( $request->get_param( 'type' ) );
			if ( is_null( $synchronizer_class ) ) {
				return new WP_REST_Response(
					array(
						'error_message' => 'Type not found.',
					),
					400
				);
			}
			$synchronizer_class->setup();

			try {
				$object_to_synchronize = $synchronizer_class->retrieve_object( $request->get_param( 'id' ) );
			} catch ( SUCAPIException $e ) {
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to get object data.',
					),
					500
				);
			}

			try {
				$synchronizer_class->synchronize_one( $object_to_synchronize );
				$synchronizer_class->create_synchronized_object( $object_to_synchronize, true, 'manual', 'create', null );
			} catch ( SUCAPIException $e ) {
				$synchronizer_class->create_synchronized_object( $object_to_synchronize, false, 'manual', 'create', $e->get_message() );
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to synchronize object: ' . esc_js( $e->get_message() ),
					),
					500
				);
			}
			return new WP_REST_Response();
		}

		/**
		 * Retry updating an object.
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return WP_REST_Response The response.
		 */
		private function retry_update_object( WP_REST_Request $request ): WP_REST_Response {
			$synchronizer_class = SUCSynchronizer::get_synchronizer_class( $request->get_param( 'type' ) );
			if ( is_null( $synchronizer_class ) ) {
				return new WP_REST_Response(
					array(
						'error_message' => 'Type not found.',
					),
					400
				);
			}

			try {
				$synchronizer_class->setup();
			} catch ( Exception $e ) {
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup synchronizer class.',
					),
					500
				);
			}

			try {
				$object_to_synchronize = $synchronizer_class->retrieve_object( $request->get_param( 'id' ) );
			} catch ( SUCAPIException $e ) {
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to get object data.',
					),
					500
				);
			}

			try {
				$synchronizer_class->update_one( $object_to_synchronize );
				$synchronizer_class->create_synchronized_object( $object_to_synchronize, true, 'manual', 'update', null );
			} catch ( SUCAPIException $e ) {
				$synchronizer_class->create_synchronized_object( $object_to_synchronize, false, 'manual', 'update', $e->get_message() );
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to update object: ' . esc_js( $e->get_message() ),
					),
					500
				);
			}
			return new WP_REST_Response();
		}

		/**
		 * Retry removing an object.
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return WP_REST_Response The response.
		 */
		private function retry_remove_object( WP_REST_Request $request ): WP_REST_Response {
			$synchronizer_class = SUCSynchronizer::get_synchronizer_class( $request->get_param( 'type' ) );
			if ( is_null( $synchronizer_class ) ) {
				return new WP_REST_Response(
					array(
						'error_message' => 'Type not found.',
					),
					400
				);
			}

			try {
				$synchronizer_class->setup();
			} catch ( Exception $e ) {
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup synchronizer class.',
					),
					500
				);
			}

			$object_to_synchronize = array(
				'id' => $request->get_param( 'id' ),
			);

			try {
				$synchronizer_class->delete_one( $object_to_synchronize );
				$synchronizer_class->create_synchronized_object( $object_to_synchronize, true, 'manual', 'delete', null );
			} catch ( SUCAPIException $e ) {
				$synchronizer_class->create_synchronized_object( $object_to_synchronize, false, 'manual', 'delete', $e->get_message() );
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to delete object: ' . esc_js( $e->get_message() ),
					),
					500
				);
			}
			return new WP_REST_Response();
		}

		/**
		 * Try to synchronize an object again.
		 *
		 * @param WP_REST_Request $request The REST API request.
		 *
		 * @return WP_REST_Response A REST response with a failed or succeeded status code.
		 */
		public function retry_synchronized_object( WP_REST_Request $request ): WP_REST_Response {
			$method = $request->get_param( 'method' );
			if ( 'create' === $method ) {
				return $this->retry_create_object( $request );
			} else if ( 'update' === $method ) {
				return $this->retry_update_object( $request );
			} else if ( 'delete' === $method ) {
				return $this->retry_remove_object( $request );
			} else {
				return new WP_REST_Response(
					array(
						'error_message' => 'Unknown method.',
					),
					400
				);
			}
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

		/**
		 * Validate method REST parameter.
		 *
		 * @param mixed           $param   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $key     The key of the parameter.
		 *
		 * @return bool Whether the method parameter was validated correctly.
		 */
		public function validate_args_method( $param, WP_REST_Request $request, string $key ): bool {
			return 'create' === $param || 'update' === $param || 'delete' === $param;
		}

		/**
		 * Sanitize method REST parameter.
		 *
		 * @param mixed           $value   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $param   The parameter name.
		 *
		 * @return string Sanitized REST parameter for method.
		 */
		public function sanitize_args_method( $value, WP_REST_Request $request, string $param ): string {
			return strval( $value );
		}
	}
}
