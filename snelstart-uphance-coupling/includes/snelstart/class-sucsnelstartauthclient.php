<?php
/**
 * Snelstart Client Key
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-sucapiauthclient.php';

if ( ! class_exists( 'SUCSnelstartAuthClient' ) ) {
	/**
	 * Snelstart OAuth class.
	 *
	 * @class SUCSnelstartAuthClient
	 */
	class SUCSnelstartAuthClient extends SUCAPIAuthClient {

		/**
		 * Token setting name.
		 *
		 * @var string
		 */
		protected string $token_info_setting = 'suc_snelstart_token_info';

		/**
		 * Client key.
		 *
		 * @var string
		 */
		private string $client_key;

		/**
		 * Token URL.
		 *
		 * @var string
		 */
		private string $_token_url = 'https://auth.snelstart.nl/b2b/token';

		/**
		 * Constructor.
		 *
		 * @param string $client_key the client key.
		 */
		public function __construct( string $client_key ) {
			$this->client_key = $client_key;
		}

		/**
		 * Request an access token.
		 *
		 * @return array the response
		 * @throws SUCAPIException On API communication error.
		 */
		public function request_access_token(): array {
			$headers = array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			);

			$body = 'grant_type=clientkey&clientkey=' . $this->client_key;
			$response = wp_remote_post(
				$this->_token_url,
				array(
					'headers' => $headers,
					'body' => $body,
				)
			);
			if ( is_wp_error( $response ) ) {
				$msg = SUCAPIClient::get_error_message( wp_remote_retrieve_body( $response ) );
				throw new SUCAPIException(
					intval( wp_remote_retrieve_response_code( $response ) ),
					-1,
					esc_html( $this->_token_url . ":\n " . $msg ),
					null,
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- This is an array.
					is_array( wp_remote_retrieve_headers( $response ) ) ? wp_remote_retrieve_headers( $response ) : wp_remote_retrieve_headers( $response )->getAll(),
				);
			} else {
				return json_decode( wp_remote_retrieve_body( $response ), true );
			}
		}
	}
}
