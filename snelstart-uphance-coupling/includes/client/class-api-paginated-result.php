<?php
/**
 * Paginated Result class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCAPIPaginatedResult' ) ) {
	/**
	 * Uphance Client class
	 *
	 * @class SUCAPIPaginatedResult
	 */
	class SUCAPIPaginatedResult {

		public array $result;
		public int $current_page;
		public ?string $next_page;
		public ?string $previous_page;
		public int $total_pages;
		public int $total_count;

		public function __construct( array $result ) {
			$this->current_page = $result['meta']['current_page'];
			$this->next_page = $result['meta']['next_page'];
			$this->previous_page = $result['meta']['previous_page'];
			$this->total_pages = $result['meta']['total_pages'];
			$this->total_count = $result['meta']['total_count'];
			unset( $result['meta'] );
			$this->result = $result;
		}
	}
}
