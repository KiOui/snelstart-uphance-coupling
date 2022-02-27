<?php
/**
 * API Client class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-sucapiexception.php';
include_once SUC_ABSPATH . 'includes/client/class-sucapiauthclient.php';

/**
 * API client
 *
 * @class SUCAPIClient
 */
abstract class SUCAPIClient {

	/**
	 * Prefix of API URL.
	 *
	 * @var string
	 */
	protected string $prefix = '';

	/**
	 * API Auth manager for retrieving access tokens.
	 *
	 * @var SUCAPIAuthClient
	 */
	protected SUCAPIAuthClient $auth_manager;

	/**
	 * Timeout for requests.
	 *
	 * @var int
	 */
	protected int $requests_timeout;

	/**
	 * Constructor.
	 *
	 * @param SUCAPIAuthClient $auth_manager the auth manager to use.
	 * @param int              $requests_timeout request timeout to use.
	 */
	public function __construct( SUCAPIAuthClient $auth_manager, int $requests_timeout = 45 ) {
		$this->requests_timeout = $requests_timeout;
		$this->auth_manager = $auth_manager;
	}

	/**
	 * Reset Auth token info.
	 *
	 * @return void
	 */
	public function reset_auth_token() {
		$this->auth_manager->reset_token();
	}

	/**
	 * Get Authentication headers for requests.
	 *
	 * @return array the authentication headers.
	 * @throws SUCAPIException On failed retrieval of access token.
	 */
	protected function auth_headers(): array {
		if ( ! isset( $this->auth_manager ) ) {
			return array();
		} else {
			return array( 'Authorization' => 'Bearer ' . $this->auth_manager->get_access_token() );
		}
	}

	/**
	 * Get error message from request body.
	 *
	 * @param string $body the request body.
	 *
	 * @return string the retrieved error message.
	 */
	public static function get_error_message( string $body ): string {
		try {
			$decoded_json = json_decode( $body, true );
		} catch ( Exception $e ) {
			return 'error';
		}
		if ( gettype( $decoded_json ) === 'array' ) {
			if ( key_exists( 'message', $decoded_json ) ) {
				return $decoded_json['message'];
			} else if ( key_exists( 0, $decoded_json ) && key_exists( 'message', $decoded_json[0] ) ) {
				return $decoded_json[0]['message'];
			} else {
				return 'error';
			}
		} else if ( gettype( $decoded_json ) === 'string' ) {
			return $decoded_json;
		} else {
			return 'error';
		}
	}

	/**
	 * Create an Exception from a response.
	 *
	 * @param array|WP_Error $response the response.
	 * @param string         $url the URL of the request.
	 *
	 * @return SUCAPIException the created exception.
	 */
	protected function make_exception( $response, string $url ): SUCAPIException {
		// TODO: Include body in error message.
		$msg = self::get_error_message( wp_remote_retrieve_body( $response ) );
		return new SUCAPIException(
			wp_remote_retrieve_response_code( $response ),
			-1,
			$url . ":\n " . $msg,
			null,
			gettype( wp_remote_retrieve_headers( $response ) ) === 'array' ? wp_remote_retrieve_headers( $response ) : wp_remote_retrieve_headers( $response )->getAll(),
		);
	}

	/**
	 * Create a querystring from an array of key, value pairs.
	 *
	 * @param array $queries key, value pairs.
	 *
	 * @return string a querystring with encoded key, value pairs as ?key=value&key2=value.
	 */
	protected function create_querystring( array $queries ): string {
		$queries = array_filter(
			$queries,
			function ( $query ) {
				return isset( $query ) && '' !== $query;
			}
		);
		if ( count( $queries ) > 0 ) {
			$return_str = '';
			$prepend = '?';
			foreach ( $queries as $query => $value ) {
				$return_str = $return_str . $prepend . urlencode( $query ) . '=' . urlencode( $value );
				if ( '?' === $prepend ) {
					$prepend = '&';
				}
			}
			return $return_str;
		} else {
			return '';
		}
	}

	/**
	 * Perform an internal API call.
	 *
	 * @param string     $method the method to use.
	 * @param string     $url the URL to call.
	 * @param array|null $payload the payload of the request.
	 * @param array      $params extra request parameters.
	 *
	 * @return array the response of the request.
	 * @throws SUCAPIException On API error.
	 */
	protected function _internal_call( string $method, string $url, ?array $payload, array $params ): array {
		$args = array( 'params' => $params );
		if ( ! ( str_starts_with( $url, 'http' ) ) ) {
			$url = $this->prefix . $url;
		}
		$headers = $this->auth_headers();

		if ( isset( $args['params']['content_type'] ) ) {
			$headers['Content-Type'] = $args['params']['content_type'];
			unset( $args['params']['content_type'] );
		} else {
			$headers['Content-Type'] = 'application/json';
		}

		if ( isset( $payload ) ) {
			$args['body'] = json_encode( $payload );
		}

		$args['headers'] = $headers;
		$args['timeout'] = $this->requests_timeout;
		$args['method'] = $method;

		$response = wp_remote_get( $url, $args );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) < 200 || wp_remote_retrieve_response_code( $response ) > 300 ) {
			throw $this->make_exception( $response, $url );
		} else {
			$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( isset( $decoded ) ) {
				return $decoded;
			} else {
				throw $this->make_exception( $response, $url );
			}
		}
	}

	/**
	 * Perform a GET request.
	 *
	 * @param string     $url the URL to call.
	 * @param array|null $args the arguments for the request.
	 * @param array|null $payload the payload of the request.
	 *
	 * @return array response.
	 * @throws SUCAPIException On API error.
	 */
	protected function _get( string $url, ?array $args, ?array $payload ): array {
		if ( ! isset( $args ) ) {
			$args = array();
		}
		return $this->_internal_call( 'GET', $url, $payload, $args );
	}

	/**
	 * Perform a POST request.
	 *
	 * @param string     $url the URL to call.
	 * @param array|null $args the arguments for the request.
	 * @param array|null $payload the payload of the request.
	 *
	 * @return array response.
	 * @throws SUCAPIException On API error.
	 */
	protected function _post( string $url, ?array $args, ?array $payload ): array {
		if ( ! isset( $args ) ) {
			$args = array();
		}
		return $this->_internal_call( 'POST', $url, $payload, $args );
	}

	/**
	 * Perform a DELETE request.
	 *
	 * @param string     $url the URL to call.
	 * @param array|null $args the arguments for the request.
	 * @param array|null $payload the payload of the request.
	 *
	 * @return array response.
	 * @throws SUCAPIException On API error.
	 */
	protected function _delete( string $url, ?array $args, ?array $payload ): array {
		if ( ! isset( $args ) ) {
			$args = array();
		}
		return $this->_internal_call( 'DELETE', $url, $payload, $args );
	}

	/**
	 * Perform a PUT request.
	 *
	 * @param string     $url the URL to call.
	 * @param array|null $args the arguments for the request.
	 * @param array|null $payload the payload of the request.
	 *
	 * @return array response.
	 * @throws SUCAPIException On API error.
	 */
	protected function _put( string $url, ?array $args, ?array $payload ): array {
		if ( ! isset( $args ) ) {
			$args = array();
		}
		return $this->_internal_call( 'PUT', $url, $payload, $args );
	}
}
