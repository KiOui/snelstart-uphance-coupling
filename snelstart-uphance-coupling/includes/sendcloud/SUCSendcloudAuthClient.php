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
		 * The public key.
		 *
		 * @var string
		 */
		private string $public_key;

		/**
		 * The private key.
		 *
		 * @var string
		 */
		private string $private_key;

		/**
		 * Constructor.
		 */
		public function __construct( string $public_key, string $private_key ) {
			$this->public_key = $public_key;
			$this->private_key = $private_key;
		}

		/**
		 * Get public and private key.
		 *
		 * @return array The public and private key.
		 */
		public function request_access_token(): array {
			return array(
				'private-key' => $this->private_key,
				'public-key' => $this->public_key,
			);
		}
	}
}
