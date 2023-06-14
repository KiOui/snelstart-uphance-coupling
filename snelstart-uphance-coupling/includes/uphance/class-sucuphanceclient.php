<?php
/**
 * Uphance Client class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-sucapiclient.php';
include_once SUC_ABSPATH . 'includes/uphance/class-sucuphanceauthclient.php';
include_once SUC_ABSPATH . 'includes/client/class-sucapipaginatedresult.php';

if ( ! class_exists( 'SUCUphanceClient' ) ) {
	/**
	 * Uphance Client class
	 *
	 * @class SUCUphanceClient
	 */
	class SUCUphanceClient extends SUCAPIClient {

		/**
		 * The URL endpoint for the Uphance API.
		 *
		 * @var string
		 */
		protected string $prefix = 'https://api.uphance.com/';

		/**
		 * Uphance Client instance.
		 *
		 * @var SUCUphanceClient|null
		 */
		protected static ?SUCUphanceClient $_instance = null;

		/**
		 * Uphance instance.
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return ?SUCUphanceClient the client if all required settings are set, null otherwise
		 */
		public static function instance(): ?SUCUphanceClient {
			if ( is_null( self::$_instance ) ) {
				$settings = SUCSettings::instance()->get_settings();
				$uphance_username = $settings->get_value( 'uphance_username' );
				$uphance_password = $settings->get_value( 'uphance_password' );

				if ( isset( $uphance_username ) && isset( $uphance_password ) && '' !== $uphance_username && '' !== $uphance_password ) {
					self::$_instance = new SUCUphanceClient( new SUCUphanceAuthClient( $uphance_username, $uphance_password ) );
				} else {
					return null;
				}
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @param SUCAPIAuthClient|null $auth_client the authentication client.
		 * @param int                   $requests_timeout request timeout.
		 */
		public function __construct( ?SUCAPIAuthClient $auth_client, int $requests_timeout = 45 ) {
			parent::__construct( $auth_client, $requests_timeout );
			$this->requests_timeout = $requests_timeout;
		}

		/**
		 * Get all organisations.
		 *
		 * @return array organisations.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function organisations(): array {
			return $this->_get( 'organisations', null, null );
		}

		/**
		 * Set current organisation.
		 *
		 * @param int $organisation_id the organisation ID to set.
		 *
		 * @return bool true if setting succeeded, false otherwise.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function set_current_organisation( int $organisation_id ): bool {
			$response = $this->_post( 'organisations/set_current_org', null, array( 'organizationId' => $organisation_id ) );
			if ( isset( $response['Status'] ) && 'Updated' === $response['Status'] ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Get an invoice.
		 *
		 * @param int $invoice_id the invoice ID of the invoice to get.
		 *
		 * @return array the invoice
		 * @throws SUCAPIException On exception with API request.
		 */
		public function invoice( int $invoice_id ): array {
			$url = "invoices/?invoice_id=$invoice_id";
			$invoice = $this->_get( $url, null, null )['invoices'];
			if ( count( $invoice ) > 0 ) {
				return $invoice[0];
			} else {
				throw new SUCAPIException( 404, 404, 'The invoice could not be found', 'The invoice could not be found', null );
			}
		}

		/**
		 * Get all invoices.
		 *
		 * @param int|null $since_id optional ID of invoice, when set only invoices from this ID will be requested.
		 *
		 * @return SUCAPIPaginatedResult the result with invoices.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function invoices( ?int $since_id = null, int $page = 1 ): SUCAPIPaginatedResult {
			$url = 'invoices/';
			$queries = array(
				'since_id' => $since_id,
				'page' => $page,
			);
			$url = $url . $this->create_querystring( $queries );
			$response = $this->_get( $url, null, null );
			return new SUCAPIPaginatedResult( $response );
		}

		/**
		 * Get an order.
		 *
		 * @param int $order_id the order ID of the order to get.
		 *
		 * @return array the order
		 * @throws SUCAPIException On exception with API request.
		 */
		public function order( int $order_id ): array {
			$url = "sales_orders/$order_id";
			return $this->_get( $url, null, null );
		}

		/**
		 * Get an order by order number.
		 *
		 * @param ?int $order_number the order number of the order to get.
		 *
		 * @return SUCAPIPaginatedResult the orders
		 * @throws SUCAPIException On exception with API request.
		 */
		public function orders( ?int $order_number ): SUCAPIPaginatedResult {
			$url = 'sales_orders/';
			$queries = array(
				'by_order_number' => $order_number,
			);
			$url = $url . $this->create_querystring( $queries );
			$response = $this->_get( $url, null, null );
			return new SUCAPIPaginatedResult( $response );
		}

		/**
		 * Get a credit note.
		 *
		 * @param int $credit_note_id the credit note ID of the credit note to get.
		 *
		 * @return array the credit note.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function credit_note( int $credit_note_id ): array {
			$url = "credit_notes/$credit_note_id";
			return $this->_get( $url, null, null )['credit_notes'][0];
		}

		/**
		 * Get all credit notes.
		 *
		 * @param int|null $since_id optional ID of credit note, when set only credit notes from this ID will be requested.
		 *
		 * @return SUCAPIPaginatedResult the result with credit notes.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function credit_notes( ?int $since_id = null, int $page = 1 ): SUCAPIPaginatedResult {
			$url = 'credit_notes/';
			$queries = array(
				'since_id' => $since_id,
				'page' => $page,
			);
			$url = $url . $this->create_querystring( $queries );
			$response = $this->_get( $url, null, null );
			return new SUCAPIPaginatedResult( $response );
		}

		/**
		 * Get a pick tickets.
		 *
		 * @param int $pick_ticket_id the pick ticket ID of the pick ticket to get.
		 *
		 * @return array the pick ticket.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function pick_ticket( int $pick_ticket_id ): array {
			$url = "pick_tickets/$pick_ticket_id";
			return $this->_get( $url, null, null )['pick_ticket'];
		}

		/**
		 * Get all pick tickets.
		 *
		 * @param int|null $since_id optional ID of pick tickets, when set only pick tickets from this ID will be requested.
		 *
		 * @return SUCAPIPaginatedResult the result with pick tickets.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function pick_tickets( ?int $since_id = null, int $page = 1 ): SUCAPIPaginatedResult {
			$url = 'pick_tickets/';
			$queries = array(
				'since_id' => $since_id,
				'page' => $page,
			);
			$url = $url . $this->create_querystring( $queries );
			$response = $this->_get( $url, null, null );
			return new SUCAPIPaginatedResult( $response );
		}

		/**
		 * Get Customer by ID.
		 *
		 * @param int $customer_id the customer ID to get.
		 *
		 * @return array a customer.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function customer_by_id( int $customer_id ): array {
			return $this->_get( 'customers/' . $customer_id, null, null );
		}

		/**
		 * Add a payment to Uphance.
		 *
		 * @param float       $amount the amount to add for the payment.
		 * @param string|null $reference the reference of the payment.
		 * @param DateTime    $date the date of the payment.
		 * @param int         $sale_id the sale ID of the payment.
		 * @param int         $company_id the company ID for which to add the payment.
		 * @param int         $invoice_id the invoice ID for the payment.
		 * @param string      $source the source of the payment (e.g. cash).
		 *
		 * @return array the payment as an array.
		 * @throws SUCAPIException On exception with API request.
		 */
		public function add_payment( float $amount, ?string $reference, DateTime $date, int $sale_id, int $company_id, int $invoice_id, string $source ): array {
			return $this->_post(
				'payments/',
				null,
				array(
					'amount' => number_format( $amount, 2, '.', '' ),
					'reference' => $reference,
					'created_at' => $date->format( 'c' ),
					'date' => $date->format( 'Y-m-d' ),
					'sale_id' => $sale_id,
					'company_id' => $company_id,
					'invoice_id' => $invoice_id,
					'source' => $source,
				)
			);
		}
	}
}
