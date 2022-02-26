<?php
/**
 * Uphance Auth Client
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-sucapiauthclient.php';

if ( ! class_exists( 'SUCUphanceAuthClient' ) ) {
	/**
	 * Uphance Auth class.
	 *
	 * @class SUCUphanceAuthClient
	 */
	class SUCUphanceAuthClient extends SUCAPIAuthClient {

		/**
		 * Token info setting name.
		 *
		 * @var string
		 */
		protected string $token_info_setting = 'suc_uphance_token_info';

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
			$headers = array(
				'Content-Type' => 'application/json',
			);

			$body = json_encode(
				array(
					'email' => $this->email,
					'password' => $this->password,
					'grant_type' => 'password',
				)
			);

			$response = wp_remote_post(
				$this->token_url,
				array(
					'headers' => $headers,
					'body' => $body,
				)
			);
			if ( is_wp_error( $response ) ) {
				$msg = SUCAPIClient::get_error_message( wp_remote_retrieve_body( $response ) );
				throw new SUCAPIException(
					wp_remote_retrieve_response_code( $response ),
					-1,
					$this->token_url . ":\n " . $msg,
					null,
					wp_remote_retrieve_headers( $response )->getAll(),
				);
			} else {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset( $body['error'] ) ) {
					throw new SUCAPIException(
						wp_remote_retrieve_response_code( $response ),
						-1,
						$this->token_url . ":\n " . $body['error'],
						null,
						wp_remote_retrieve_headers( $response )->getAll(),
					);
				}
				if ( is_null( $body ) ) {
					throw new SUCAPIException(
						wp_remote_retrieve_response_code( $response ),
						-1,
						'Access token request returned null',
						null,
						wp_remote_retrieve_headers( $response )->getAll(),
					);
				}
				return json_decode( wp_remote_retrieve_body( $response ), true );
			}
		}
	}
}
