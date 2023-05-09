<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . '/includes/rest/SUCRestRoute.php';
include_once SUC_ABSPATH . 'includes/class-sucsettings.php';

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
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'update', $e->get_message() );
				} else {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'update', $e->__toString() );
				}
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup synchronizer.',
					),
					500
				);
			}

			try {
				$synchronizer_class->update_one( $invoice );
				$synchronizer_class->create_synchronized_object( $invoice, true, 'webhook', 'update', null );
			} catch ( Exception $e ) {
				if ( $e instanceof SUCAPIException ) {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'update', $e->get_message() );
				} else {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'update', $e->__toString() );
				}
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to synchronize invoice to Snelstart.',
					),
					500
				);
			}
			return new WP_REST_Response();
		}

		/**
		 * Maybe delete an invoice from Snelstart (if it has not been done before).
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return WP_REST_Response The response.
		 */
		private function maybe_delete_invoice_from_snelstart( WP_REST_Request $request ): WP_REST_Response {
			$invoice = $request->get_param( 'invoice' );
			$mapped_object = SUCObjectMapping::get_mapped_object( 'invoice', 'uphance', 'snelstart', $invoice['id'] );
			if ( null === $mapped_object ) {
				return new WP_REST_Response();
			} else {
				return $this->delete_invoice_from_snelstart( $request );
			}
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
			$mapped_object = SUCObjectMapping::get_mapped_object( 'invoice', 'uphance', 'snelstart', $invoice['id'] );
			if ( null === $mapped_object ) {
				return new WP_REST_Response(
					array(
						'error_message' => 'No invoice mapping found with given data.',
					),
					400
				);
			}

			$snelstart_client = SUCSnelstartClient::instance();
			if ( null === $snelstart_client ) {
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup Uphance client.',
					),
					500
				);
			}

			try {
				$snelstart_client->remove_verkoopboeking( get_post_meta( $mapped_object->ID, 'mapped_to_object_id', true ) );
				SUCSynchronizedObjects::create_synchronized_object( $invoice['id'], SUCInvoiceSynchronizer::$type, true, 'webhook', 'delete', null, null, null );
			} catch ( SUCAPIException $e ) {
				SUCSynchronizedObjects::create_synchronized_object( $invoice['id'], SUCInvoiceSynchronizer::$type, false, 'webhook', 'delete', null, $e->get_message(), null );
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to remove object: ' . esc_js( $e->get_message() ),
					),
					500
				);
			}
			wp_delete_post( $mapped_object->ID, true );
			return new WP_REST_Response();
		}

		/**
		 * Maybe create an invoice in Snelstart (if it has not been done before).
		 *
		 * @param WP_REST_Request $request The request.
		 *
		 * @return WP_REST_Response The response.
		 */
		private function maybe_create_invoice_in_snelstart( WP_REST_Request $request ): WP_REST_Response {
			$invoice = $request->get_param( 'invoice' );
			$mapped_object = SUCObjectMapping::get_mapped_object( 'invoice', 'uphance', 'snelstart', $invoice['id'] );
			if ( null === $mapped_object ) {
				return $this->create_invoice_in_snelstart( $request );
			} else {
				return new WP_REST_Response();
			}
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

			try {
				$synchronizer_class->setup();
			} catch ( Exception $e ) {
				if ( $e instanceof SUCAPIException ) {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'create', $e->get_message() );
				} else {
					$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'create', $e->__toString() );
				}
				return new WP_REST_Response(
					array(
						'error_message' => 'Failed to setup synchronizer.',
					),
					500
				);
			}

			try {
				$synchronizer_class->synchronize_one( $invoice );
				$synchronizer_class->create_synchronized_object( $invoice, true, 'webhook', 'create', null );
			} catch ( SUCAPIException $e ) {
				$synchronizer_class->create_synchronized_object( $invoice, false, 'webhook', 'create', $e->get_message() );
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
		 * Try to synchronize an invoice to Snelstart.
		 *
		 * @param WP_REST_Request $request The REST API request.
		 *
		 * @return WP_REST_Response A REST response with a failed or succeeded status code.
		 */
		public function synchronize_invoice_to_snelstart( WP_REST_Request $request ): WP_REST_Response {
			$event = $request->get_param( 'event' );
			if ( 'invoice_create' === $event ) {
				return $this->maybe_create_invoice_in_snelstart( $request );
			} else if ( 'invoice_update' === $event ) {
				return $this->update_invoice_in_snelstart( $request );
			} else if ( 'invoice_delete' === $event ) {
				return $this->maybe_delete_invoice_from_snelstart( $request );
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
			print_r( $request );
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
		public function sanitize_args_event( $value, WP_REST_Request $request, string $param ): string {
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
