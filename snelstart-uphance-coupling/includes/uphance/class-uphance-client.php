<?php
/**
 * Uphance Client class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-api-client.php';
include_once SUC_ABSPATH . 'includes/uphance/class-uphance-auth-client.php';
include_once SUC_ABSPATH . 'includes/client/class-api-paginated-result.php';

if ( ! class_exists( 'SUCUphanceClient' ) ) {
	/**
	 * Uphance Client class
	 *
	 * @class SUCUphanceClient
	 */
	class SUCUphanceClient extends SUCAPIClient {

		protected string $prefix = 'https://api.uphance.com/';

		protected static ?SUCUphanceClient $_instance = null;

		private bool $synched_organisation = false;

		/**
		 * Snelstart Uphance Coupling Core.
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return ?SUCUphanceClient
		 */
		public static function instance(): ?SUCUphanceClient {
			if ( is_null( self::$_instance ) ) {
				$settings = get_option( 'suc_settings', null );
				if ( ! isset( $settings ) ) {
					return null;
				}
				$uphance_username = $settings['uphance_username'];
				$uphance_password = $settings['uphance_password'];

				if ( isset( $uphance_username ) && isset( $uphance_password ) && $uphance_username !== '' && $uphance_password !== '' ) {
					self::$_instance = new SUCUphanceClient( new SUCUphanceAuthClient( $uphance_username, $uphance_password ) );
				} else {
					return null;
				}
			}

			return self::$_instance;
		}

		public function __construct( ?SUCAPIAuthClient $auth_client, int $requests_timeout = 45 ) {
			parent::__construct( $auth_client, $requests_timeout );
			$this->requests_timeout = $requests_timeout;
		}

		/**
		 * @throws SUCAPIException
		 */
		public function organisations(): array {
			return $this->_get( 'organisations', null, null );
		}

		public function set_current_organisation( int $organisation_id ): bool {
			$response = $this->_post( 'organisations/set_current_org', null, array( 'organizationId' => $organisation_id ) );
			if ( isset( $response['Status'] ) && $response['Status'] === 'Updated' ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * @throws SUCAPIException
		 */
		public function invoices(): SUCAPIPaginatedResult {
			$response = $this->_get( 'invoices', null, null );
			return new SUCAPIPaginatedResult( $response );
		}

		/**
		 * @throws SUCAPIException
		 */
		public function invoices_since(int $since_id): SUCAPIPaginatedResult {
			$response = $this->_get('invoices/since?since_id=' . $since_id, null, null);
			return new SUCAPIPaginatedResult( $response );
		}
	}
}
