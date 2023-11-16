<?php
/**
 * Snelstart Uphance Payment
 *
 * @package snelstart-uphance-coupling
 */

if ( ! class_exists( 'SUCPayment' ) ) {
	/**
	 * SUC Payment class.
	 *
	 * @class SUCPayment
	 */
	class SUCPayment {

		/**
		 * Payment ID.
		 *
		 * @var string
		 */
		public string $id;

		/**
		 * Payment total amount.
		 *
		 * @var float
		 */
		public float $amount;

		/**
		 * Payment Invoice ID.
		 *
		 * @var string
		 */
		public string $invoice_id;

		/**
		 * Payment description.
		 *
		 * @var string
		 */
		public string $description;

		/**
		 * Created on date.
		 *
		 * @var DateTime
		 */
		public DateTime $created;

		/**
		 * Modified on date.
		 *
		 * @var DateTime
		 */
		public DateTime $modified_on;

		/**
		 * Construct a SUCPayment.
		 *
		 * @param string   $id the Payment ID.
		 * @param float    $amount the Payment amount.
		 * @param string   $invoice_id the Invoice ID.
		 * @param string   $description the Payment description.
		 * @param DateTime $created the created on date.
		 * @param DateTime $modified_on the modified on date.
		 */
		public function __construct( string $id, float $amount, string $invoice_id, string $description, DateTime $created, DateTime $modified_on ) {
			$this->id          = $id;
			$this->amount      = $amount;
			$this->invoice_id  = $invoice_id;
			$this->description = $description;
			$this->created     = $created;
			$this->modified_on = $modified_on;
		}

		/**
		 * Convert this object to string.
		 *
		 * @return string a string-like representation of this object.
		 */
		public function __toString() {
			return sprintf( 'Payment %s (invoice: %s)', $this->id, $this->invoice_id );
		}
	}
}
