<?php
/**
 * Uphance Payment
 *
 * @package snelstart-uphance-coupling
 */

if ( ! class_exists( 'SUCUphancePayment' ) ) {
	/**
	 * SUC Uphance Payment class.
	 *
	 * @class SUCUphancePayment
	 */
	class SUCUphancePayment {

		/**
		 * The ID of the Payment in Uphance.
		 *
		 * @var int
		 */
		public int $id;

		/**
		 * The created at date of the Payment.
		 *
		 * @var DateTime
		 */
		public DateTime $created_at;

		/**
		 * The updated at date of the Payment.
		 *
		 * @var DateTime
		 */
		public DateTime $updated_at;

		/**
		 * The payment amount.
		 *
		 * @var float
		 */
		public float $amount;

		/**
		 * The payment reference in Uphance.
		 *
		 * @var string
		 */
		public string $reference;

		/**
		 * The date of the Payment.
		 *
		 * @var DateTime
		 */
		public DateTime $date;

		/**
		 * The sale ID of the Payment in Uphance.
		 *
		 * @var int
		 */
		public int $sale_id;

		/**
		 * The company ID of the Payment in Uphance.
		 *
		 * @var int|null
		 */
		public ?int $company_id;

		/**
		 * The Invoice ID of the Payment.
		 *
		 * @var int
		 */
		public int $invoice_id;

		/**
		 * The source of the Payment (e.g. "cash").
		 *
		 * @var string
		 */
		public string $source;

		/**
		 * Construct a SUCUphancePayment.
		 *
		 * @param int      $id the Payment ID in Uphance.
		 * @param DateTime $created_at the created at date.
		 * @param DateTime $updated_at the updated at date.
		 * @param float    $amount the Payment amount.
		 * @param string   $reference the Payment reference.
		 * @param DateTime $date the Payment date.
		 * @param int      $sale_id the sale ID.
		 * @param int|null $company_id the company ID.
		 * @param int      $invoice_id the invoice ID.
		 * @param string   $source the source of the Payment (e.g. "cash").
		 */
		public function __construct( int $id, DateTime $created_at, DateTime $updated_at, float $amount, string $reference, DateTime $date, int $sale_id, ?int $company_id, int $invoice_id, string $source ) {
			$this->id         = $id;
			$this->created_at = $created_at;
			$this->updated_at = $updated_at;
			$this->amount     = $amount;
			$this->reference  = $reference;
			$this->date       = $date;
			$this->sale_id    = $sale_id;
			$this->company_id = $company_id;
			$this->invoice_id = $invoice_id;
			$this->source     = $source;
		}

		/**
		 * Convert this object to a key => value array.
		 *
		 * @return array of key => value pairs of the class properties of this object.
		 */
		public function to_json(): array {
			return array(
				'id' => $this->id,
				'created_at' => $this->created_at->format( 'c' ),
				'updated_at' => $this->created_at->format( 'c' ),
				'amount' => number_format( $this->amount, 2, '.', '' ),
				'reference' => $this->reference,
				'date' => $this->date->format( 'c' ),
				'sale_id' => $this->sale_id,
				'company_id' => $this->company_id,
				'invoice_id' => $this->invoice_id,
				'source' => $this->source,
			);
		}
	}
}
