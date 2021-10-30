<?php
/**
 * Snelstart Client class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-api-client.php';
include_once SUC_ABSPATH . 'includes/snelstart/class-snelstart-api-exception.php';
include_once SUC_ABSPATH . 'includes/snelstart/class-snelstart-client-key.php';

if ( ! class_exists( 'SUCSnelstartClient' ) ) {
	/**
	 * Snelstart Client class
	 *
	 * @class SUCSnelstartClient
	 */
	class SUCSnelstartClient extends SUCAPIClient {

		private string $snelstart_key;
		private string $prefix = "https://b2bapi.snelstart.nl/v2/";
		private string $subscription_key;

		public function __construct(string $snelstart_key, string $subscription_key, ?SUCAPIAuthClient $auth_client, int $requests_timeout=45) {
			parent::__construct( isset( $auth_client ) ? $auth_client : new SUCSnelstartClientKey(), $requests_timeout=$requests_timeout);
			$this->snelstart_key = $snelstart_key;
			$this->requests_timeout = $requests_timeout;
			$this->subscription_key = $subscription_key;
		}

		private function _auth_headers(): array {
			if ( ! isset( $this->auth_manager ) ) {
				return array("Ocp-Apim-Subscription-Key" => $this->subscription_key);
			}
			else {
				return array("Authorization" => "Bearer ". $this->auth_manager->get_access_token(), "Ocp-Apim-Subscription-Key" => $this->subscription_key);
			}
		}

		/**
		 * @throws SUCSnelstartAPIException
		 */
		private function _internal_call(string $method, string $url, ?string $payload, array $params): array {
			$args = array("params" => $params);
			if ( ! ( str_starts_with( $url, 'http' ) ) ) {
				$url = $this->prefix . $url;
			}
			$headers = $this->_auth_headers();

			if ( isset( $args["params"]["content_type"] ) ) {
				$headers['Content-Type'] = $args["params"]["content_type"];
				unset( $args["params"]["content_type"] );
			}
			else {
				$headers["Content-Type"] = "application/json";
			}

			if ( isset( $payload ) ) {
				$args["body"] = json_encode($payload);
			}

			$args["headers"] = $headers;
			$args["timeout"] = $this->requests_timeout;

			$response = wp_remote_get( $url, $args );

			if ( is_wp_error($response) ) {
				try {
					$msg = json_decode( wp_remote_retrieve_body( $response ), true )['message'];
				} catch (Exception $e) {
					$msg = "error";
				}
				$response->get_error_message();
				throw new SUCSnelstartAPIException(
					wp_remote_retrieve_response_code( $response ),
					-1,
					$url . ":\n " . $msg,
					null,
					wp_remote_retrieve_headers( $response ),
				);
			}
			else  {
				return json_decode( wp_remote_retrieve_body( $response ), true );
			}
		}

		/**
		 * @throws SUCSnelstartAPIException
		 */
		private function _get(string $url, ?array $args, ?string $payload): array {
			if ( ! isset( $args) ) {
				$args = array();
			}
			return $this->_internal_call("GET", $url, $payload, $args);
		}

		/**
		 * @throws SUCSnelstartAPIException
		 */
		private function _post(string $url, ?array $args, ?string $payload): array {
			if ( ! isset( $args) ) {
				$args = array();
			}
			return $this->_internal_call("POST", $url, $payload, $args);
		}

		/**
		 * @throws SUCSnelstartAPIException
		 */
		private function _delete(string $url, ?array $args, ?string $payload): array {
			if ( ! isset( $args) ) {
				$args = array();
			}
			return $this->_internal_call("DELETE", $url, $payload, $args);
		}

		/**
		 * @throws SUCSnelstartAPIException
		 */
		private function _put(string $url, ?array $args, ?string $payload): array {
			if ( ! isset( $args) ) {
				$args = array();
			}
			return $this->_internal_call("PUT", $url, $payload, $args);
		}

		public function bankboekingen() {
			return $this->_get('bankboekingen', null, null);
		}
	}
}
