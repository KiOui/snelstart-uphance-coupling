<?php

if ( ! class_exists( 'SUCUphanceInvoiceSearcher' ) ) {
	class SUCUphanceInvoiceSearcher {

		private array $invoices = array();
		private SUCUphanceClient $uphance_client;
		private int $current_page = 1;
		private bool $no_results_fetchable = false;

		public function __construct( SUCUphanceClient $uphance_client ) {
			$this->uphance_client = $uphance_client;
		}

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
				if ( sizeof( $results['invoices'] ) === 0 ) {
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
		 * @param int $invoice_number_to_search
		 *
		 * @return ?array
		 */
		function search_invoice( int $invoice_number_to_search ): ?array {
			for ( $i = 0; $i <= sizeof( $this->invoices ); $i++ ) {
				if ( $i === sizeof( $this->invoices ) ) {
					if ( $this->more_invoices() ) {
						$i = $i - 1;
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
