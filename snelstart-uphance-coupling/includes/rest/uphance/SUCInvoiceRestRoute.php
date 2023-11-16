<?php
/**
 * SUC Invoice REST Route.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . '/includes/rest/SUCRestRoute.php';
include_once SUC_ABSPATH . '/includes/class-sucsettings.php';
include_once SUC_ABSPATH . '/includes/objects/SUCObjectMapping.php';

if ( ! class_exists( 'SUCInvoiceRestRoute' ) ) {
	/**
	 * Invoice REST Route
	 *
	 * @class SUCInvoiceRestRoute
	 */
	class SUCInvoiceRestRoute extends SUCRestRoute {

		/**
		 * Add the REST API endpoint.
		 *
		 * @return void
		 */
		public function add_rest_api_endpoints(): void {
			register_rest_route(
				'snelstart-uphance-coupling/v1',
				'/uphance/invoice/(?P<secret>[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})',
				array(
					'methods' => 'POST',
					'callback' => array( $this, 'synchronize_invoice_to_snelstart' ),
					'args' => array(
						'event' => array(
							'required' => true,
							'type' => 'string',
							'validate_callback' => array( $this, 'validate_args_event' ),
							'sanitize_callback' => array( $this, 'sanitize_args_event' ),
						),
						'invoice' => array(
							'required' => true,
							'type' => 'object',
						),
					),
					'permission_callback' => array( $this, 'check_permissions' ),
				)
			);
		}

		/**
		 * Update an invoice in Snelstart.
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return WP_REST_Response The response.
		 */
		private function update_invoice_in_snelstart( WP_REST_Request $request ): WP_REST_Response {
			$invoice = $request->get_param( 'invoice' );
			$synchronizer_class = SUCSynchronizer::get_synchronizer_class( 'invoice' );
			try {
				$synchronizer_class->setup();
			} catch ( Exception $e ) {
				if ( $e instanceof SUCAPIException ) {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'update', $e->get_message(), null );
				} else {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'update', $e->__toString(), null );
				}
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup synchronizer.',
					),
					200
				);
			}

			try {
				$synchronizer_class->update_one( $invoice );
				$synchronizer_class->create_synchronized_object( $invoice, true, 'webhook', 'update', null, null );
			} catch ( Exception $e ) {
				if ( $e instanceof SUCAPIException ) {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'update', $e->get_message(), null );
				} else {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'update', $e->__toString(), null );
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
		 * Delete an invoice from Snelstart.
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return WP_REST_Response The response.
		 */
		private function delete_invoice_from_snelstart( WP_REST_Request $request ): WP_REST_Response {
			$invoice = $request->get_param( 'invoice' );
			$synchronizer_class = SUCSynchronizer::get_synchronizer_class( 'invoice' );

			try {
				$synchronizer_class->setup();
			} catch ( Exception $e ) {
				if ( $e instanceof SUCAPIException ) {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'delete', $e->get_message(), null );
				} else {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'delete', $e->__toString(), null );
				}

				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup synchronizer.',
					),
					200
				);
			}

			try {
				$synchronizer_class->delete_one( $invoice );
				$synchronizer_class->create_synchronized_object( $invoice, true, 'webhook', 'delete', null, null );
			} catch ( SUCAPIException $e ) {
				$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'delete', $e->get_message(), null );
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to remove object: ' . esc_js( $e->get_message() ),
					),
					200
				);
			} catch ( Exception $e ) {
				$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'delete', $e->__toString(), null );
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
		 * Try to create an invoice in Snelstart.
		 *
		 * @param WP_REST_Request $request The REST API request.
		 *
		 * @return WP_REST_Response A REST response with a failed or succeeded status code.
		 */
		private function create_invoice_in_snelstart( WP_REST_Request $request ): WP_REST_Response {
			$invoice = $request->get_param( 'invoice' );
			$synchronizer_class = SUCSynchronizer::get_synchronizer_class( 'invoice' );

			$mapped_object = SUCObjectMapping::get_mapped_object( SUCInvoiceSynchronizer::$type, 'uphance', 'snelstart', $invoice['id'] );
			if ( null !== $mapped_object ) {
				$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'create', 'Mapped object for this type already exists.', null );
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
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'create', $e->get_message(), null );
				} else {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'create', $e->__toString(), null );
				}
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup synchronizer.',
					),
					200
				);
			}

			try {
				$synchronizer_class->synchronize_one( $invoice );
				$synchronizer_class->create_synchronized_object( $invoice, true, 'webhook', 'create', null, null );
			} catch ( SUCAPIException $e ) {
				$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'create', $e->get_message(), null );
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
		 * Try to synchronize an invoice to Snelstart.
		 *
		 * @param WP_REST_Request $request The REST API request.
		 *
		 * @return WP_REST_Response A REST response with a failed or succeeded status code.
		 */
		public function synchronize_invoice_to_snelstart( WP_REST_Request $request ): WP_REST_Response {
			$event = $request->get_param( 'event' );
			if ( 'invoice_create' === $event ) {
				return $this->create_invoice_in_snelstart( $request );
			} else if ( 'invoice_update' === $event ) {
				return $this->update_invoice_in_snelstart( $request );
			} else if ( 'invoice_delete' === $event ) {
				return $this->delete_invoice_from_snelstart( $request );
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
		public function validate_args_event( mixed $param, WP_REST_Request $request, string $key ): bool {
			return 'invoice_create' === $param || 'invoice_update' === $param || 'invoice_delete' === $param;
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
		public function sanitize_args_event( mixed $value, WP_REST_Request $request, string $param ): string {
			return strval( $value );
		}

		/**
		 * Check whether user can access the REST API endpoint.
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return bool Whether a user can access this REST API endpoint.
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
