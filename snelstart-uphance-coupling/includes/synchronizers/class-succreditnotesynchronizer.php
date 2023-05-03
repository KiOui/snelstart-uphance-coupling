<?php
/**
 * Credit Note synchronizer.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/synchronizers/class-sucsynchronisable.php';
include_once SUC_ABSPATH . 'includes/snelstart/class-sucbtw.php';

if ( ! class_exists( 'SUCCreditNoteSynchronizer' ) ) {
	/**
	 * SUC Credit Note Synchronizer.
	 *
	 * @class SUCCreditNoteSynchronizer
	 */
	class SUCCreditNoteSynchronizer extends SUCSynchronisable {

		/**
		 * Type of this class.
		 *
		 * @var string
		 */
		public static string $type = 'credit-note';

		/**
		 * BTW Converter.
		 *
		 * @var ?SUCBTW
		 */
		private ?SUCBTW $btw_converter = null;

		/**
		 * Credit notes to synchronize.
		 *
		 * @var array
		 */
		private array $credit_notes;

		/**
		 * Get the credit notes to sync.
		 *
		 * @throws SUCAPIException On Exception with the API.
		 */
		private function get_credit_notes_to_sync( ?string $credit_note_from, ?int $max_to_sync ): array {
			if ( isset( $credit_note_from ) ) {
				$credit_notes = $this->uphance_client->credit_notes( $credit_note_from )->result;
			} else {
				$credit_notes = $this->uphance_client->credit_notes()->result;
			}

			$credit_notes = $credit_notes['credit_notes'];

			if ( isset( $max_to_sync ) ) {
				if ( 0 === $max_to_sync ) {
					return array();
				} else {
					$credit_notes = array_slice( $credit_notes, 0, $max_to_sync );
				}
			}

			return $credit_notes;
		}

		public static function get_url( int $credit_note_id ) {
			return sprintf( 'https://app.uphance.com/credit_notes/%d', $credit_note_id );
		}

		/**
		 * Synchronize credit notes to Snelstart.
		 *
		 * @return void
		 */
		public function run(): void {
			$amount_of_credit_notes = count( $this->credit_notes );

			for ( $i = 0; $i < $amount_of_credit_notes; $i ++ ) {
				try {
					$credit_note_converted = $this->setup_credit_note_for_synchronisation( $this->credit_notes[ $i ] );
					$this->sync_credit_note_to_snelstart( $credit_note_converted );
					SUCSynchronizedObjects::create_synchronized_object(
						intval( $this->credit_notes[ $i ]['id'] ),
						$this::$type,
						true,
						$this::get_url( intval( $this->credit_notes[ $i ]['id'] ) ),
						null,
						[
							'Credit note number' => $this->credit_notes[ $i ]['credit_note_number'],
						],
					);
				} catch ( Exception $e ) {
					if ( get_class( $e ) === 'SUCAPIException' ) {
						$message = $e->get_message();
					} else {
						$message = $e->__toString();
					}
					SUCSynchronizedObjects::create_synchronized_object(
						intval( $this->credit_notes[ $i ]['id'] ),
						$this::$type,
						false,
						$this::get_url( intval( $this->credit_notes[ $i ]['id'] ) ),
						$message,
						[
							'Credit note number' => $this->credit_notes[ $i ]['credit_note_number'],
						],
					);
					$error_log = new SUCErrorLogging();
					$error_log->set_error( $e . esc_html( sprintf( '\nURL: https://app.uphance.com/credit_notes/%d', $this->credit_notes[ $i ]['id'] ) ), 'synchronize-credit-note', self::$type, $this->credit_notes[ $i ]['id'] );
				}
			}
		}

		/**
		 * Setup a credit note for synchronization to Snelstart.
		 *
		 * @throws SUCAPIException On Exception with the API.
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
		 * @return void
		 * @throws SUCAPIException|Exception On Exception with the API or something else.
		 */
		public function sync_credit_note_to_snelstart( array $credit_note ): void {
			$credit_note_id = $credit_note['id'];
			$customer = $credit_note['customer'];
			$grootboek_regels = suc_construct_order_line_items( $credit_note['line_items'], $this->btw_converter );
			$btw_regels                  = suc_construct_btw_line_items( $credit_note['line_items'] );
			$snelstart_relatie_for_order = get_or_create_relatie_with_name( $this->snelstart_client, $customer );

			if ( ! isset( $snelstart_relatie_for_order ) ) {
				$name = $customer['name'];
				throw new Exception( __( 'Failed to synchronize %1$s because customer %2$s could not be found and created in Snelstart.', 'snelstart-uphance-coupling' ), $credit_note_id, $name );
			}

			try {
				$credit_note_date = new DateTime( $credit_note['created_at'] );
			} catch ( Exception $e ) {
				$credit_note_date = new DateTime( 'now' );
			}

			$this->snelstart_client->add_verkoopboeking( $credit_note['credit_note_number'], $snelstart_relatie_for_order['id'], suc_format_number( $credit_note['grand_total'] ), 0, $grootboek_regels, $btw_regels, $credit_note_date );
		}

		/**
		 * Synchronize one credit note to Snelstart.
		 *
		 * @param string $id the ID of the credit note to synchronize.
		 *
		 * @return void
		 */
		public function synchronize_one( string $id ): void {
			// TODO: implement this method.
		}

		/**
		 * Setup this class.
		 *
		 * @throws SUCAPIException|Exception When settings are not configured or on Exception with the API.
		 */
		public function setup(): void {
			$manager          = SUCSettings::instance()->get_settings();
			$credit_note_from = $manager->get_value( 'uphance_synchronise_credit_notes_from' );
			$max_to_sync      = $manager->get_value( 'max_credit_notes_to_synchronize' );
			$grootboekcode_btw_hoog = $manager->get_value( 'snelstart_grootboekcode_btw_hoog' );
			$grootboekcode_btw_geen = $manager->get_value( 'snelstart_grootboekcode_btw_geen' );
			if ( ! isset( $grootboekcode_btw_hoog ) || ! isset( $grootboekcode_btw_geen ) ) {
				throw new Exception( 'Grootboekcodes must be set in order to use Credit note synchronizer' );
			}

			$tax_types = $this->snelstart_client->btwtarieven();

			$this->btw_converter = new SUCBTW( $grootboekcode_btw_hoog, $grootboekcode_btw_geen, $tax_types );
			$this->credit_notes = $this->get_credit_notes_to_sync( $credit_note_from, $max_to_sync );
		}

		/**
		 * Update the WordPress settings.
		 *
		 * @return void
		 */
		public function after_run(): void {
			if ( count( $this->credit_notes ) > 0 ) {
				$latest_credit_note = $this->credit_notes[ count( $this->credit_notes ) - 1 ]['id'];

				$settings_manager = SUCSettings::instance()->get_settings();
				$settings_manager->set_value( 'uphance_synchronise_credit_notes_from', $latest_credit_note );
			}
		}

		/**
		 * Whether this synchronizer is enabled.
		 *
		 * @return bool True when this synchronizer is enabled.
		 */
		public function enabled(): bool {
			$manager          = SUCSettings::instance()->get_settings();
			return $manager->get_value( 'synchronize_credit_notes_to_snelstart' );
		}
	}
}
