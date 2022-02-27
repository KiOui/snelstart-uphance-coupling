<?php
/**
 * Snelstart Grootboek.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCSnelstartGrootboek' ) ) {
	/**
	 * Snelstart Grootboek class.
	 *
	 * @class SUCSnelstartGrootboek
	 */
	class SUCSnelstartGrootboek {

		/**
		 * When the grootboek was last modified.
		 *
		 * @var DateTime|null
		 */
		public ?DateTime $modified_on;

		/**
		 * Description.
		 *
		 * @var string|null
		 */
		public ?string $omschrijving;

		/**
		 * Whether a kostenplaats is mandatory.
		 *
		 * @var bool|null
		 */
		public ?bool $kostenplaats_verplicht;

		/**
		 * The account code.
		 *
		 * @var string|null
		 */
		public ?string $rekening_code;

		/**
		 * Whether grootboek is not active.
		 *
		 * @var bool|null
		 */
		public ?bool $nonactief;

		/**
		 * Grootboek number.
		 *
		 * @var int|null
		 */
		public ?int $nummer;

		/**
		 * Grootboek function.
		 *
		 * @var string|null
		 */
		public ?string $grootboekfunctie;

		/**
		 * Grootboek rubriek.
		 *
		 * @var string|null
		 */
		public ?string $grootboek_rubriek;

		/**
		 * RSG Code.
		 *
		 * @var array
		 */
		public array $rsg_code;

		/**
		 * Tax type.
		 *
		 * @var array
		 */
		public array $btw_soort;

		/**
		 * Tax rate code.
		 *
		 * @var string|null
		 */
		public ?string $vat_rate_code;

		/**
		 * ID.
		 *
		 * @var string
		 */
		public string $id;

		/**
		 * URI.
		 *
		 * @var string
		 */
		public string $uri;

		/**
		 * Constructor.
		 *
		 * @param DateTime|null $modified_on when the Grootboek was last modified.
		 * @param string|null   $omschrijving the description.
		 * @param bool|null     $kostenplaats_verplicht whether a kostenplaats is mandatory.
		 * @param string|null   $rekening_code the account code.
		 * @param bool|null     $nonactief whether the grootboek is not active.
		 * @param int|null      $nummer the grootboek number.
		 * @param string|null   $grootboekfunctie the grootboek function.
		 * @param string|null   $grootboek_rubriek the grootboek rubriek.
		 * @param array         $rsg_code the RSG code.
		 * @param array         $btw_soort the tax type.
		 * @param string|null   $vat_rate_code the tax rate code.
		 * @param string        $id the ID.
		 * @param string        $uri the URI.
		 */
		public function __construct( ?DateTime $modified_on, ?string $omschrijving, ?bool $kostenplaats_verplicht, ?string $rekening_code, ?bool $nonactief, ?int $nummer, ?string $grootboekfunctie, ?string $grootboek_rubriek, array $rsg_code, array $btw_soort, ?string $vat_rate_code, string $id, string $uri ) {
			$this->modified_on  = $modified_on;
			$this->omschrijving = $omschrijving;
			$this->kostenplaats_verplicht = $kostenplaats_verplicht;
			$this->rekening_code          = $rekening_code;
			$this->nonactief             = $nonactief;
			$this->nummer                = $nummer;
			$this->grootboekfunctie      = $grootboekfunctie;
			$this->grootboek_rubriek      = $grootboek_rubriek;
			$this->rsg_code               = $rsg_code;
			$this->btw_soort              = $btw_soort;
			$this->vat_rate_code           = $vat_rate_code;
			$this->id                    = $id;
			$this->uri                   = $uri;
		}


	}
}
