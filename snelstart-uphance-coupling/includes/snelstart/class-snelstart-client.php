<?php
/**
 * Snelstart Client class
 *
 * @package snelstart-uphance-coupling
 */

use JetBrains\PhpStorm\Pure;

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

		protected string $prefix = "https://b2bapi.snelstart.nl/v2/";
		protected string $subscription_key;

		protected static ?SUCSnelstartClient $_instance = null;

		/**
		 * Snelstart Uphance Coupling Core.
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return ?SUCSnelstartClient
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

		public function __construct(string $subscription_key, SUCAPIAuthClient $auth_client, int $requests_timeout=45) {
			parent::__construct( $auth_client, $requests_timeout);
			$this->requests_timeout = $requests_timeout;
			$this->subscription_key = $subscription_key;
		}

		protected function _auth_headers(): array {
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

		public function add_verkoopboeking(string $factuurnummer, string $klant, array $boekingsregels) {
			return $this->_post('verkoopboekingen', null, array(
				"factuurnummer" => $factuurnummer,
				"klant" => array(
					"id" => $klant,
				),
				"boekingsregels" => $boekingsregels,
				"factuurdatum" => date("Y-m-d H:i:s"),
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
		public function relaties(): array {
			return $this->_get('relaties', null, null);
		}
	}
}
