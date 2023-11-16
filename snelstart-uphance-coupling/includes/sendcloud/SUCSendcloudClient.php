<?php
/**
 * Sendcloud Client class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-sucapiauthclient.php';
include_once SUC_ABSPATH . 'includes/sendcloud/SUCSendcloudAuthClient.php';

if ( ! class_exists( 'SUCSendcloudClient' ) ) {
	/**
	 * Sendcloud Client class
	 *
	 * @class SUCSendcloudClient
	 */
	class SUCSendcloudClient extends SUCAPIClient {

		/**
		 * The URL endpoint for the Sendcloud API.
		 *
		 * @var string
		 */
		protected string $prefix = 'https://panel.sendcloud.sc/api/v2/';

		/**
		 * Sendcloud Client instance.
		 *
		 * @var SUCSendcloudClient|null
		 */
		protected static ?SUCSendcloudClient $_instance = null;

		/**
		 * Sendcloud instance.
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return ?SUCSendcloudClient the client if all required settings are set, null otherwise
		 */
		public static function instance(): ?SUCSendcloudClient {
			if ( is_null( self::$_instance ) ) {
				$settings         = SUCSettings::instance()->get_settings();
				$public_key = $settings->get_value( 'sendcloud_public_key' );
				$private_key = $settings->get_value( 'sendcloud_private_key' );
				if ( isset( $public_key ) && isset( $private_key ) && '' !== $public_key && '' !== $private_key ) {
					self::$_instance = new SUCSendcloudClient( new SUCSendcloudAuthClient( $public_key, $private_key ) );
				} else {
					return null;
				}
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @param SUCSendcloudAuthClient|null $auth_client the authentication client.
		 * @param int                         $requests_timeout request timeout.
		 */
		public function __construct( ?SUCSendcloudAuthClient $auth_client, int $requests_timeout = 45 ) {
			parent::__construct( $auth_client, $requests_timeout );
			$this->requests_timeout = $requests_timeout;
		}

		/**
		 * Overwrite auth_headers function to add Ocp-Apim-Subscription-Key.
		 *
		 * @return array auth headers to use for all requests.
		 * @throws SUCAPIException When requesting the access token fails.
		 */
		protected function auth_headers(): array {
			$keys = $this->auth_manager->request_access_token();
			$public_key = $keys['public-key'];
			$private_key = $keys['private-key'];

			return array(
				'Authorization' => 'Basic ' . base64_encode( "$public_key:$private_key" ),
			);
		}

		/**
		 * Create a parcel in Sendcloud.
		 *
		 * @param array $data The data for the parcel.
		 *
		 * @return array the returned data.
		 * @throws SUCAPIException On Exception with the request.
		 */
		public function create_parcel( array $data ): array {
			return $this->_post(
				'parcels',
				null,
				$data,
			);
		}

		/**
		 * Update a parcel in Sendcloud.
		 *
		 * @param array $data The data for the parcel.
		 *
		 * @return array the returned data.
		 * @throws SUCAPIException On Exception with the request.
		 */
		public function update_parcel( array $data ): array {
			return $this->_put(
				'parcels',
				null,
				$data,
			);
		}

		/**
		 * Remove a parcel from Sendcloud.
		 *
		 * @param string $id The ID of the parcel in Sendcloud.
		 *
		 * @return array the returned data.
		 * @throws SUCAPIException On Exception with the request.
		 */
		public function cancel_parcel( string $id ): array {
			return $this->_post(
				"parcels/$id/cancel",
				null,
				null,
			);
		}

		/**
		 * Get available shipping methods.
		 *
		 * @return array An array with available shipping methods.
		 * @throws SUCAPIException On exception with the API.
		 */
		public function get_shipping_methods(): array {
			return $this->_get(
				'shipping_methods',
				null,
				null,
			);
		}
	}
}
