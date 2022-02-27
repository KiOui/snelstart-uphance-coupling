<?php
/**
 * Snelstart Grootboek mutatie.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCSnelstartGrootboekmutatie' ) ) {
	/**
	 * Snelstart Grootboekmutatie class.
	 *
	 * @class SUCSnelstartGrootboekmutatie
	 */
	class SUCSnelstartGrootboekmutatie {

		/**
		 * The grootboek.
		 *
		 * @var array
		 */
		public array $grootboek;

		/**
		 * The kostenplaats.
		 *
		 * @var array
		 */
		public array $kostenplaats;

		/**
		 * The date of te mutation.
		 *
		 * @var DateTime
		 */
		public DateTime $datum;

		/**
		 * The date this mutation was last modified on.
		 *
		 * @var DateTime
		 */
		public DateTime $modified_on;

		/**
		 * The dagboek.
		 *
		 * @var array
		 */
		public array $dagboek;

		/**
		 * The description.
		 *
		 * @var string
		 */
		public string $omschrijving;

		/**
		 * The debet amount.
		 *
		 * @var float
		 */
		public float $debet;

		/**
		 * The credit amount.
		 *
		 * @var float
		 */
		public float $credit;

		/**
		 * The saldo.
		 *
		 * @var float
		 */
		public float $saldo;

		/**
		 * The documents.
		 *
		 * @var array
		 */
		public array $documents;

		/**
		 * The boekstuk.
		 *
		 * @var string|null
		 */
		public ?string $boekstuk;

		/**
		 * The invoice number.
		 *
		 * @var string|null
		 */
		public ?string $factuur_nummer;

		/**
		 * The relation identifier.
		 *
		 * @var array|null
		 */
		public ?array $relatie_public_identifier;

		/**
		 * The id.
		 *
		 * @var string
		 */
		public string $id;

		/**
		 * The uri.
		 *
		 * @var string
		 */
		public string $uri;

		/**
		 * Constructor.
		 *
		 * @param array    $grootboek the grootboek.
		 * @param array    $kostenplaats the kostenplaats.
		 * @param DateTime $datum the date.
		 * @param DateTime $modified_on the date this was last modified on.
		 * @param array    $dagboek the dagboek.
		 * @param string   $omschrijving the description.
		 * @param float    $debet the debet amount.
		 * @param float    $credit the credit amount.
		 * @param float    $saldo the saldo.
		 * @param array    $documents the documents.
		 * @param ?string  $boekstuk the boekstuk.
		 * @param ?string  $factuur_nummer the invoice number.
		 * @param ?array   $relatie_public_identifier the relation identifier.
		 * @param string   $id the id.
		 * @param string   $uri the uri.
		 */
		public function __construct( array $grootboek, array $kostenplaats, DateTime $datum, DateTime $modified_on, array $dagboek, string $omschrijving, float $debet, float $credit, float $saldo, array $documents, ?string $boekstuk, ?string $factuur_nummer, ?array $relatie_public_identifier, string $id, string $uri ) {
			$this->grootboek               = $grootboek;
			$this->kostenplaats            = $kostenplaats;
			$this->datum                   = $datum;
			$this->modified_on              = $modified_on;
			$this->dagboek                 = $dagboek;
			$this->omschrijving            = $omschrijving;
			$this->debet                   = $debet;
			$this->credit                  = $credit;
			$this->saldo                   = $saldo;
			$this->documents               = $documents;
			$this->boekstuk                = $boekstuk;
			$this->factuur_nummer           = $factuur_nummer;
			$this->relatie_public_identifier = $relatie_public_identifier;
			$this->id                      = $id;
			$this->uri                     = $uri;
		}

		/**
		 * Convert an array to a SUCSnelstartGrootboekmutatie.
		 *
		 * @return SUCSnelstartGrootboekmutatie the Grootboekmutatie object.
		 * @throws Exception When a DateTime could not be created.
		 */
		public static function from_snelstart( array $from_snelstart ): SUCSnelstartGrootboekmutatie {
			return new SUCSnelstartGrootboekmutatie(
				$from_snelstart['grootboek'],
				is_null( $from_snelstart['kostenplaats'] ) ? array() : $from_snelstart['kostenplaats'],
				new DateTime( $from_snelstart['datum'] ),
				new DateTime( $from_snelstart['modified_on'] ),
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

		/**
		 * Convert this object to string.
		 *
		 * @return string a string-like representation of this object.
		 */
		public function __toString() {
			return sprintf( 'Snelstart Grootboekmutatie %s (invoice: %s)', $this->id, $this->factuur_nummer );
		}
	}
}
