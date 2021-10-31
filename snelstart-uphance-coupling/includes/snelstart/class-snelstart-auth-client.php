<?php
/**
 * Snelstart Client Key
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-api-auth-client.php';

if ( ! class_exists( 'SUCSnelstartAuthClient' ) ) {
	/**
	 * Snelstart OAuth class
	 *
	 * @class SUCSnelstartClientKey
	 */
	class SUCSnelstartClientKey extends SUCAPIAuthClient {

		protected string $TOKEN_INFO_SETTING = 'suc_snelstart_token_info';

		private string $_client_key;
		private string $_token_url = "https://auth.snelstart.nl/b2b/token";

		public function __construct(string $client_key) {
			$this->_client_key = $client_key;
		}

		/**
		 * @throws SUCAPIException
		 */
		public function request_access_token(): array {
			$headers = array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			);

			$body = "grant_type=clientkey&clientkey=" . $this->_client_key;

			$response = wp_remote_post($this->_token_url, array(
				"headers" => $headers,
				"body" => $body,
			));
			if ( is_wp_error( $response ) ) {
				try {
					$msg = json_decode( wp_remote_retrieve_body( $response ), true )['message'];
				} catch (Exception $e) {
					$msg = "error";
				}
				throw new SUCAPIException(
					wp_remote_retrieve_response_code( $response ),
					-1,
					$this->_token_url . ":\n " . $msg,
					null,
					wp_remote_retrieve_headers( $response ),
				);
			}
			else {
				return json_decode( wp_remote_retrieve_body( $response ), true );
			}
		}
	}
}
