<?php

if ( ! class_exists( 'SUCSnelstartGrootboek' ) ) {
	class SUCSnelstartGrootboek {

		public ?DateTime $modifiedOn;
		public ?string $omschrijving;
		public ?bool $kostenplaatsVerplicht;
		public ?string $rekeningCode;
		public ?bool $nonactief;
		public ?int $nummer;
		public ?string $grootboekfunctie;
		public ?string $grootboekRubriek;
		public array $rsgCode;
		public array $btwSoort;
		public ?string $vatRateCode;
		public string $id;
		public string $uri;

		/**
		 * @param DateTime|null $modifiedOn
		 * @param string|null $omschrijving
		 * @param bool|null $kostenplaatsVerplicht
		 * @param string|null $rekeningCode
		 * @param bool|null $nonactief
		 * @param int|null $nummer
		 * @param string|null $grootboekfunctie
		 * @param string|null $grootboekRubriek
		 * @param array $rsgCode
		 * @param array $btwSoort
		 * @param string|null $vatRateCode
		 * @param string $id
		 * @param string $uri
		 */
		public function __construct( ?DateTime $modifiedOn, ?string $omschrijving, ?bool $kostenplaatsVerplicht, ?string $rekeningCode, ?bool $nonactief, ?int $nummer, ?string $grootboekfunctie, ?string $grootboekRubriek, array $rsgCode, array $btwSoort, ?string $vatRateCode, string $id, string $uri ) {
			$this->modifiedOn            = $modifiedOn;
			$this->omschrijving          = $omschrijving;
			$this->kostenplaatsVerplicht = $kostenplaatsVerplicht;
			$this->rekeningCode          = $rekeningCode;
			$this->nonactief             = $nonactief;
			$this->nummer                = $nummer;
			$this->grootboekfunctie      = $grootboekfunctie;
			$this->grootboekRubriek      = $grootboekRubriek;
			$this->rsgCode               = $rsgCode;
			$this->btwSoort              = $btwSoort;
			$this->vatRateCode           = $vatRateCode;
			$this->id                    = $id;
			$this->uri                   = $uri;
		}


	}
}