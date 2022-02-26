<?php

if ( ! class_exists( 'SUCUphancePayment' ) ) {
	class SUCUphancePayment {
		public int $id;
		public DateTime $created_at;
		public DateTime $updated_at;
		public float $amount;
		public string $reference;
		public DateTime $date;
		public int $sale_id;
		public ?int $company_id;
		public int $invoice_id;
		public string $source;

		/**
		 * @param int      $id
		 * @param DateTime $created_at
		 * @param DateTime $updated_at
		 * @param float    $amount
		 * @param string   $reference
		 * @param DateTime $date
		 * @param int      $sale_id
		 * @param int|null $company_id
		 * @param int      $invoice_id
		 * @param string   $source
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
