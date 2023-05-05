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
		 * Token URL for authentication tokens.
		 *
		 * @var string
		 */
		private string $token_url = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
		}

		/**
		 * Request a new Access token.
		 *
		 * @throws SUCAPIException On error with the API request, also thrown when authentication fails.
		 */
		public function request_access_token(): array {
			return array();
		}
	}
}
