<?php
/**
 * Uphance Auth Client
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-api-auth-client.php';

if ( ! class_exists( 'SUCUphanceAuthClient' ) ) {
	/**
	 * Uphance Auth class
	 *
	 * @class SUCUphanceAuthClient
	 */
	class SUCUphanceAuthClient extends SUCAPIAuthClient {

		protected string $TOKEN_INFO_SETTING = 'suc_uphance_token_info';

		private string $email;
		private string $password;
		private string $_token_url = "https://api.uphance.com/oauth/token";

		public function __construct(string $email, string $password) {
			$this->email = $email;
			$this->password = $password;
		}

		/**
		 * @throws SUCAPIException
		 */
		public function request_access_token(): array {
			$headers = array(
				'Content-Type' => 'application/json',
			);

			$body = json_encode(array(
				"email" => $this->email,
				"password" => $this->password,
				"grant_type" => "password",
			));

			$response = wp_remote_post($this->_token_url, array(
				"headers" => $headers,
				"body" => $body,
			));
			if ( is_wp_error( $response ) ) {
				$msg = SUCAPIClient::get_error_message(wp_remote_retrieve_body($response));
				throw new SUCAPIException(
					wp_remote_retrieve_response_code( $response ),
					-1,
					$this->_token_url . ":\n " . $msg,
					null,
					wp_remote_retrieve_headers( $response )->getAll(),
				);
			}
			else {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset ( $body['error'] ) ) {
					throw new SUCAPIException(
						wp_remote_retrieve_response_code( $response ),
						-1,
						$this->_token_url . ":\n " . $body['error'],
						null,
						wp_remote_retrieve_headers( $response )->getAll(),
					);
				}
				return json_decode( wp_remote_retrieve_body( $response ), true );
			}
		}
	}
}
