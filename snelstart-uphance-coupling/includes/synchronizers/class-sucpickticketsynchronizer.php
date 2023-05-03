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
		 * Run the synchronizer.
		 *
		 * @return void
		 */
		public function run(): void {
			$amount_of_pick_tickets = count( $this->pick_tickets );

			for ( $i = 0; $i < $amount_of_pick_tickets; $i ++ ) {
				try {

				} catch ( Exception $e ) {
					$error_log = new SUCErrorLogging();
					// $error_log->set_error( $e . esc_html( sprintf( '\nURL: https://app.uphance.com/invoices/%d', $this->invoices[ $i ]['id'] ) ), 'synchronize-invoice', self::$type, $this->invoices[ $i ]['id'] );
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
			$pick_ticket = $pick_ticket['id'];

		}

		/**
		 * Synchronize one pick ticket to Sendcloud.
		 *
		 * @param string $id the ID of the pick ticket to synchronize.
		 *
		 * @return void
		 */
		public function synchronize_one( string $id ): void {
			// TODO: implement this method.
		}

		/**
		 * Whether this synchronizer should be enabled.
		 *
		 * @return bool whether this synchronizer is enabled.
		 */
		public function enabled(): bool {
			$manager          = SUCSettings::instance()->get_settings();
			return $manager->get_value( 'synchronize_pick_tickets_to_snelstart' );
		}

		public function setup(): void {}

		public function after_run(): void {}
	}
}
