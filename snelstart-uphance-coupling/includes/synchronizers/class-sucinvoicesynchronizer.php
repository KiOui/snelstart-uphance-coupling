<?php
/**
 * Invoice synchronizer.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/synchronizers/class-sucsynchronisable.php';
include_once SUC_ABSPATH . 'includes/snelstart/class-sucbtw.php';

if ( ! class_exists( 'SUCInvoiceSynchronizer' ) ) {
	/**
	 * SUC Invoice Synchronizer.
	 *
	 * @class SUCInvoiceSynchronizer
	 */
	class SUCInvoiceSynchronizer extends SUCSynchronisable {

		/**
		 * The type of this synchronizer.
		 *
		 * @var string
		 */
		public static string $type = 'invoice';

		/**
		 * BTW Converter.
		 *
		 * @var ?SUCBTW
		 */
		private ?SUCBTW $btw_converter = null;

		/**
		 * Invoices to synchronize.
		 *
		 * @var array
		 */
		private array $invoices;

		/**
		 * Get the invoices to sync.
		 *
		 * @throws SUCAPIException On Exception with the API.
		 */
		private function get_invoices_to_sync( ?string $invoices_from, ?int $max_to_sync ): array {
			if ( isset( $invoices_from ) ) {
				$invoices = $this->uphance_client->invoices( $invoices_from )->result;
			} else {
				$invoices = $this->uphance_client->invoices()->result;
			}

			$invoices = $invoices['invoices'];

			if ( isset( $max_to_sync ) ) {
				if ( 0 === $max_to_sync ) {
					return array();
				} else {
					$invoices = array_slice( $invoices, 0, $max_to_sync );
				}
			}

			return $invoices;
		}

		/**
		 * Run the synchronizer.
		 *
		 * @return void
		 */
		public function run(): void {
			$amount_of_invoices = count( $this->invoices );

			for ( $i = 0; $i < $amount_of_invoices; $i ++ ) {
				try {
					$invoice_converted = $this->setup_invoice_for_synchronisation( $this->invoices[ $i ] );
					$this->sync_invoice_to_snelstart( $invoice_converted );
				} catch ( Exception $e ) {
					$error_log = new SUCErrorLogging();
					$error_log->set_error( $e, 'synchronize-invoice', self::$type, $this->invoices[ $i ]['id'] );
				}
			}
		}

		/**
		 * Setup an invoice for synchronisation.
		 *
		 * @throws SUCAPIException When setup failed.
		 */
		private function setup_invoice_for_synchronisation( array $invoice ): array {
			$invoice['customer'] = $this->uphance_client->customer_by_id( $invoice['company_id'] )['customer'];
			return $invoice;
		}

		/**
		 * Synchronize an invoice to Snelstart.
		 *
		 * @param array $invoice the invoice to synchronize.
		 *
		 * @return void
		 * @throws SUCAPIException|Exception With Exception in an API request or other Exception.
		 */
		public function sync_invoice_to_snelstart( array $invoice ): void {
			$invoice_id = $invoice['id'];
			$customer = $invoice['customer'];
			$grootboek_regels = suc_construct_order_line_items( $invoice['line_items'], $this->btw_converter );
			$btw_regels                  = suc_construct_btw_line_items( $invoice['line_items'] );
			$snelstart_relatie_for_order = get_or_create_relatie_with_name( $this->snelstart_client, $customer['name'] );
			$betalingstermijn            = suc_convert_date_to_amount_of_days_until( $invoice['due_date'] );

			if ( ! isset( $snelstart_relatie_for_order ) ) {
				$name = $customer['name'];
				throw new Exception( __( 'Failed to synchronize %1$s because customer %2$s could not be found and created in Snelstart.', 'snelstart-uphance-coupling' ), $invoice_id, $name );
			}

			if ( ! isset( $betalingstermijn ) ) {
				throw new Exception( __( 'Failed to synchronize %1$s because invoice due date could not be converter.', 'snelstart-uphance-coupling' ), $invoice_id );
			}

			try {
				$invoice_date = new DateTime( $invoice['created_at'] );
			} catch ( Exception $e ) {
				$invoice_date = new DateTime( 'now' );
			}

			$this->snelstart_client->add_verkoopboeking( $invoice['invoice_number'], $snelstart_relatie_for_order['id'], suc_format_number( $invoice['items_total'] + $invoice['items_tax'] ), $betalingstermijn, $grootboek_regels, $btw_regels, $invoice_date );
		}

		/**
		 * Synchronize one invoice to Snelstart.
		 *
		 * @param string $id the ID of the invoice to synchronize.
		 *
		 * @return void
		 */
		public function synchronize_one( string $id ): void {
			// TODO: implement this method.
		}

		/**
		 * Setup this class.
		 *
		 * @throws Exception When setup of the class fails.
		 */
		public function setup(): void {
			$manager          = SUCSettings::instance()->get_manager();
			$invoices_from = $manager->get_value_by_setting_id( 'uphance_synchronise_invoices_from' );
			$max_to_sync      = $manager->get_value_by_setting_id( 'max_invoices_to_synchronize' );
			$grootboekcode_btw_hoog = $manager->get_value_by_setting_id( 'snelstart_grootboekcode_btw_hoog' );
			$grootboekcode_btw_geen = $manager->get_value_by_setting_id( 'snelstart_grootboekcode_btw_geen' );
			if ( ! isset( $grootboekcode_btw_hoog ) || ! isset( $grootboekcode_btw_geen ) ) {
				throw new Exception( 'Grootboekcodes must be set in order to use Credit note synchronizer' );
			}

			$tax_types = $this->snelstart_client->btwtarieven();

			$this->btw_converter = new SUCBTW( $grootboekcode_btw_hoog, $grootboekcode_btw_geen, $tax_types );
			$this->invoices = $this->get_invoices_to_sync( $invoices_from, $max_to_sync );
		}

		/**
		 * Update settings after run.
		 *
		 * @return void
		 */
		public function after_run(): void {
			$latest_invoice                                = $this->invoices[ count( $this->invoices ) - 1 ]['id'];

			$settings_manager = SUCSettings::instance()->get_manager();
			$settings_manager->set_value_by_setting_id( 'uphance_synchronise_invoices_from', $latest_invoice );
		}

		/**
		 * Whether this synchronizer should be enabled.
		 *
		 * @return bool whether this synchronizer is enabled.
		 */
		public function enabled(): bool {
			$manager          = SUCSettings::instance()->get_manager();
			return $manager->get_value_by_setting_id( 'synchronize_invoices_to_snelstart' );
		}
	}
}
