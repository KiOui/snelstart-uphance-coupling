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
				self::$_instance = new SUCSendcloudClient( new SUCSendcloudAuthClient() );
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @param SUCSendcloudAuthClient|null $auth_client the authentication client.
		 * @param int $requests_timeout request timeout.
		 */
		public function __construct( ?SUCSendcloudAuthClient $auth_client, int $requests_timeout = 45 ) {
			parent::__construct( $auth_client, $requests_timeout );
			$this->requests_timeout = $requests_timeout;
		}

	}
}
