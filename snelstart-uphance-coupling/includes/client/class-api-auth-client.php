<?php
/**
 * API Auth Client
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Auth client
 *
 * @class SUCAPIAuthClient
 */
abstract class SUCAPIAuthClient {

	protected string $TOKEN_INFO_SETTING;
	protected ?array $token_info;

	public abstract function request_access_token(): array;

	private function _save_token_info(?array $token_info) {
		if ( isset( $token_info) ) {
			update_option( $this->TOKEN_INFO_SETTING, $token_info );
		}
		else {
			delete_option( $this->TOKEN_INFO_SETTING );
		}
	}

	public function get_cached_token(): ?array {
		$token_info = get_option( $this->TOKEN_INFO_SETTING, null ) ;
		if ( isset( $token_info ) && $this->is_token_expired( $token_info ) ) {
			return null;
		}
		return $token_info;
	}

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

		$token_info = $this->request_access_token();
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
		$now = time();
		return $token_info["expires_at"] - $now < 60;
	}
}

