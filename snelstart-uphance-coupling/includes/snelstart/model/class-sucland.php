<?php
/**
 * Snelstart Land.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCLand' ) ) {
	/**
	 * Snelstart Land class.
	 *
	 * @class SUCLand
	 */
	class SUCLand {

		/**
		 * Country name.
		 *
		 * @var string
		 */
		public string $naam;

		/**
		 * Landcode ISO.
		 *
		 * @var ?string
		 */
		public ?string $landcode_iso;

		/**
		 * Landcode.
		 *
		 * @var string
		 */
		public string $landcode;

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
		 * @param string  $naam the name.
		 * @param ?string $landcode_iso the country code in ISO format.
		 * @param string  $landcode the country code.
		 * @param string  $id the ID.
		 * @param string  $uri the URI.
		 */
		public function __construct( string $naam, ?string $landcode_iso, string $landcode, string $id, string $uri ) {
			$this->naam = $naam;
			$this->landcode_iso = $landcode_iso;
			$this->landcode = $landcode;
			$this->id                    = $id;
			$this->uri                   = $uri;
		}


	}
}
