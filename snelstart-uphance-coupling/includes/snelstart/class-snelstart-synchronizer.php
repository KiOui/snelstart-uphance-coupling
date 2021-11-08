<?php
/**
 * Snelstart Synchronizer class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/snelstart/class-snelstart-client.php';

if ( ! class_exists( 'SUCSnelstartSynchronizer' ) ) {
	/**
	 * Snelstart Synchronizer
	 *
	 * @class SUCSnelstartSynchronizer
	 */
	class SUCSnelstartSynchronizer {

		/**
		 * Constant for BTW Hoog amount.
		 *
		 * @var float
		 */
		public static float $BTW_HOOG = 21.0;

		/**
		 * Constanct for BTW None amount.
		 *
		 * @var float
		 */
		public static float $BTW_NONE = 0.0;

		/**
		 * Constant for BTW Hoog post.
		 *
		 * @var string
		 */
		public static string $BTW_NAME_HIGH = "VerkopenHoog";

		/**
		 * Constant for BTW None post.
		 *
		 * @var string
		 */
		public static string $BTW_NAME_NONE = "VerkopenVerlegd";

		/**
		 * Grootboekcode for BTW Hoog post.
		 *
		 * @var string
		 */
		private string $grootboekcode_btw_hoog;

		/**
		 * Grootboekcode for BTW None post.
		 *
		 * @var string
		 */
		private string $grootboekcode_btw_geen;

		/**
		 * Client for Snelstart.
		 *
		 * @var SUCSnelstartClient
		 */
		private SUCSnelstartClient $client;

		/**
		 * Tax types.
		 *
		 * @var array
		 */
		private array $tax_types;

		/**
		 * Constructor.
		 *
		 * @param SUCSnelstartClient $client the Snelstart Client
		 * @param string $grootboekcode_btw_hoog Grootboekcode for BTW Hoog post.
		 * @param string $grootboekcode_btw_geen Grootboekcode for BTW Geen post.
		 *
		 * @throws SUCAPIException
		 */
		public function __construct(SUCSnelstartClient $client, string $grootboekcode_btw_hoog, string $grootboekcode_btw_geen) {
			$this->client = $client;
			$this->grootboekcode_btw_hoog = $grootboekcode_btw_hoog;
			$this->grootboekcode_btw_geen = $grootboekcode_btw_geen;
			$this->tax_types = suc_get_current_btw_soorten( $this->client );
		}

		/**
		 * Get relatie with specific name.
		 *
		 * @param string $naam the name to get the relatie for
		 *
		 * @return array|null an array with the relatie if succeeded, null if the relatie does not exist or multiple relaties were returned
		 */
		public function get_relatie_with_name(string $naam): ?array {
			try {
				$relaties = $this->client->relaties( null, null, "Naam eq '$naam'" );
			} catch (SUCAPIException $e) {
				return null;
			}
			if ( sizeof( $relaties ) === 1) {
				return $relaties[0];
			} else {
				return null;
			}
		}

		/**
		 * Convert a BTW amount to BTW name.
		 *
		 * @param float $btw_amount the BTW amount to convert
		 *
		 * @return string|null the BTW name or null if it does not exist
		 */
		public static function convert_btw_amount_to_name(float $btw_amount): ?string {
			if (SUCSnelstartSynchronizer::$BTW_NONE === $btw_amount) {
				return SUCSnelstartSynchronizer::$BTW_NAME_NONE;
			}
			else if (SUCSnelstartSynchronizer::$BTW_HOOG === $btw_amount) {
				return SUCSnelstartSynchronizer::$BTW_NAME_HIGH;
			}
			else {
				return null;
			}
		}

		/**
		 * Get a grootboekcode for a tax amount.
		 *
		 * @param float $btw_amount the BTW amount to get the grootboekcode for
		 *
		 * @return string|null the grootboekcode ID or null if it does not exist
		 */
		private function get_grootboekcode_for_tax_amount(float $btw_amount): ?string {
			if (SUCSnelstartSynchronizer::$BTW_NONE === $btw_amount) {
				return $this->grootboekcode_btw_geen;
			}
			else if (SUCSnelstartSynchronizer::$BTW_HOOG === $btw_amount) {
				return $this->grootboekcode_btw_hoog;
			}
			else {
				return null;
			}
		}

		/**
		 * Convert BTW amount to type.
		 *
		 * @param float $tax_level the tax level to convert
		 *
		 * @return array|null the tax type or null if it does not exist
		 */
		public function convert_btw_amount_to_type( float $tax_level ): ?array {
			foreach( $this->tax_types as $tax_type ) {
				if ( $tax_type['btwPercentage'] === $tax_level ) {
					return $tax_type;
				}
			}
			return null;
		}

		/**
		 * Format a number to two decimals maximum.
		 *
		 * @param float $number the number to format
		 *
		 * @return string a string of the formatted number
		 */
		public static function format_number( float $number ): string {
			return number_format( $number, 2 );
		}

		/**
		 * Construct order line items for an invoice.
		 *
		 * @param array $items the item array
		 *
		 * @return array|null an array with line items, null if constructing the line items failed
		 */
		private function construct_order_line_items(array $items): ?array {
			$to_order = array();
			foreach ($items as $item) {
				$price = $item['unit_price'];
				$product_id = $item['product_id'];
				$product_name = $item['product_name'];
				$tax_level = $item['tax_level'];
				$amount = array_reduce( $item['line_quantities'], function( int $carry, array $item ) {
					return $carry + $item['quantity'];
				}, 0);
				$grootboekcode = $this->get_grootboekcode_for_tax_amount($tax_level);
				$tax_type = $this->convert_btw_amount_to_type( $tax_level );
				if ( !isset( $tax_type ) ) {
					SUCLogging::instance()->write( "Failed to get tax type for $tax_level." );
					return null;
				}
				if ( !isset( $grootboekcode ) ) {
					SUCLogging::instance()->write( "Failed to get the grootboekcode for tax level $tax_level." );
					return null;
				}
				$tax_name = $tax_type['btwSoort'];
				array_push( $to_order,
					array(
						"omschrijving" => "$amount x $product_id $product_name",
						"grootboek" => array(
							"id" => $grootboekcode,
						),
						"bedrag" => SUCSnelstartSynchronizer::format_number($price * $amount),
						"btwSoort" => $tax_name,
					)
				);
			}
			return $to_order;
		}

		/**
		 * Construct BTW line items for an invoice.
		 *
		 * @param array $items the item array
		 *
		 * @return array|null an array with BTW line items, null if constructing the BTW line items failed
		 */
		private function construct_btw_line_items( array $items ): ?array {
			$btw_items = array();
			foreach ($items as $item) {
				$price = $item['unit_price'];
				$tax_level = $item['tax_level'];
				$amount = array_reduce( $item['line_quantities'], function( int $carry, array $item ) {
					return $carry + $item['quantity'];
				}, 0);
				$tax_name = $this->convert_btw_amount_to_name( $tax_level );
				if (key_exists($tax_name, $btw_items)) {
					$btw_items[$tax_name]["btwBedrag"] = $btw_items[$tax_name]["btwBedrag"] + $price * $amount * $tax_level;
				}
				else {
					$btw_items[$tax_name] = array(
						"btwSoort" => $tax_name,
						"btwBedrag" => SUCSnelstartSynchronizer::format_number($price * $amount * $tax_level),
					);
				}
			}
			return array_values($btw_items);
		}

		/**
		 * Construct grootboek regels.
		 *
		 * @param array $invoice construct grootboek regels for an invoice
		 *
		 * @return array|null an array with line items, null if constructing the line items failed
		 */
		private function construct_grootboek_regels(array $invoice): ?array {
			return $this->construct_order_line_items( $invoice['line_items'] );
		}

		/**
		 * Synchronize an invoice to Snelstart.
		 *
		 * @param array $invoice the invoice to synchronize
		 *
		 * @return bool true if synchonization succeeded, false otherwise
		 */
		public function sync_invoice_to_snelstart(array $invoice): bool {
			$invoice_id = $invoice['id'];
			SUCLogging::instance()->write( "Starting synchronization of invoice $invoice_id." );
			$customer = $invoice['customer'];
			if ( isset( $customer ) ) {
				$grootboek_regels = $this->construct_grootboek_regels( $invoice );
				if ( isset( $grootboek_regels ) ) {
					$btw_regels                  = $this->construct_btw_line_items( $invoice['line_items'] );
					$snelstart_relatie_for_order = $this->get_relatie_with_name( $customer['name'] );
					if ( !isset( $btw_regels ) ) {
						SUCLogging::instance()->write( "Failed to synchronize $invoice_id because BTW regels could not be constructed." );
						return false;
					}

					if ( !isset( $snelstart_relatie_for_order ) ) {
						$name = $customer['name'];
						SUCLogging::instance()->write( "Failed to synchronize $invoice_id because customer $name was not found in Snelstart." );
						return false;
					}

					try {
						$this->client->add_verkoopboeking( $invoice["invoice_number"], $snelstart_relatie_for_order["id"], SUCSnelstartSynchronizer::format_number( $invoice['items_total'] ), $grootboek_regels, $btw_regels );
					} catch (SUCAPIException $e) {
						SUCLogging::instance()->write($e);
						SUCLogging::instance()->write( "Failed to synchronize $invoice_id because of an exception." );
						return false;
					}
				} else {
					SUCLogging::instance()->write( "Failed to synchronize $invoice_id because no grootboek regels could be created." );
					return false;
				}
			}
			else {
				SUCLogging::instance()->write( "Failed to synchronize $invoice_id because no customer for invoice was set." );
				return false;
			}
			SUCLogging::instance()->write( "Synchronization of invoice $invoice_id succeeded." );
			return true;
		}

		/**
		 * Synchronize multiple invoices to Snelstart.
		 *
		 * @param array $invoices array of invoices
		 *
		 * @return bool true if all invoices synchronized successfully, false otherwise
		 */
		public function sync_invoices_to_snelstart(array $invoices): bool {
			$return_value = true;
			foreach ( $invoices as $invoice ) {
				if ( !$this->sync_invoice_to_snelstart( $invoice ) ) {
					$return_value = false;
				}
			}
			return $return_value;
		}
	}
}