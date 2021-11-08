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

	/**
	 * Token info setting name.
	 *
	 * @var string
	 */
	protected string $TOKEN_INFO_SETTING;

	/**
	 * Token info.
	 *
	 * @var array|null
	 */
	protected ?array $token_info;

	/**
	 * Request an access token.
	 *
	 * @return array access token
	 * @throws SUCAPIException 
	 */
	public abstract function request_access_token(): array;

	private function _save_token_info(?array $token_info) {
		if ( isset( $token_info) ) {
			update_option( $this->TOKEN_INFO_SETTING, $token_info );
		}
		else {
			delete_option( $this->TOKEN_INFO_SETTING );
		}
	}

	/**
	 * Get the cached token (if it exists).
	 *
	 * @return array|null the cached token or null when there is no valid cached token
	 */
	public function get_cached_token(): ?array {
		$token_info = get_option( $this->TOKEN_INFO_SETTING, null ) ;
		if ( isset( $token_info ) && $this->is_token_expired( $token_info ) ) {
			return null;
		}
		return $token_info;
	}

	/**
	 * Get an access token.
	 *
	 * @param bool $check_cache whether to check cache
	 *
	 * @return string the access token
	 * @throws SUCAPIException
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

		$token_info = $this->request_access_token();
		$token_info = $this->add_custom_values_to_token_info($token_info);
		$this->token_info = $token_info;
		$this->_save_token_info( $token_info );
		return $this->token_info["access_token"];
	}

	/**
	 * Add custom values to token info string.
	 *
	 * @param array $token_info the token info
	 *
	 * @return array the token info with added values
	 */
	private function add_custom_values_to_token_info(array $token_info): array {
		$token_info['expires_at'] = time() + $token_info['expires_in'];
		return $token_info;
	}

	/**
	 * Check if token is expired.
	 *
	 * @param array $token_info the token info
	 *
	 * @return bool true when token info is expired, false otherwise
	 */
	public function is_token_expired(array $token_info): bool {
		$now = time();
		return $token_info["expires_at"] - $now < 60;
	}
}

