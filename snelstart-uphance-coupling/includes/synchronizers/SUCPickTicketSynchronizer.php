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

		private array $pick_tickets;

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

			for ( $i = 0; $i < $amount_of_pick_tickets; $i ++ ) {
				try {

				} catch ( Exception $e ) {

				}
			}
		}

		/**
		 * Synchronize a Pick ticket to Sendcloud.
		 *
		 * @param array $pick_ticket the invoice to synchronize.
		 *
		 * @return void
		 * @throws SUCAPIException|Exception With Exception in an API request or other Exception.
		 */
		public function sync_pick_ticket_to_sendcloud( array $pick_ticket ): void {
			$pick_ticket_id = $pick_ticket['id'];

		}

		/**
		 * Synchronize one pick ticket to Sendcloud.
		 *
		 * @param array $to_synchronize the data from Uphance to send to Sendcloud.
		 *
		 * @return void
		 */
		public function synchronize_one( array $to_synchronize ): void {
			// TODO: implement this method.
		}

		/**
		 * Whether this synchronizer should be enabled.
		 *
		 * @return bool whether this synchronizer is enabled.
		 */
		public function enabled(): bool {
			$manager          = SUCSettings::instance()->get_settings();
			return $manager->get_value( 'synchronize_pick_tickets_to_sendcloud' );
		}

		public function setup(): void {}

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

		public function after_run(): void {}

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
		 *
		 * @return void
		 */
		public function create_synchronized_object( array $object, bool $succeeded, string $source, string $method, ?string $error_message ) {
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

		public function update_one( array $to_synchronize ): void {
			// TODO: Implement update_one() method.
		}
	}
}
