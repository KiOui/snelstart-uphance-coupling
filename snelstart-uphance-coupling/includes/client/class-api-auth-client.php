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

	abstract public function get_cached_token(): ?string;

	/**
	 * @throws SUCSnelstartAPIException
	 */
	abstract public function get_access_token(bool $check_cache=true): string;

	abstract public function is_token_expired(array $token_info): bool;
}

