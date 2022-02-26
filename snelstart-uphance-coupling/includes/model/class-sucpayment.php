<?php

if ( ! class_exists( 'SUCPayment' ) ) {
	class SUCPayment {

		public string $id;
		public float $amount;
		public string $invoice_id;
		public string $description;
		public DateTime $created;
		public DateTime $modified_on;

		function __construct( string $id, float $amount, string $invoice_id, string $description, DateTime $created, DateTime $modified_on ) {
			$this->id          = $id;
			$this->amount      = $amount;
			$this->invoice_id  = $invoice_id;
			$this->description = $description;
			$this->created     = $created;
			$this->modified_on = $modified_on;
		}

		public function __toString() {
			return sprintf( 'Payment %s (invoice: %s)', $this->id, $this->invoice_id );
		}

	}
}
