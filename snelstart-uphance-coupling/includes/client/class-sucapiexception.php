<?php
/**
 * API Exception
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCAPIException' ) ) {
	/**
	 * API Exception class
	 *
	 * @class SUCAPIException
	 */
	class SUCAPIException extends Exception {

		/**
		 * HTTP status code of Exception.
		 *
		 * @var int
		 */
		public int $http_status;

		/**
		 * Reason for Exception.
		 *
		 * @var string|null
		 */
		public ?string $reason;

		/**
		 * Headers for Exception.
		 *
		 * @var array
		 */
		public array $headers;

		/**
		 * Constructor.
		 *
		 * @param int         $http_status the HTTP status code.
		 * @param int         $code the Error code.
		 * @param string      $message the message.
		 * @param string|null $reason reason for Exception.
		 * @param array|null  $headers the headers of the request.
		 */
		public function __construct( int $http_status, int $code, string $message, ?string $reason, ?array $headers ) {
			parent::__construct( $message, $code );
			$this->http_status = $http_status;
			$this->reason = $reason;
			if ( empty( $headers ) ) {
				$headers = array();
			}
			$this->headers = $headers;
		}

		/**
		 * Convert to string.
		 *
		 * @return string this object in string format
		 */
		public function __toString(): string {
			return 'http status: ' . $this->http_status . ', code: ' . $this->code . ' - ' . $this->message . ', reason: ' . $this->reason;
		}

		/**
		 * Get the message.
		 *
		 * @return string
		 */
		public function get_message(): string {
			return strval( $this->message );
		}

	}
}
