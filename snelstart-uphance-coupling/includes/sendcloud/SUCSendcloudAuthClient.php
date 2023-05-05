<?php
/**
 * Sendcloud Auth Client
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-sucapiauthclient.php';

if ( ! class_exists( 'SUCSendcloudAuthClient' ) ) {
	/**
	 * Sendcloud Auth class.
	 *
	 * @class SUCSendcloudAuthClient
	 */
	class SUCSendcloudAuthClient extends SUCAPIAuthClient {

		/**
		 * Token info setting name.
		 *
		 * @var string
		 */
		protected string $token_info_setting = 'suc_sendcloud_token_info';

		/**
		 * Email address used for authentication towards Uphance.
		 *
		 * @var string
		 */
		private string $email;

		/**
		 * Password used for authentication towards Uphance.
		 *
		 * @var string
		 */
		private string $password;

		/**
		 * Token URL for authentication tokens.
		 *
		 * @var string
		 */
		private string $token_url = 'https://api.uphance.com/oauth/token';

		/**
		 * Constructor.
		 *
		 * @param string $email email to login to Uphance.
		 * @param string $password password to login to Uphance.
		 */
		public function __construct( string $email, string $password ) {
			$this->email = $email;
			$this->password = $password;
		}

		/**
		 * Request a new Access token.
		 *
		 * @throws SUCAPIException On error with the API request, also thrown when authentication fails.
		 */
		public function request_access_token(): array {

		}
	}
}
