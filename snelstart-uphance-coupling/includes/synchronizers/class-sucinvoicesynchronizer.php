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
include_once SUC_ABSPATH . 'includes/SUCSynchronizedObjects.php';
include_once SUC_ABSPATH . 'includes/class-sucsettings.php';

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
		 * Get the URL of an invoice.
		 *
		 * @param array $object The object to get the URL for.
		 *
		 * @return string A URL pointing to the Uphance resource.
		 */
		public function get_url( array $object ): string {
			return sprintf( 'https://app.uphance.com/invoices/%d', $object['id'] );
		}

		/**
		 * Run the synchronizer.
		 *
		 * @return void
		 */
		public function run(): void {
			$amount_of_invoices = count( $this->invoices );

			for ( $i = 0; $i < $amount_of_invoices; $i ++ ) {
				if ( ! $this->object_already_successfully_synchronized( $this->invoices[ $i ]['id'] ) ) {
					try {
						$this->synchronize_one( $this->invoices[ $i ] );
					} catch ( SUCAPIException $e ) {
						$this->create_synchronized_object(
							$this->invoices[ $i ],
							false,
							'cron',
							'create',
							$e->get_message()
						);
					} catch ( Exception $e ) {
						$this->create_synchronized_object(
							$this->invoices[ $i ],
							false,
							'cron',
							'create',
							$e->__toString()
						);
					}
				}
			}
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
					'Invoice number' => $object['invoice_number'],
					'Total' => suc_format_number( $object['items_total'] + $object['items_tax'] ),
				),
			);
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
			$snelstart_relatie_for_order = get_or_create_relatie_with_name( $this->snelstart_client, $customer );
			$betalingstermijn            = suc_convert_date_to_amount_of_days_until( $invoice['due_date'] );

			if ( ! isset( $snelstart_relatie_for_order ) ) {
				$name = $customer['name'];
				throw new Exception( __( 'Failed to synchronize %1$s because customer %2$s could not be found and created in Snelstart.', 'snelstart-uphance-coupling' ), $invoice_id, $name );
			}

			if ( ! isset( $betalingstermijn ) ) {
				throw new Exception( __( 'Failed to synchronize %1$s because invoice due date could not be converted.', 'snelstart-uphance-coupling' ), $invoice_id );
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
		 * @param $to_synchronize array data to synchronize.
		 *
		 * @return void
		 *
		 * @throws SUCAPIException When the Snelstart API throws an exception.
		 */
		public function synchronize_one( array $to_synchronize ): void {
			$invoice_converted = $this->setup_invoice_for_synchronisation( $to_synchronize );
			$this->sync_invoice_to_snelstart( $invoice_converted );
		}

		/**
		 * Retrieve one of the invoices to synchronize by ID.
		 *
		 * @return array The invoice to synchronize.
		 *
		 * @throws SUCAPIException When the invoice could not be retrieved.
		 */
		public function retrieve_object( int $id ): array {
			return $this->uphance_client->invoice( $id );
		}

		/**
		 * Setup this class.
		 *
		 * @throws Exception When setup of the class fails.
		 */
		public function setup(): void {
			$manager          = SUCSettings::instance()->get_settings();
			$grootboekcode_btw_hoog = $manager->get_value( 'snelstart_grootboekcode_btw_hoog' );
			$grootboekcode_btw_geen = $manager->get_value( 'snelstart_grootboekcode_btw_geen' );
			if ( ! isset( $grootboekcode_btw_hoog ) || ! isset( $grootboekcode_btw_geen ) ) {
				throw new Exception( 'Grootboekcodes must be set in order to use Credit note synchronizer' );
			}

			$tax_types = $this->snelstart_client->btwtarieven();

			$this->btw_converter = new SUCBTW( $grootboekcode_btw_hoog, $grootboekcode_btw_geen, $tax_types );
		}

		/**
		 * Setup objects before run.
		 *
		 * @return void
		 *
		 * @throws SettingsConfigurationException When settings are not configured correctly.
		 * @throws SUCAPIException When invoices could not be retrieved from Uphance.
		 */
		public function setup_objects(): void {
			$manager          = SUCSettings::instance()->get_settings();
			$invoices_from = $manager->get_value( 'uphance_synchronise_invoices_from' );
			$max_to_sync      = $manager->get_value( 'max_invoices_to_synchronize' );
			$this->invoices = $this->get_invoices_to_sync( $invoices_from, $max_to_sync );
		}

		/**
		 * Update settings after run.
		 *
		 * @return void
		 *
		 * @throws SettingsConfigurationException When settings are not configured correctly.
		 */
		public function after_run(): void {
			if ( count( $this->invoices ) > 0 ) {
				$latest_invoice = $this->invoices[ count( $this->invoices ) - 1 ]['id'];
				$settings_manager = SUCSettings::instance()->get_settings();
				$settings_manager->set_value( 'uphance_synchronise_invoices_from', $latest_invoice );
			}
		}

		/**
		 * Whether this synchronizer should be enabled.
		 *
		 * @return bool whether this synchronizer is enabled.
		 *
		 * @throws SettingsConfigurationException When settings are not configured correctly.
		 */
		public function enabled(): bool {
			$manager          = SUCSettings::instance()->get_settings();
			return $manager->get_value( 'synchronize_invoices_to_snelstart' );
		}
	}
}
