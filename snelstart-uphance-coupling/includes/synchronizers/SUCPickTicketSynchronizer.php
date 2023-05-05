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

		public function setup_objects(): void {
			// TODO: Implement setup_objects() method.
		}

		public function after_run(): void {}

		public function get_url( array $object ): string {
			return "";
		}

		public function create_synchronized_object( array $object, bool $succeeded, ?string $error_message ) {
			// TODO: Implement create_synchronized_object() method.
		}

		public function retrieve_object( int $id ): array {
			return array();
		}
	}
}
