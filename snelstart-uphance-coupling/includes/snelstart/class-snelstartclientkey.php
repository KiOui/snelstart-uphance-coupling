<?php
/**
 * Snelstart Client Key
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/snelstart/class-snelstartapiexception.php';

/**
 * @throws Exception
 */
function _ensure_value($value, $option_key) {
	if ( isset( $value ) ) {
		return $value;
	}
	else if ( isset( get_option('suc_settings')[$option_key] ) ) {
		return $option_key;
	}
	else {
		throw new Exception("Value of " . $option_key . " should be set");
	}
}

function is_token_expired(array $token_info): bool {
	$now = time();
    return $token_info["expires_at"] - $now < 60;
}

function _get_authentication_body($client_key) {
	return "grant_type=clientkey&clientkey=" . $client_key;
}

if ( ! class_exists( 'SUCSnelstartClientKey' ) ) {
	/**
	 * Snelstart OAuth class
	 *
	 * @class SUCSnelstartClientKey
	 */
	class SUCSnelstartClientKey {

		private string $_client_key;
		private string $_token_url = "https://auth.snelstart.nl/b2b/token";
		private ?array $token_info;

		/**
		 * @throws Exception
		 */
		public function __construct($client_key) {
			$this->_client_key = _ensure_value($client_key, 'snelstart_client_key');
		}

		public function get_cached_token() {
			$token_info = get_option('suc_token_info', null);
			if ( isset( $token_info ) && $this->is_token_expired( $token_info ) ) {
				return null;
			}
			return $token_info;
		}

		private function _save_token_info(?array $token_info) {
			if ( isset( $token_info) ) {
				update_option( 'suc_token_info', $token_info );
			}
			else {
				delete_option( 'suc_token_info' );
			}
		}

		/**
		 * @throws SUCSnelstartAPIException
		 */
		public function get_access_token(bool $check_cache=true): string {
			if ($check_cache) {
				$token_info = $this->get_cached_token();
				if ( isset( $token_info ) && ! $this->is_token_expired( $token_info ) ) {
					return $token_info['access_token'];
				}
			}

			if ( isset( $this->token_info ) && ! $this->is_token_expired( $this->token_info ) ) {
				return $this->token_info["access_token"];
			}

			$token_info = $this->_request_access_token();
			$token_info = $this->_add_custom_values_to_token_info($token_info);
			$this->token_info = $token_info;
			$this->_save_token_info( $token_info );
			return $this->token_info["access_token"];
		}

		private function _add_custom_values_to_token_info(array $token_info): array {
			$token_info['expires_at'] = time() + $token_info['expires_in'];
			return $token_info;
		}

		public function is_token_expired(array $token_info): bool {
			return is_token_expired($token_info);
		}

		/**
		 * @throws SUCSnelstartAPIException
		 */
		private function _request_access_token(): array {
			$headers = array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			);

			$body = _get_authentication_body($this->_client_key);

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
				throw new SUCSnelstartAPIException(
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
