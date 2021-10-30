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

		public int $http_status;
		public ?string $reason;
		public array $headers;

		public function __construct(int $http_status, int $code, string $message, ?string $reason, ?array $headers) {
			parent::__construct($message, $code);
			$this->http_status = $http_status;
			$this->reason = $reason;
			if ( empty( $headers ) ) {
				$headers = array();
			}
			$this->headers = $headers;
		}

		public function __toString(): string {
			return "http status: " . $this->http_status . ", code: " . $this->code . "-" . $this->message . ", reason: " . $this->reason;
		}

	}
}
