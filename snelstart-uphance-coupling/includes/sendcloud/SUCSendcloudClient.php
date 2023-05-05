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
		protected string $prefix = 'https://api.uphance.com/';

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
				$uphance_username = $settings->get_value( 'uphance_username' );
				$uphance_password = $settings->get_value( 'uphance_password' );

				if ( isset( $uphance_username ) && isset( $uphance_password ) && '' !== $uphance_username && '' !== $uphance_password ) {
					self::$_instance = new SUCUphanceClient( new SUCSendcloudAuthClient( $uphance_username, $uphance_password ) );
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
		 * @param int $requests_timeout request timeout.
		 */
		public function __construct( ?SUCAPIAuthClient $auth_client, int $requests_timeout = 45 ) {
			parent::__construct( $auth_client, $requests_timeout );
			$this->requests_timeout = $requests_timeout;
		}

	}
}
