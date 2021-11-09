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
	 * Paginated result class.
	 *
	 * @class SUCAPIPaginatedResult
	 */
	class SUCAPIPaginatedResult {

		/**
		 * Result.
		 *
		 * @var array
		 */
		public array $result;

		/**
		 * Current page number.
		 *
		 * @var int
		 */
		public int $current_page;

		/**
		 * Next page URL.
		 *
		 * @var string|null
		 */
		public ?string $next_page;

		/**
		 * Previous page URL.
		 *
		 * @var string|null
		 */
		public ?string $previous_page;

		/**
		 * Total amount of pages.
		 *
		 * @var int
		 */
		public int $total_pages;

		/**
		 * Total amount of results.
		 *
		 * @var int
		 */
		public int $total_count;

		/**
		 * Constructor.
		 *
		 * @param array $result the paginated result of the request.
		 */
		public function __construct( array $result ) {
			$this->current_page = $result['meta']['current_page'];
			$this->next_page = $result['meta']['next_page'];
			$this->previous_page = $result['meta']['prev_page'];
			$this->total_pages = $result['meta']['total_pages'];
			$this->total_count = $result['meta']['total_count'];
			unset( $result['meta'] );
			$this->result = $result;
		}
	}
}
