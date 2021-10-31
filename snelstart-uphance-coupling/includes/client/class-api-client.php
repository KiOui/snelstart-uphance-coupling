<?php
/**
 * API Client class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-api-exception.php';
include_once SUC_ABSPATH . 'includes/client/class-api-auth-client.php';

abstract class SUCAPIClient {

	protected string $prefix = "";
	protected SUCAPIAuthClient $auth_manager;
	protected int $requests_timeout;

	public function __construct(SUCAPIAuthClient $auth_manager, int $requests_timeout=45) {
		$this->requests_timeout = $requests_timeout;
		$this->auth_manager = $auth_manager;
	}

	protected function _auth_headers(): array {
		if ( ! isset( $this->auth_manager ) ) {
			return array();
		}
		else {
			return array("Authorization" => "Bearer ". $this->auth_manager->get_access_token());
		}
	}

	/**
	 * @throws SUCAPIException
	 */
	protected function _internal_call(string $method, string $url, ?array $payload, array $params): array {
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
			$args["body"] = json_encode( $payload );
		}

		$args["headers"] = $headers;
		$args["timeout"] = $this->requests_timeout;
		$args["method"] = $method;

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error($response) || (wp_remote_retrieve_response_code( $response ) < 200 && wp_remote_retrieve_response_code( $response ) > 300 ) ) {
			try {
				$msg = json_decode( wp_remote_retrieve_body( $response ), true )['message'];
			} catch (Exception $e) {
				$msg = "error";
			}
			throw new SUCAPIException(
				wp_remote_retrieve_response_code( $response ),
				-1,
				$url . ":\n " . $msg,
				null,
				wp_remote_retrieve_headers( $response )->getAll(),
			);
		}
		else  {
			return json_decode( wp_remote_retrieve_body( $response ), true );
		}
	}

	/**
	 * @throws SUCAPIException
	 */
	protected function _get(string $url, ?array $args, ?array $payload): array {
		if ( ! isset( $args) ) {
			$args = array();
		}
		return $this->_internal_call("GET", $url, $payload, $args);
	}

	/**
	 * @throws SUCAPIException
	 */
	protected function _post(string $url, ?array $args, ?array $payload): array {
		if ( ! isset( $args) ) {
			$args = array();
		}
		return $this->_internal_call("POST", $url, $payload, $args);
	}

	/**
	 * @throws SUCAPIException
	 */
	protected function _delete(string $url, ?array $args, ?array $payload): array {
		if ( ! isset( $args) ) {
			$args = array();
		}
		return $this->_internal_call("DELETE", $url, $payload, $args);
	}

	/**
	 * @throws SUCAPIException
	 */
	protected function _put(string $url, ?array $args, ?array $payload): array {
		if ( ! isset( $args) ) {
			$args = array();
		}
		return $this->_internal_call("PUT", $url, $payload, $args);
	}
}
