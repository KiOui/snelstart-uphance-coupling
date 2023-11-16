<?php
/**
 * Snelstart Landen.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/snelstart/model/class-sucland.php';

if ( ! class_exists( 'SUCLanden' ) ) {
	/**
	 * Snelstart Landen
	 *
	 * @class SUCLanden
	 */
	class SUCLanden {

		/**
		 * The only instance of this class.
		 *
		 * @var SUCLanden|null
		 */
		private static ?SUCLanden $instance = null;

		/**
		 * The saved countries in this class.
		 *
		 * @var array
		 */
		private array $countries;

		/**
		 * Get an instance of this class.
		 *
		 * @return static
		 * @throws SUCAPIException On Exception with the Snelstart API.
		 */
		public static function instance(): self {
			if ( is_null( self::$instance ) ) {
				$snelstart_client = SUCSnelstartClient::instance();
				$countries = $snelstart_client->landen();
				$countries_obj = array();
				foreach ( $countries as $country ) {
					$countries_obj[] = new SUCLand( $country['naam'], $country['landcodeISO'], $country['landcode'], $country['id'], $country['uri'] );
				}
				self::$instance = new SUCLanden( $countries_obj );
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @param array $countries an array of SUCLand objects.
		 */
		private function __construct( array $countries ) {
			$this->countries = $countries;
		}

		/**
		 * Retrieve an SUCLand by country_code.
		 *
		 * @param string $country_code the country code to retrieve.
		 *
		 * @return SUCLand|null An SUCLand with the same country_code or null on failure.
		 */
		public function get_country_id_from_country_code( string $country_code ): ?SUCLand {
			foreach ( $this->countries as $country ) {
				if ( $country->landcode === $country_code ) {
					return $country;
				}
			}
			return null;
		}
	}
}
