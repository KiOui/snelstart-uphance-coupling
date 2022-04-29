<?php
/**
 * Credit Note synchronizer.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/synchronizers/interface-sucsynchronisable.php';
include_once SUC_ABSPATH . 'includes/uphance/class-sucuphancecreditnotetetrieve.php';
include_once SUC_ABSPATH . 'includes/snelstart/class-sucbtw.php';

if ( ! class_exists("SUCCreditNoteSynchronizer") ) {
	class SUCCreditNoteSynchronizer extends Synchronisable {

		/**
		 * BTW Converter.
		 *
		 * @var ?SUCBTW
		 */
		private ?SUCBTW $btw_converter = null;

		/**
		 * @throws SUCAPIException
		 */
		private function get_btw_converter(): SUCBTW {
			if ( is_null( $this->btw_converter ) ) {
				$this->btw_converter = new SUCBTW( suc_get_current_btw_soorten( $this->snelstart_client ) );
			}
			return $this->btw_converter;
		}

		/**
		 * Get the credit notes to sync.
		 *
		 * @throws SUCAPIException on Exception with the API.
		 */
		private function get_credit_notes_to_sync( ?string $credit_note_from, ?int $max_to_sync ): array {
			if ( isset( $credit_note_from ) ) {
				// $credit_notes = $uphance_client->credit_notes( $credit_note_from )->result;
				// Workaround because Uphance does not support getting from a certain credit note number.
				$credit_note_retriever = new SUCUphanceCreditNoteRetriever( $this->uphance_client );
				$credit_notes = array(
					'credit_notes' => $credit_note_retriever->get_next_credit_notes( $credit_note_from, $max_to_sync ),
				);
			} else {
				$credit_notes = $this->uphance_client->credit_notes()->result;
			}

			if ( isset( $max_to_sync ) ) {
				if ( $max_to_sync === 0 ) {
					return array();
				} else {
					$credit_notes = array_slice( $credit_notes, 0, $max_to_sync );
				}
			}

			return $credit_notes['credit_notes'];
		}

		/**
		 * 1. Get settings
		 * 2. Get all credit notes
		 * ---- for all credit notes
		 * 1. Get the credit note customer from uphance
		 * 2. Alter the line quantities
		 * 3. Convert grootboek regels and BTW line items
		 * 4. Get relatie in Snelstart
		 * 5. Add a verkoopboeking
		 *
		 *
		 *
		 * @throws SUCAPIException
		 */
		public function run(): bool {
			$settings_manager = SUCSettings::instance()->get_manager();

			$credit_note_from                 = $settings_manager->get_value_by_setting_id('uphance_synchronise_credit_notes_from');
			$max_to_sync                      = $settings_manager->get_value_by_setting_id('max_credit_notes_to_synchronize');

			$credit_note_synchronizer = new SUCSnelstartSynchronizer( $this->snelstart_client, $snelstart_grootboekcode_btw_hoog, $snelstart_grootboekcode_btw_geen );

			$credit_notes = $this->get_credit_notes_to_sync( $credit_note_from, $max_to_sync );

			if ( count( $credit_notes ) > 0 ) {

				$amount_of_credit_notes = count( $credit_notes );

				for ( $i = 0; $i < $amount_of_credit_notes; $i ++ ) {
					try {
						$credit_note_converted = $this->setup_credit_note_for_synchronisation( $credit_notes[ $i ] );
					} catch (Exception $e) {
						// Create error log
					}
				}
				$credit_note_synchronizer->sync_credit_notes_to_snelstart( $credit_notes );
				$latest_credit_note                                = $credit_notes[ count( $credit_notes ) - 1 ]['id'];
				$settings_manager->set_value_by_setting_id('uphance_synchronise_credit_notes_from', $latest_credit_note);
			}
			return true;
		}

		/**
		 * @throws SUCAPIException
		 */
		private function setup_credit_note_for_synchronisation( array $credit_note ): array {
			$order = $this->uphance_client->orders( $credit_note['order_number'] )->result['sales_orders'][0];
			$credit_note['customer'] = $this->uphance_client->customer_by_id( $order['company_id'] )['customer'];
			$credit_note['items_total'] = $credit_note['items_total'] * -1;
			$credit_note['items_tax'] = $credit_note['items_total'] * -1;
			$credit_note['subtotal'] = $credit_note['subtotal'] * -1;
			$credit_note['total_tax'] = $credit_note['total_tax'] * -1;
			$credit_note['grand_total'] = $credit_note['grand_total'] * -1;
			$amount_of_line_items = count( $credit_note['line_items'] );
			for ( $line_item_index = 0; $line_item_index < $amount_of_line_items; $line_item_index++ ) {
				$credit_note['line_items'][ $line_item_index ]['unit_tax'] = $credit_note['line_items'][ $line_item_index ]['unit_tax'] * -1;
				$credit_note['line_items'][ $line_item_index ]['unit_price'] = $credit_note['line_items'][ $line_item_index ]['unit_price'] * -1;
				$credit_note['line_items'][ $line_item_index ]['original_price'] = $credit_note['line_items'][ $line_item_index ]['original_price'] * -1;
			}
			if ( isset( $credit_note['freeform_amount'] ) && 0 != $credit_note['freeform_amount'] ) {
				// Add a fake line item for the freeform amount.
				$computed_tax_level = $credit_note['freeform_tax'] / ( $credit_note['freeform_amount'] / 100 );
				$credit_note['line_items'][] = array(
					'id' => -1,
					'product_id' => -1,
					'product_name' => $credit_note['freeform_description'],
					'tax_level' => suc_format_number( $computed_tax_level, 1 ),
					'unit_price' => $credit_note['freeform_amount'] * -1,
					// Fake line_quantities to make the synchronizer pick up.
					'line_quantities' => array(
						array(
							'quantity' => 1,
						),
					),
				);
			}
			return $credit_note;
		}

		/**
		 * Synchronize a credit note to Snelstart.
		 *
		 * @param array $credit_note the credit note to synchronize.
		 *
		 * @return bool true if synchonization succeeded, false otherwise.
		 * @throws SUCAPIException
		 * @throws Exception
		 */
		public function sync_credit_note_to_snelstart( array $credit_note ): bool {
			$credit_note_id = $credit_note['id'];
			$customer = $credit_note['customer'];
			$grootboek_regels = suc_construct_order_line_items( $credit_note['line_items'] );
			$btw_regels                  = suc_construct_btw_line_items( $credit_note['line_items'] );
			$snelstart_relatie_for_order = get_or_create_relatie_with_name( $this->snelstart_client, $customer['name'] );

			if ( ! isset( $snelstart_relatie_for_order ) ) {
				$name = $customer['name'];
				SUCLogging::instance()->write( sprintf( __( 'Failed to synchronize %1$s because customer %2$s could not be found and created in Snelstart.', 'snelstart-uphance-coupling' ), $credit_note_id, $name ) );
				return false;
			}

			try {
				$credit_note_date = new DateTime( $credit_note['created_at'] );
			} catch ( Exception $e ) {
				SUCLogging::instance()->write( sprintf( __( 'Failed to get date for %1$s, using datetime now.', 'snelstart-uphance-coupling' ), $credit_note_id ) );
				$credit_note_date = new DateTime( 'now' );
			}

			$this->snelstart_client->add_verkoopboeking( $credit_note['credit_note_number'], $snelstart_relatie_for_order['id'], suc_format_number( $credit_note['grand_total'] ), 0, $grootboek_regels, $btw_regels, $credit_note_date );
			SUCLogging::instance()->write( sprintf( __( 'Synchronization of credit note %s succeeded.', 'snelstart-uphance-coupling' ), $credit_note_id ) );
			return true;
		}

		public function synchronize_one( string $id ) {

		}
	}
}