<?php
/**
 * Pick Ticket synchronizer.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/synchronizers/class-sucsynchronisable.php';
include_once SUC_ABSPATH . 'includes/class-succache.php';

if ( ! class_exists( 'SUCPickTicketSynchronizer' ) ) {
	/**
	 * SUC Pick Ticket Synchronizer.
	 *
	 * @class SUCPickTicketSynchronizer
	 */
	class SUCPickTicketSynchronizer extends SUCSynchronisable {

		/**
		 * The type of this synchronizer.
		 *
		 * @var string
		 */
		public static string $type = 'pick-ticket';

		/**
		 * The pick tickets to synchronize.
		 *
		 * @var array
		 */
		private array $pick_tickets;

		/**
		 * The shipping method to use.
		 *
		 * @var string
		 */
		private string $shipping_method;

		/**
		 * The ID of the shipping method to use.
		 *
		 * @var int
		 */
		private int $shipping_method_id;

		/**
		 * The Uphance client to use for the synchronizer.
		 *
		 * @var SUCUphanceClient
		 */
		protected SUCUphanceClient $uphance_client;

		/**
		 * The Sendcloud client to use for the synchronizer.
		 *
		 * @var SUCSendcloudClient
		 */
		protected SUCSendcloudClient $sendcloud_client;

		/**
		 * Constructor.
		 *
		 * @param SUCuphanceClient   $uphance_client the Uphance client.
		 * @param SUCSendcloudClient $sendcloud_client the Snelstart client.
		 */
		public function __construct( SUCuphanceClient $uphance_client, SUCSendcloudClient $sendcloud_client ) {
			$this->uphance_client = $uphance_client;
			$this->sendcloud_client = $sendcloud_client;
		}

		/**
		 * Run the synchronizer.
		 *
		 * @return void
		 */
		public function run(): void {
			$amount_of_pick_tickets = count( $this->pick_tickets );
			for ( $i = 0; $i < $amount_of_pick_tickets; $i++ ) {
				if ( ! $this->object_already_successfully_synchronized( $this->pick_tickets[ $i ]['id'] ) ) {
					try {
						$this->synchronize_one( $this->pick_tickets[ $i ] );
						$this->create_synchronized_object(
							$this->pick_tickets[ $i ],
							true,
							'cron',
							'create',
							null,
							null
						);
					} catch ( Exception $e ) {
						if ( get_class( $e ) === 'SUCAPIException' ) {
							$this->create_synchronized_object(
								$this->pick_tickets[ $i ],
								false,
								'cron',
								'create',
								$e->get_message(),
								null
							);
						} else {
							$this->create_synchronized_object(
								$this->pick_tickets[ $i ],
								false,
								'cron',
								'create',
								$e->__toString(),
								null
							);
						}
					}
				}
			}
		}

		/**
		 * Map a pick ticket to parcel items.
		 *
		 * @param array $pick_ticket The pick ticket from Uphance.
		 *
		 * @return array The parcel items to be sent to Sendcloud.
		 */
		private function map_parcel_items( array $pick_ticket ): array {
			$parcel_items = array();
			foreach ( $pick_ticket['line_items'] as $product ) {
				$product_description = $product['product_name'];
				$product_id = $product['product_id'];
				$color = $product['color'];
				foreach ( $product['line_quantities'] as $quantity ) {
					if ( $quantity['quantity'] > 0 ) {
						$sku = $quantity['sku_id'];
						$size = $quantity['size'];
						$parcel_items[] = array(
							'description' => $product_description,
							'quantity' => $quantity['quantity'],
							'sku' => $sku,
							'weight' => '0.001',
							'value' => suc_format_number( floatval( $product['unit_price'] ) ),
							'product_id' => $product_id,
							'properties' => array(
								'color' => $color,
								'size' => $size,
							),
						);
					}
				}
			}
			return $parcel_items;
		}

		/**
		 * Convert a weight from Uphance to KG.
		 *
		 * @param float  $weight The weight from Uphance.
		 * @param string $weight_unit The weight unit from Uphance (g, oz, lb or kg).
		 *
		 * @return float The weight converted to KG.
		 */
		private function convert_weight_to_kg( float $weight, string $weight_unit ): float {
			if ( 'g' === $weight_unit ) {
				return $weight / 1000;
			} else if ( 'oz' === $weight_unit ) {
				return $weight * 0.02834952;
			} else if ( 'lb' === $weight_unit ) {
				return $weight * 0.4535924;
			} else {
				return $weight;
			}
		}

		/**
		 * Convert a dimension string (W x L x H) to array of split dimensions.
		 *
		 * @param string $dimension_string The dimension string.
		 *
		 * @return array|null An array of split dimensions if the dimensions match, else null.
		 */
		private function convert_dimensions( string $dimension_string ): ?array {
			$matches = array();
			if ( preg_match( '/^(?P<width>\d*) *x *(?P<length>\d*) *x *(?P<height>\d*)$/', $dimension_string, $matches ) ) {
				return array(
					'width' => $matches['width'],
					'length' => $matches['length'],
					'height' => $matches['height'],
				);
			} else {
				return null;
			}
		}

		/**
		 * Set up a pick ticket for synchronization.
		 *
		 * @param array $pick_ticket The pick ticket from Uphance.
		 *
		 * @return array The data to be sent to Sendcloud.
		 */
		public function setup_pick_ticket_for_synchronisation( array $pick_ticket ): array {
			if ( ! empty( $pick_ticket['address']['line_2'] ) && ! empty( $pick_ticket['address']['line_3'] ) ) {
				$address_2 = $pick_ticket['address']['line_2'] . ' - ' . $pick_ticket['address']['line_3'];
			} else if ( ! empty( $pick_ticket['address']['line_2'] ) ) {
				$address_2 = $pick_ticket['address']['line_2'];
			} else if ( ! empty( $pick_ticket['address']['line_3'] ) ) {
				$address_2 = $pick_ticket['address']['line_3'];
			} else {
				$address_2 = '';
			}

			if ( $pick_ticket['dimensions'] ) {
				$dimensions = $this->convert_dimensions( $pick_ticket['dimensions'] );
			} else {
				$dimensions = null;
			}

			if ( isset( $pick_ticket['gross_weight'] ) && '' !== $pick_ticket['gross_weight'] ) {
				$weight = suc_format_number( $this->convert_weight_to_kg( $pick_ticket['gross_weight'], $pick_ticket['gross_weight_unit'] ) );
			} else {
				$weight = 0.001;
			}

			// Weight parameter must be greater than 0.001.
			if ( $weight < 0.001 ) {
				$weight = 0.001;
			}

			return array(
				'parcel' => array(
					'name' => is_null( $pick_ticket['contact_name'] ) ? $pick_ticket['customer_name'] : $pick_ticket['contact_name'],
					'company_name' => $pick_ticket['customer_name'],
					'email' => $pick_ticket['contact_email'],
					'telephone' => $pick_ticket['contact_phone'],
					'address' => $pick_ticket['address']['line_1'],
					'address_2' => $address_2,
					'order_number' => $pick_ticket['order_number'],
					'city' => $pick_ticket['address']['city'],
					'country' => $pick_ticket['address']['country'],
					'postal_code' => $pick_ticket['address']['postcode'],
					'country_state' => suc_sendcloud_requires_state( $pick_ticket['address']['country'] ) ? $pick_ticket['address']['state'] : null,
					'parcel_items' => $this->map_parcel_items( $pick_ticket ),
					'weight' => $weight,
					'length' => is_null( $dimensions ) ? null : $dimensions['length'],
					'width' => is_null( $dimensions ) ? null : $dimensions['width'],
					'height' => is_null( $dimensions ) ? null : $dimensions['height'],
					'total_order_value' => $pick_ticket['grand_total'],
					'total_order_value_currency' => $pick_ticket['currency'],
					'customs_shipment_type' => 2, // Commercial goods.
					'is_return' => false,
					'shipment' => array(
						'id' => $this->shipping_method_id,
						'name' => $this->shipping_method,
					),
					'request_label' => false,
				),
			);
		}

		/**
		 * Synchronize one pick ticket to Sendcloud.
		 *
		 * @param array $to_synchronize the data from Uphance to send to Sendcloud.
		 *
		 * @return void
		 */
		public function synchronize_one( array $to_synchronize ): void {
			$parcel = $this->setup_pick_ticket_for_synchronisation( $to_synchronize );
			$sendcloud_parcel = $this->sendcloud_client->create_parcel( $parcel );
			SUCObjectMapping::create_mapped_object(
				self::$type,
				'uphance',
				'sendcloud',
				$to_synchronize['id'],
				$sendcloud_parcel['parcel']['id'],
			);
		}

		/**
		 * Whether this synchronizer should be enabled.
		 *
		 * @return bool whether this synchronizer is enabled.
		 * @throws SettingsConfigurationException When the 'synchronize_pick_tickets_to_sendcloud' setting is not registered.
		 */
		public function enabled(): bool {
			$manager          = SUCSettings::instance()->get_settings();
			return $manager->get_value( 'synchronize_pick_tickets_to_sendcloud' );
		}

		/**
		 * Setup this class.
		 *
		 * @return void
		 * @throws SettingsConfigurationException|Exception When an Exception occurs during setup.
		 */
		public function setup(): void {
			$shipping_methods = SUCCache::instance()->get_shipping_methods();
			if ( null === $shipping_methods ) {
				throw new Exception( 'Shipping methods could not be retrieved from Sendcloud.' );
			}

			$manager = SUCSettings::instance()->get_settings();
			$selected_shipping_method_name = $manager->get_value( 'sendcloud_shipping_method' );
			if ( null === $selected_shipping_method_name ) {
				throw new Exception( 'No default shipping method selected.' );
			}

			$set = false;

			foreach ( $shipping_methods['shipping_methods'] as $shipping_method ) {
				if ( $shipping_method['name'] === $selected_shipping_method_name ) {
					$this->shipping_method = $selected_shipping_method_name;
					$this->shipping_method_id = $shipping_method['id'];
					$set = true;
					break;
				}
			}

			if ( ! $set ) {
				throw new Exception( 'Shipping method not found in Sendcloud.' );
			}
		}

		/**
		 * Get the pick tickets to sync.
		 *
		 * @throws SUCAPIException On Exception with the API.
		 */
		private function get_pick_tickets_to_sync( ?string $pick_tickets_from, ?int $max_to_sync ): array {
			if ( isset( $pick_tickets_from ) ) {
				$pick_tickets = $this->uphance_client->pick_tickets( $pick_tickets_from )->result;
			} else {
				$pick_tickets = $this->uphance_client->pick_tickets()->result;
			}

			$pick_tickets = $pick_tickets['pick_tickets'];

			if ( isset( $max_to_sync ) ) {
				if ( 0 === $max_to_sync ) {
					return array();
				} else {
					$pick_tickets = array_slice( $pick_tickets, 0, $max_to_sync );
				}
			}

			return $pick_tickets;
		}

		/**
		 * Setup objects before run job.
		 *
		 * @return void
		 *
		 * @throws SettingsConfigurationException When settings are not configured correctly.
		 * @throws SUCAPIException When pick tickets could not be retrieved from Uphance.
		 */
		public function setup_objects(): void {
			$manager = SUCSettings::instance()->get_settings();
			$pick_tickets_from = $manager->get_value( 'uphance_synchronise_pick_tickets_from' );
			$max_to_sync = $manager->get_value( 'max_pick_tickets_to_synchronize' );
			$this->pick_tickets = $this->get_pick_tickets_to_sync( $pick_tickets_from, $max_to_sync );
		}

		/**
		 * Update settings after run.
		 *
		 * @return void
		 *
		 * @throws SettingsConfigurationException When settings are not configured correctly.
		 */
		public function after_run(): void {
			if ( count( $this->pick_tickets ) > 0 ) {
				$latest_pick_ticket = $this->pick_tickets[ count( $this->pick_tickets ) - 1 ]['id'];
				$settings_manager = SUCSettings::instance()->get_settings();
				$settings_manager->set_value( 'uphance_synchronise_pick_tickets_from', $latest_pick_ticket );
			}
		}

		/**
		 * Get the URL of a pick ticket.
		 *
		 * @param array $object The object to get the URL for.
		 *
		 * @return string A URL pointing to the Uphance resource.
		 */
		public function get_url( array $object ): string {
			return 'https://app.uphance.com/pick_tickets/' . $object['id'];
		}

		/**
		 * Create a synchronized object.
		 *
		 * @param array       $object The object.
		 * @param bool        $succeeded Whether the synchronization succeeded.
		 * @param string      $source The source of the synchronization.
		 * @param string      $method The method of the synchronization.
		 * @param string|null $error_message A possible error message that occurred during synchronization.
		 * @param array|null  $extra_data Possible extra data.
		 *
		 * @return void
		 */
		public function create_synchronized_object( array $object, bool $succeeded, string $source, string $method, ?string $error_message, ?array $extra_data ): void {
			if ( null === $extra_data ) {
				$extra_data = array();
			}

			if ( array_key_exists( 'order_number', $object ) ) {
				$extra_data['Order number'] = $object['order_number'];
			}

			if ( array_key_exists( 'status', $object ) ) {
				$extra_data['Shipped'] = 'shipped' === $object['status'] ? 'true' : 'false';
			}

			SUCSynchronizedObjects::create_synchronized_object(
				intval( $object['id'] ),
				$this::$type,
				$succeeded,
				$source,
				$method,
				$this::get_url( $object ),
				$error_message,
				$extra_data,
			);
		}

		/**
		 * Retrieve a pick ticket by ID.
		 *
		 * @param int $id The ID of the pick ticket to retrieve.
		 *
		 * @return array A pick ticket.
		 * @throws SUCAPIException When an exception with the API occurred.
		 */
		public function retrieve_object( int $id ): array {
			return $this->uphance_client->pick_ticket( $id );
		}

		/**
		 * Update a pick ticket.
		 *
		 * @param array $to_synchronize The pick ticket to update.
		 *
		 * @return void
		 * @throws Exception When the mapped object does not exist.
		 */
		public function update_one( array $to_synchronize ): void {
			$mapped_object = SUCObjectMapping::get_mapped_object( self::$type, 'uphance', 'sendcloud', $to_synchronize['id'] );
			if ( null === $mapped_object ) {
				throw new Exception( 'Mapped object for this type does not exist.' );
			}

			$parcel = $this->setup_pick_ticket_for_synchronisation( $to_synchronize );
			$parcel['parcel']['id'] = get_post_meta( $mapped_object->ID, 'mapped_to_object_id', true );
			$this->sendcloud_client->update_parcel( $parcel );
		}

		/**
		 * Delete a pick ticket from Sendcloud.
		 *
		 * @throws Exception|SUCAPIException API Exception on exception with API and Exception when Mapped object does not exist.
		 */
		public function delete_one( array $to_synchronize ): void {
			$mapped_object = SUCObjectMapping::get_mapped_object( self::$type, 'uphance', 'sendcloud', $to_synchronize['id'] );
			if ( null === $mapped_object ) {
				throw new Exception( 'Mapped object for this type does not exist.' );
			}

			$this->sendcloud_client->cancel_parcel( get_post_meta( $mapped_object->ID, 'mapped_to_object_id', true ) );
			wp_delete_post( $mapped_object->ID, true );
		}
	}
}
