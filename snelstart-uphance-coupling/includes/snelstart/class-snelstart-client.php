<?php
/**
 * Snelstart Client class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-api-client.php';
include_once SUC_ABSPATH . 'includes/snelstart/class-snelstart-auth-client.php';

if ( ! class_exists( 'SUCSnelstartClient' ) ) {
	/**
	 * Snelstart Client class
	 *
	 * @class SUCSnelstartClient
	 */
	class SUCSnelstartClient extends SUCAPIClient {

		/**
		 * The URL endpoint for the Snelstart API.
		 *
		 * @var string
		 */
		protected string $prefix = "https://b2bapi.snelstart.nl/v2/";

		/**
		 * Subscription key.
		 *
		 * @var string
		 */
		protected string $subscription_key;

		/**
		 * Snelstart Client instance.
		 *
		 * @var SUCSnelstartClient|null
		 */
		protected static ?SUCSnelstartClient $_instance = null;

		/**
		 * Snelstart instance.
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return ?SUCSnelstartClient the client if all required settings are set, null otherwise
		 */
		public static function instance(): ?SUCSnelstartClient {
			if ( is_null( self::$_instance ) ) {
				$settings = get_option( 'suc_settings', null );
				if ( ! isset( $settings ) ) {
					return null;
				}
				$snelstart_key = $settings['snelstart_client_key'];
				$subscription_key = $settings['snelstart_subscription_key'];

				if ( isset( $snelstart_key ) && isset( $subscription_key ) && $snelstart_key !== "" && $subscription_key !== "") {
					self::$_instance = new self($subscription_key, new SUCSnelstartClientKey( $snelstart_key ) );
				} else {
					return null;
				}
			}

			return self::$_instance;
		}

		/**
		 * @param string $subscription_key
		 * @param SUCAPIAuthClient $auth_client
		 * @param int $requests_timeout
		 */
		public function __construct(string $subscription_key, SUCAPIAuthClient $auth_client, int $requests_timeout=45) {
			parent::__construct( $auth_client, $requests_timeout);
			$this->requests_timeout = $requests_timeout;
			$this->subscription_key = $subscription_key;
		}

		protected function auth_headers(): array {
			if ( ! isset( $this->auth_manager ) ) {
				return array("Ocp-Apim-Subscription-Key" => $this->subscription_key);
			}
			else {
				return array("Authorization" => "Bearer ". $this->auth_manager->get_access_token(), "Ocp-Apim-Subscription-Key" => $this->subscription_key);
			}
		}

		/**
		 * @throws SUCAPIException
		 */
		public function bankboekingen(): array {
			return $this->_get('bankboekingen', null, null);
		}

		/**
		 * @throws SUCAPIException
		 */
		public function add_verkoopboeking(string $factuurnummer, string $klant, float|string $factuurbedrag, array $boekingsregels, array $btw_regels): array {
			return $this->_post('verkoopboekingen', null, array(
				"factuurnummer" => $factuurnummer,
				"klant" => array(
					"id" => $klant,
				),
				"boekingsregels" => $boekingsregels,
				"factuurbedrag" => $factuurbedrag,
				"factuurdatum" => date("Y-m-d H:i:s"),
				"btw" => $btw_regels,
			));
		}

		/**
		 * @throws SUCAPIException
		 */
		public function grootboeken(): array {
			return $this->_get('grootboeken', null, null);
		}

		/**
		 * @throws SUCAPIException
		 */
		public function relaties(int $skip = null, int $top = null, string $filter = null): array {
			$queries = array(
				"\$skip" => $skip,
				"\$top" => $top,
				"\$filter" => $filter,
			);
			$querystring = $this->create_querystring($queries);
			return $this->_get('relaties' . $querystring, null, null);
		}

		/**
		 * @throws SUCAPIException
		 */
		public function btwtarieven(): array {
			return $this->_get('btwtarieven', null, null);
		}
	}
}
