<?php
/**
 * Uphance Invoice Searcher
 *
 * @package snelstart-uphance-coupling
 */

if ( ! class_exists( 'SUCUphanceInvoiceSearcher' ) ) {
	/**
	 * Uphance Invoice Searcher class.
	 *
	 * @class SUCUpanceInvoiceSearcher
	 */
	class SUCUphanceInvoiceSearcher {

		/**
		 * The invoices already requested by the invoice searcher.
		 *
		 * @var array
		 */
		private array $invoices = array();

		/**
		 * The Uphance client to use for the search requests.
		 *
		 * @var SUCUphanceClient
		 */
		private SUCUphanceClient $uphance_client;

		/**
		 * The current page that is being loaded for an invoice search.
		 *
		 * @var int
		 */
		private int $current_page = 1;

		/**
		 * Whether new results are not fetchable anymore.
		 *
		 * @var bool
		 */
		private bool $no_results_fetchable = false;

		/**
		 * Construct an SUCUphanceInvoiceSearcher.
		 *
		 * @param SUCUphanceClient $uphance_client the uphance client to use for the invoice searcher.
		 */
		public function __construct( SUCUphanceClient $uphance_client ) {
			$this->uphance_client = $uphance_client;
		}

		/**
		 * Load more invoices in the $invoices array.
		 *
		 * @return bool true when more invoices were loaded, false if all invoices are already loaded.
		 */
		private function more_invoices(): bool {
			if ( ! $this->no_results_fetchable ) {
				try {
					$results = $this->uphance_client->invoices( null, $this->current_page )->result;
				} catch ( SUCAPIException $e ) {
					SUCLogging::instance()->write( 'The following exception occurred while retrieving invoice data from Uphance:' );
					SUCLogging::instance()->write( $e );
					$this->no_results_fetchable = true;
					return false;
				}
				if ( count( $results['invoices'] ) === 0 ) {
					$this->no_results_fetchable = true;
					return false;
				}
				$this->invoices = array_merge( $this->invoices, $results['invoices'] );
				$this->current_page = $this->current_page + 1;
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Search an invoice number in Uphance.
		 *
		 * This function exists because we can't search for invoice numbers in Uphance via the API. This function traverses
		 * all pages in search of the required invoice number.
		 *
		 * @param int $invoice_number_to_search the invoice number to search.
		 *
		 * @return ?array an array with the invoice, or null when it was not found.
		 */
		public function search_invoice( int $invoice_number_to_search ): ?array {
			$amount_of_invoices = count( $this->invoices );
			for ( $i = 0; $i <= $amount_of_invoices; $i++ ) {
				if ( $i === $amount_of_invoices ) {
					if ( $this->more_invoices() ) {
						--$i;
						$amount_of_invoices = count( $this->invoices );
					}
				} else {
					if ( $this->invoices[ $i ]['invoice_number'] === $invoice_number_to_search ) {
						return $this->invoices[ $i ];
					}
				}
			}
			return null;
		}
	}
}
