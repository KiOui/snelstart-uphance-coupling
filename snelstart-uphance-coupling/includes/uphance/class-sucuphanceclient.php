<?php
/**
 * Uphance Client class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-sucapiclient.php';
include_once SUC_ABSPATH . 'includes/uphance/class-sucuphanceauthclient.php';
include_once SUC_ABSPATH . 'includes/client/class-sucapipaginatedresult.php';

if ( ! class_exists( 'SUCUphanceClient' ) ) {
	/**
	 * Uphance Client class
	 *
	 * @class SUCUphanceClient
	 */
	class SUCUphanceClient extends SUCAPIClient {

		/**
		 * The URL endpoint for the Uphance API.
		 *
		 * @var string
		 */
		protected string $prefix = 'https://api.uphance.com/';

		/**
		 * Uphance Client instance.
		 *
		 * @var SUCUphanceClient|null
		 */
		protected static ?SUCUphanceClient $_instance = null;

		/**
		 * Uphance instance.
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return ?SUCUphanceClient the client if all required settings are set, null otherwise
		 */
		public static function instance(): ?SUCUphanceClient {
			if ( is_null( self::$_instance ) ) {
				$settings = get_option( 'suc_settings', null );
				if ( ! isset( $settings ) ) {
					return null;
				}
				$uphance_username = $settings['uphance_username'];
				$uphance_password = $settings['uphance_password'];

				if ( isset( $uphance_username ) && isset( $uphance_password ) && '' !== $uphance_username && '' !== $uphance_password ) {
					self::$_instance = new SUCUphanceClient( new SUCUphanceAuthClient( $uphance_username, $uphance_password ) );
				} else {
					return null;
				}
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @param SUCAPIAuthClient|null $auth_client the authentication client.
		 * @param int                   $requests_timeout request timeout.
		 */
		public function __construct( ?SUCAPIAuthClient $auth_client, int $requests_timeout = 45 ) {
			parent::__construct( $auth_client, $requests_timeout );
			$this->requests_timeout = $requests_timeout;
		}

		/**
		 * Get all organisations.
		 *
		 * @return array organisations.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function organisations(): array {
			return $this->_get( 'organisations', null, null );
		}

		/**
		 * Set current organisation.
		 *
		 * @param int $organisation_id the organisation ID to set.
		 *
		 * @return bool true if setting succeeded, false otherwise.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function set_current_organisation( int $organisation_id ): bool {
			$response = $this->_post( 'organisations/set_current_org', null, array( 'organizationId' => $organisation_id ) );
			if ( isset( $response['Status'] ) && 'Updated' === $response['Status'] ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Get all invoices.
		 *
		 * @param int|null $since_id optional ID of invoice, when set only invoices from this ID will be requested.
		 *
		 * @return SUCAPIPaginatedResult the result with invoices.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function invoices( ?int $since_id = null ): SUCAPIPaginatedResult {
			$url = 'invoices';
			if ( isset( $since_id ) ) {
				$queries = array(
					'since_id' => $since_id,
				);
				$url = $url . $this->create_querystring( $queries );
			}
			$response = $this->_get( $url, null, null );
			return new SUCAPIPaginatedResult( $response );
		}

		/**
		 * Get Customer by ID.
		 *
		 * @param int $customer_id the customer ID to get.
		 *
		 * @return array a customer.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function customer_by_id( int $customer_id ): array {
			return $this->_get( 'customers/' . $customer_id, null, null );
		}
	}
}
