<?php
/**
 * Snelstart Client class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/client/class-sucapiclient.php';
include_once SUC_ABSPATH . 'includes/snelstart/class-sucsnelstartauthclient.php';

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
		protected string $prefix = 'https://b2bapi.snelstart.nl/v2/';

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
		 * Maximum amount of results to get per request.
		 *
		 * @var int
		 */
		protected static int $maximum_results = 500;

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

				if ( isset( $snelstart_key ) && isset( $subscription_key ) && '' !== $snelstart_key && '' !== $subscription_key ) {
					self::$_instance = new self( $subscription_key, new SUCSnelstartAuthClient( $snelstart_key ) );
				} else {
					return null;
				}
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @param string           $subscription_key the subscription key to use for all requests.
		 * @param SUCAPIAuthClient $auth_client the Auth client to use for all requests.
		 * @param int              $requests_timeout the timeout of the requests.
		 */
		public function __construct( string $subscription_key, SUCAPIAuthClient $auth_client, int $requests_timeout = 45 ) {
			parent::__construct( $auth_client, $requests_timeout );
			$this->requests_timeout = $requests_timeout;
			$this->subscription_key = $subscription_key;
		}

		/**
		 * Overwrite auth_headers function to add Ocp-Apim-Subscription-Key.
		 *
		 * @return array auth headers to use for all requests.
		 * @throws SUCAPIException When access token could not be get from Auth client.
		 */
		protected function auth_headers(): array {
			if ( ! isset( $this->auth_manager ) ) {
				return array( 'Ocp-Apim-Subscription-Key' => $this->subscription_key );
			} else {
				return array(
					'Authorization' => 'Bearer ' . $this->auth_manager->get_access_token(),
					'Ocp-Apim-Subscription-Key' => $this->subscription_key,
				);
			}
		}

		/**
		 * Get all results for a given endpoint.
		 *
		 * @param callable $function a function that requests resources at an endpoint.
		 * @param ?int     $maximum the maximum amount of results to get.
		 * @param mixed    ...$args other arguments for the $function.
		 *
		 * @return array an array of results.
		 */
		public function get_all( callable $function, ?int $maximum = null, ...$args ): array {
			$results = array();
			do {
				$result = $function( count( $results ), ( is_null( $maximum ) ? null : min( $maximum - count( $results ), self::$maximum_results ) ), ...$args );
				$results = array_merge( $results, $result );
				$amount_of_results = count( $result );
				$amount_of_results_total = count( $results );
			} while ( 0 !== $amount_of_results && ( is_null( $maximum ) || $amount_of_results_total < $maximum ) );
			return $results;
		}

		/**
		 * Get Bankboekingen.
		 *
		 * @throws SUCAPIException On exception with API request.
		 */
		public function bankboekingen(): array {
			return $this->_get( 'bankboekingen', null, null );
		}

		/**
		 * Add a verkoopboeking.
		 *
		 * @throws SUCAPIException On exception with API request.
		 */
		public function add_verkoopboeking( string $factuurnummer, string $klant, $factuurbedrag, int $betalingstermijn, array $boekingsregels, array $btw_regels, DateTime $date ): array {
			return $this->_post(
				'verkoopboekingen',
				null,
				array(
					'factuurnummer' => $factuurnummer,
					'klant' => array(
						'id' => $klant,
					),
					'boekingsregels' => $boekingsregels,
					'factuurbedrag' => $factuurbedrag,
					'betalingstermijn' => $betalingstermijn,
					'factuurdatum' => $date->format( 'Y-m-d H:i:s' ),
					'btw' => $btw_regels,
				)
			);
		}

		/**
		 * Get grootboeken.
		 *
		 * @throws SUCAPIException On exception with API request.
		 */
		public function grootboeken(): array {
			return $this->_get( 'grootboeken', null, null );
		}

		/**
		 * Get grootboekmutaties.
		 *
		 * @throws SUCAPIException On exception with API request.
		 */
		public function grootboekmutaties( int $skip = null, int $top = null, string $filter = null, string $expand = null ): array {
			$queries = array(
				'$skip' => $skip,
				'$top' => $top,
				'$filter' => $filter,
				'$expand' => $expand,
			);
			$querystring = $this->create_querystring( $queries );
			return $this->_get( 'grootboekmutaties' . $querystring, null, null );
		}

		/**
		 * Get relaties.
		 *
		 * @throws SUCAPIException On exception with API request.
		 */
		public function relaties( int $skip = null, int $top = null, string $filter = null, string $expand = null ): array {
			$queries = array(
				'$skip' => $skip,
				'$top' => $top,
				'$filter' => $filter,
				'$expand' => $expand,
			);
			$querystring = $this->create_querystring( $queries );
			return $this->_get( 'relaties' . $querystring, null, null );
		}

		/**
		 * Add a relatie.
		 *
		 * @param array $parameters The parameters for the request.
		 *
		 * $@throws SUCAPIException On exception with API request.
		 */
		public function add_relatie( array $parameters ): array {
			return $this->_post(
				'relaties',
				null,
				$parameters
			);
		}

		/**
		 * Get BTW tarieven.
		 *
		 * @throws SUCAPIException On exception with API request.
		 */
		public function btwtarieven(): array {
			return $this->_get( 'btwtarieven', null, null );
		}

		/**
		 * Add a document.
		 *
		 * @param string $type the document type (for in the URL).
		 * @param string $content the document content in bytes.
		 * @param string $parent_identifier the identifier of the parent to couple the document to.
		 * @param string $file_name the file name of the file.
		 * @param bool   $read_only whether the file should be read-only.
		 *
		 * @return array
		 * @throws SUCAPIException On exception with API request.
		 */
		private function add_document( string $type, string $content, string $parent_identifier, string $file_name, bool $read_only ) {
			return $this->_post(
				"documenten/$type",
				null,
				array(
					'content' => $content,
					'parentIdentifier' => $parent_identifier,
					'fileName' => $file_name,
					'readOnly' => $read_only,
				)
			);
		}

		/**
		 * Add a verkoopboeking document.
		 *
		 * @param string $content the document content in bytes.
		 * @param string $parent_identifier the identifier of the parent to couple the document to.
		 * @param string $file_name the file name of the file.
		 * @param bool   $read_only whether the file should be read-only.
		 *
		 * @return array
		 * @throws SUCAPIException On exception with API request.
		 */
		public function add_document_verkoopboeking( string $content, string $parent_identifier, string $file_name, bool $read_only ) {
			return $this->add_document( 'Verkoopboekingen', $content, $parent_identifier, $file_name, $read_only );
		}

		/**
		 * Get Landen.
		 *
		 * @throws SUCAPIException On exception with API request.
		 */
		public function landen(): array {
			return $this->_get(
				'landen',
				null,
				null
			);
		}
	}
}
