<?php
/**
 * Uphance Credit Note Retriever
 *
 * @package snelstart-uphance-coupling
 */

if ( ! class_exists( 'SUCUphanceCreditNoteRetriever' ) ) {
	/**
	 * Uphance Invoice Credit Note Retriever class.
	 *
	 * @class SUCUphanceCreditNoteRetriever
	 */
	class SUCUphanceCreditNoteRetriever {

		/**
		 * The credit notes already requested by the invoice searcher.
		 *
		 * @var array
		 */
		private array $credit_notes = array();

		/**
		 * The Uphance client to use for the search requests.
		 *
		 * @var SUCUphanceClient
		 */
		private SUCUphanceClient $uphance_client;

		/**
		 * The current page that is being loaded for a credit note retrieval.
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
		 * Construct an SUCUphanceCreditNoteRetriever.
		 *
		 * @param SUCUphanceClient $uphance_client the uphance client to use for the invoice searcher.
		 */
		public function __construct( SUCUphanceClient $uphance_client ) {
			$this->uphance_client = $uphance_client;
		}

		/**
		 * Load more credit notes in the $credit_notes array.
		 *
		 * @return bool true when more invoices were loaded, false if all invoices are already loaded.
		 */
		private function more_credit_notes(): bool {
			if ( ! $this->no_results_fetchable ) {
				try {
					$results = $this->uphance_client->credit_notes( null, $this->current_page )->result;
				} catch ( SUCAPIException $e ) {
					SUCLogging::instance()->write( 'The following exception occurred while retrieving credit note data from Uphance:' );
					SUCLogging::instance()->write( $e );
					$this->no_results_fetchable = true;
					return false;
				}
				if ( count( $results['credit_notes'] ) === 0 ) {
					$this->no_results_fetchable = true;
					return false;
				}
				$this->credit_notes = array_merge( $this->credit_notes, $results['credit_notes'] );
				$this->current_page = $this->current_page + 1;
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Get the next x credit notes from a certain credit note.
		 *
		 * @param ?int $from_credit_note_id retrieve credit notes from this number onwards.
		 * @param ?int $amount retrieve this amount of credit notes.
		 *
		 * @return array list of credit notes.
		 */
		public function get_next_credit_notes( ?int $from_credit_note_id, ?int $amount ): array {
			$return_value = array();

			$collect_credit_notes = ! isset( $from_credit_note_id );
			$amount_still_to_collect = $amount;

			$amount_of_credit_notes = count( $this->credit_notes );
			for ( $i = 0; $i <= $amount_of_credit_notes; $i++ ) {

				if ( isset( $amount_still_to_collect ) && 0 >= $amount_still_to_collect ) {
					return $return_value;
				}

				if ( $i === $amount_of_credit_notes ) {
					if ( $this->more_credit_notes() ) {
						--$i;
						$amount_of_credit_notes = count( $this->credit_notes );
					}
				} else {
					if ( $collect_credit_notes ) {
						$return_value[] = $this->credit_notes[ $i ];
						if ( isset( $amount_still_to_collect ) ) {
							--$amount_still_to_collect;
						}
					}

					if ( ! $collect_credit_notes && $this->credit_notes[ $i ]['id'] === $from_credit_note_id ) {
						$collect_credit_notes = true;
					}
				}
			}
			return $return_value;
		}
	}
}
