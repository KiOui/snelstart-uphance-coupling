<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . '/includes/rest/SUCRestRoute.php';
include_once SUC_ABSPATH . 'includes/class-sucsettings.php';

if ( ! class_exists( 'SUCPickTicketRestRoute' ) ) {
	/**
	 * Pick Ticket REST Route
	 *
	 * @class SUCPickTicketRestRoute
	 */
	class SUCPickTicketRestRoute extends SUCRestRoute {

		/**
		 * Add the REST API endpoint.
		 *
		 * @return void
		 */
		public function add_rest_api_endpoints(): void {
			register_rest_route(
				'snelstart-uphance-coupling/v1',
				'/uphance/pick-ticket',
				array(
					'methods' => 'POST',
					'callback' => array( $this, 'synchronize_pick_ticket_to_sendcloud' ),
					'args' => array(
						'event' => array(
							'required' => true,
							'type' => 'string',
							'validate_callback' => array( $this, 'validate_args_event' ),
							'sanitize_callback' => array( $this, 'sanitize_args_event' ),
						),
						'pick_ticket' => array(
							'required' => true,
							'type' => 'object',
						),
					),
					'permission_callback' => array( $this, 'check_permissions' ),
				)
			);
		}

		/**
		 * Try to create a pick ticket in Sendcloud.
		 *
		 * @param WP_REST_Request $request The REST API request.
		 *
		 * @return WP_REST_Response A REST response with a failed or succeeded status code.
		 */
		private function create_pick_ticket_in_sendcloud( WP_REST_Request $request ): WP_REST_Response {
			$pick_ticket = $request->get_param( 'pick_ticket' );
			$synchronizer_class = SUCSynchronizer::get_synchronizer_class( SUCPickTicketSynchronizer::$type );

			$mapped_object = SUCObjectMapping::get_mapped_object( SUCPickTicketSynchronizer::$type, 'uphance', 'sendcloud', $pick_ticket['id'] );
			if ( null !== $mapped_object ) {
				$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'create', 'Mapped object for this type already exists.' );
				return new WP_REST_Response(
					array(
						'error_message' => 'Mapped object for this type already exists.',
					),
					200
				);
			}

			try {
				$synchronizer_class->setup();
			} catch ( Exception $e ) {
				if ( $e instanceof SUCAPIException ) {
					$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'create', $e->get_message() );
				} else {
					$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'create', $e->__toString() );
				}
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup synchronizer.',
					),
					200
				);
			}

			try {
				$synchronizer_class->synchronize_one( $pick_ticket );
				$synchronizer_class->create_synchronized_object( $pick_ticket, true, 'webhook', 'create', null );
			} catch ( SUCAPIException $e ) {
				$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'create', $e->get_message() );
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to synchronize object: ' . esc_js( $e->get_message() ),
					),
					200
				);
			}
			return new WP_REST_Response();
		}

		/**
		 * Update a pick ticket in Sendcloud.
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return WP_REST_Response The response.
		 */
		private function update_pick_ticket_in_sendcloud( WP_REST_Request $request ): WP_REST_Response {
			$pick_ticket = $request->get_param( 'pick_ticket' );
			$synchronizer_class = SUCSynchronizer::get_synchronizer_class( SUCPickTicketSynchronizer::$type );
			try {
				$synchronizer_class->setup();
			} catch ( Exception $e ) {
				if ( $e instanceof SUCAPIException ) {
					$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'update', $e->get_message() );
				} else {
					$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'update', $e->__toString() );
				}
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup synchronizer.',
					),
					200
				);
			}

			try {
				$synchronizer_class->update_one( $pick_ticket );
				$synchronizer_class->create_synchronized_object( $pick_ticket, true, 'webhook', 'update', null );
			} catch ( Exception $e ) {
				if ( $e instanceof SUCAPIException ) {
					$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'update', $e->get_message() );
				} else {
					$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'update', $e->__toString() );
				}
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to synchronize invoice to Snelstart.',
					),
					200
				);
			}
			return new WP_REST_Response();
		}

		/**
		 * Delete a pick ticket from Sendcloud.
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return WP_REST_Response The response.
		 */
		private function delete_pick_ticket_from_sendcloud( WP_REST_Request $request ): WP_REST_Response {
			$pick_ticket = $request->get_param( 'pick_ticket' );
			$synchronizer_class = SUCSynchronizer::get_synchronizer_class( SUCPickTicketSynchronizer::$type );

			try {
				$synchronizer_class->setup();
			} catch ( Exception $e ) {
				if ( $e instanceof SUCAPIException ) {
					$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'delete', $e->get_message() );
				} else {
					$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'delete', $e->__toString() );
				}

				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup synchronizer.',
					),
					200
				);
			}

			try {
				$synchronizer_class->delete_one( $pick_ticket );
				$synchronizer_class->create_synchronized_object( $pick_ticket, true, 'webhook', 'delete', null );
			} catch ( SUCAPIException $e ) {
				$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'delete', $e->get_message() );
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to remove object: ' . esc_js( $e->get_message() ),
					),
					200
				);
			} catch ( Exception $e ) {
				$synchronizer_class->create_synchronized_object( $pick_ticket, false, 'webhook', 'delete', $e->__toString() );
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to remove object: ' . esc_js( $e->__toString() ),
					),
					200
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
		public function synchronize_pick_ticket_to_sendcloud( WP_REST_Request $request ): WP_REST_Response {
			$event = $request->get_param( 'event' );
			if ( 'pick_ticket_create' === $event ) {
				return $this->create_pick_ticket_in_sendcloud( $request );
			} else if ( 'pick_ticket_update' === $event ) {
				return $this->update_pick_ticket_in_sendcloud( $request );
			} else if ( 'pick_ticket_delete' === $event ) {
				return $this->delete_pick_ticket_from_sendcloud( $request );
			} else {
				return new WP_REST_Response(
					array(
						'error_message' => 'Event type not known.',
					),
					400
				);
			}
		}

		/**
		 * Validate secret REST parameter.
		 *
		 * @param mixed           $param   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $key     The key of the parameter.
		 *
		 * @return bool Whether the secret parameter was validated correctly.
		 */
		public function validate_args_event( $param, WP_REST_Request $request, string $key ): bool {
			return 'pick_ticket_create' === $param || 'pick_ticket_update' === $param || 'pick_ticket_delete' === $param;
		}

		/**
		 * Sanitize secret REST parameter.
		 *
		 * @param mixed           $value   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $param   The parameter name.
		 *
		 * @return string Sanitized REST parameter for secret.
		 */
		public function sanitize_args_event( $value, WP_REST_Request $request, string $param ): string {
			return strval( $value );
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
			return false;
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
