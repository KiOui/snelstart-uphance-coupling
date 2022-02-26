<?php


if ( ! class_exists( 'SUCSnelstartGrootboekmutatie' ) ) {
	class SUCSnelstartGrootboekmutatie {

		public array $grootboek;
		public array $kostenplaats;
		public DateTime $datum;
		public DateTime $modifiedOn;
		public array $dagboek;
		public string $omschrijving;
		public float $debet;
		public float $credit;
		public float $saldo;
		public array $documents;
		public ?string $boekstuk;
		public ?string $factuurNummer;
		public ?array $relatiePublicIdentifier;
		public string $id;
		public string $uri;

		/**
		 * @param array    $grootboek
		 * @param array    $kostenplaats
		 * @param DateTime $datum
		 * @param DateTime $modifiedOn
		 * @param array    $dagboek
		 * @param string   $omschrijving
		 * @param float    $debet
		 * @param float    $credit
		 * @param float    $saldo
		 * @param array    $documents
		 * @param ?string  $boekstuk
		 * @param ?string  $factuurNummer
		 * @param ?array   $relatiePublicIdentifier
		 * @param string   $id
		 * @param string   $uri
		 */
		public function __construct( array $grootboek, array $kostenplaats, DateTime $datum, DateTime $modifiedOn, array $dagboek, string $omschrijving, float $debet, float $credit, float $saldo, array $documents, ?string $boekstuk, ?string $factuurNummer, ?array $relatiePublicIdentifier, string $id, string $uri ) {
			$this->grootboek               = $grootboek;
			$this->kostenplaats            = $kostenplaats;
			$this->datum                   = $datum;
			$this->modifiedOn              = $modifiedOn;
			$this->dagboek                 = $dagboek;
			$this->omschrijving            = $omschrijving;
			$this->debet                   = $debet;
			$this->credit                  = $credit;
			$this->saldo                   = $saldo;
			$this->documents               = $documents;
			$this->boekstuk                = $boekstuk;
			$this->factuurNummer           = $factuurNummer;
			$this->relatiePublicIdentifier = $relatiePublicIdentifier;
			$this->id                      = $id;
			$this->uri                     = $uri;
		}

		/**
		 * @throws Exception
		 */
		public static function from_snelstart( array $from_snelstart ): SUCSnelstartGrootboekmutatie {
			return new SUCSnelstartGrootboekmutatie(
				$from_snelstart['grootboek'],
				is_null( $from_snelstart['kostenplaats'] ) ? array() : $from_snelstart['kostenplaats'],
				new DateTime( $from_snelstart['datum'] ),
				new DateTime( $from_snelstart['modifiedOn'] ),
				$from_snelstart['dagboek'],
				is_null( $from_snelstart['omschrijving'] ) ? '' : $from_snelstart['omschrijving'],
				floatval( $from_snelstart['debet'] ),
				floatval( $from_snelstart['credit'] ),
				floatval( $from_snelstart['saldo'] ),
				$from_snelstart['documents'],
				$from_snelstart['boekstuk'],
				$from_snelstart['factuurNummer'],
				$from_snelstart['relatiePublicIdentifier'],
				$from_snelstart['id'],
				$from_snelstart['uri']
			);
		}

		public function __toString() {
			return sprintf( 'Snelstart Grootboekmutatie %s (invoice: %s)', $this->id, $this->factuurNummer );
		}
	}
}
