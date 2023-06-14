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
include_once SUC_ABSPATH . '/includes/objects/SUCSynchronizedObjects.php';

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
		 * The Uphance client to use for the synchronizer.
		 *
		 * @var SUCUphanceClient
		 */
		protected SUCUphanceClient $uphance_client;

		/**
		 * The Snelstart client to use for the synchronizer.
		 *
		 * @var SUCSnelstartClient
		 */
		protected SUCSnelstartClient $snelstart_client;

		/**
		 * Constructor.
		 *
		 * @param SUCuphanceClient   $uphance_client the Uphance client.
		 * @param SUCSnelstartClient $snelstart_client the Snelstart client.
		 */
		public function __construct( SUCuphanceClient $uphance_client, SUCSnelstartClient $snelstart_client ) {
			$this->uphance_client = $uphance_client;
			$this->snelstart_client = $snelstart_client;
		}

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

		/**
		 * Get the URL of a credit note.
		 *
		 * @param array $object The object to get the URL for.
		 *
		 * @return string A URL pointing to the Uphance resource.
		 */
		public function get_url( array $object ): string {
			return sprintf( 'https://app.uphance.com/credit_notes/%d', $object['id'] );
		}

		/**
		 * Create a synchronized object.
		 *
		 * @param array       $object The object.
		 * @param bool        $succeeded Whether the synchronization succeeded.
		 * @param string      $source The source of the synchronization.
		 * @param string      $method The method of the synchronization.
		 * @param string|null $error_message A possible error message that occurred during synchronization.
		 *
		 * @return void
		 */
		public function create_synchronized_object( array $object, bool $succeeded, string $source, string $method, ?string $error_message ) {
			SUCSynchronizedObjects::create_synchronized_object(
				intval( $object['id'] ),
				$this::$type,
				$succeeded,
				$source,
				$method,
				$this::get_url( $object ),
				$error_message,
				array(
					'Credit note number' => $object['credit_note_number'],
					'Total' => suc_format_number( $object['grand_total'] ),
				),
			);
		}

		/**
		 * Synchronize credit notes to Snelstart.
		 *
		 * @return void
		 */
		public function run(): void {
			$amount_of_credit_notes = count( $this->credit_notes );

			for ( $i = 0; $i < $amount_of_credit_notes; $i ++ ) {
				if ( ! $this->object_already_successfully_synchronized( $this->credit_notes[ $i ]['id'] ) ) {
					try {
						$this->synchronize_one( $this->credit_notes[ $i ] );
						$this->create_synchronized_object( $this->credit_notes[ $i ], true, 'cron', 'create', null );
					} catch ( Exception $e ) {
						if ( get_class( $e ) === 'SUCAPIException' ) {
							$message = $e->get_message();
						} else {
							$message = $e->__toString();
						}
						$this->create_synchronized_object( $this->credit_notes[ $i ], false, 'cron', 'create', $message );
					}
				}
			}
		}

		/**
		 * Set up a credit note for synchronization to Snelstart.
		 *
		 * @param array $credit_note The credit note from Uphance.
		 *
		 * @throws Exception|SUCAPIException On Exception with the API or if Snelstart relatie was not found.
		 */
		private function setup_credit_note_for_synchronisation( array $credit_note ): array {
			$order = $this->uphance_client->orders( $credit_note['order_number'] )->result['sales_orders'][0];
			$customer = $this->uphance_client->customer_by_id( $order['company_id'] )['customer'];
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

			$credit_note_id              = $credit_note['id'];
			$grootboek_regels            = suc_construct_order_line_items( $credit_note['line_items'], $this->btw_converter );
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

			return array(
				'factuurnummer' => $credit_note['credit_note_number'],
				'klant' => array(
					'id' => $snelstart_relatie_for_order['id'],
				),
				'boekingsregels' => $grootboek_regels,
				'factuurbedrag' => suc_format_number( $credit_note['grand_total'] ),
				'betalingstermijn' => 0,
				'factuurdatum' => $credit_note_date->format( 'Y-m-d H:i:s' ),
				'btw' => $btw_regels,
			);
		}

		/**
		 * Synchronize one credit note to Snelstart.
		 *
		 * @param array $to_synchronize the data of the credit note to synchronize.
		 *
		 * @return void
		 *
		 * @throws SUCAPIException On Snelstart API exception.
		 */
		public function synchronize_one( array $to_synchronize ): void {
			$credit_note_converted = $this->setup_credit_note_for_synchronisation( $to_synchronize );
			$snelstart_credit_note = $this->snelstart_client->add_verkoopboeking( $credit_note_converted );
			SUCObjectMapping::create_mapped_object(
				self::$type,
				'uphance',
				'snelstart',
				$to_synchronize['id'],
				$snelstart_credit_note['id'],
			);
		}

		/**
		 * Retrieve an object from Uphance by ID.
		 *
		 * @throws SUCAPIException On Uphance API exception.
		 */
		public function retrieve_object( int $id ): array {
			return $this->uphance_client->credit_note( $id );
		}

		/**
		 * Setup this class.
		 *
		 * @throws SUCAPIException|Exception When settings are not configured or on Exception with the API.
		 */
		public function setup(): void {
			$manager                = SUCSettings::instance()->get_settings();
			$grootboekcode_btw_hoog = $manager->get_value( 'snelstart_grootboekcode_btw_hoog' );
			$grootboekcode_btw_geen = $manager->get_value( 'snelstart_grootboekcode_btw_geen' );
			if ( ! isset( $grootboekcode_btw_hoog ) || ! isset( $grootboekcode_btw_geen ) ) {
				throw new Exception( 'Grootboekcodes must be set in order to use Credit note synchronizer' );
			}

			$tax_types = $this->snelstart_client->btwtarieven();

			$this->btw_converter = new SUCBTW( $grootboekcode_btw_hoog, $grootboekcode_btw_geen, $tax_types );
		}

		/**
		 * Set up objects for synchronization.
		 *
		 * @throws SUCAPIException|SettingsConfigurationException On exception with the API or when settings are not configured correctly.
		 */
		public function setup_objects(): void {
			$manager          = SUCSettings::instance()->get_settings();
			$credit_note_from = $manager->get_value( 'uphance_synchronise_credit_notes_from' );
			$max_to_sync      = $manager->get_value( 'max_credit_notes_to_synchronize' );
			$this->credit_notes = $this->get_credit_notes_to_sync( $credit_note_from, $max_to_sync );
		}

		/**
		 * Update the WordPress settings.
		 *
		 * @return void
		 * @throws SettingsConfigurationException When settings are not configured correctly.
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
		 * @throws SettingsConfigurationException When settings are not configured correctly.
		 */
		public function enabled(): bool {
			$manager          = SUCSettings::instance()->get_settings();
			return $manager->get_value( 'synchronize_credit_notes_to_snelstart' );
		}

		public function update_one( array $to_synchronize ): void {
			// TODO: Implement update_one() method.
		}

		public function delete_one( array $to_synchronize ): void {
			// TODO: Implement delete_one() method.
		}
	}
}
