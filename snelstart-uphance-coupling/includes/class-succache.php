<?php
/**
 * Settings class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/uphance/SUCSendcloudClient.php';
include_once SUC_ABSPATH . 'includes/snelstart/class-sucsnelstartclient.php';
include_once SUC_ABSPATH . 'includes/suc-functions.php';

if ( ! class_exists( 'SUCCache' ) ) {
	/**
	 * Snelstart Uphance Coupling Cache class.
	 *
	 * @class SUCCache
	 */
	class SUCCache {

		/**
		 * The single instance of the class.
		 *
		 * @var SUCCache|null
		 */
		protected static ?SUCCache $_instance = null;

		/**
		 * Variable for storing grootboeken.
		 *
		 * Null when not retrieved yet, array if this variable holds valid grootboeken, false if retrieving failed.
		 *
		 * @var ?mixed
		 */
		private $cached_grootboeken = null;

		/**
		 * Variable for storing invoices.
		 *
		 * Null when not retrieved yet, array if this variable holds valid invoices, false if retrieving failed.
		 *
		 * @var ?mixed
		 */
		private $cached_invoices = null;

		/**
		 * Variable for storing credit notes.
		 *
		 * Null when not retrieved yet, array if this variable holds valid credit notes, false if retrieving failed.
		 *
		 * @var ?mixed
		 */
		private $cached_credit_notes = null;

		/**
		 * Variable for storing organisations.
		 *
		 * Null when not retrieved yet, array if this variable holds valid credit notes, false if retrieving failed.
		 *
		 * @var ?mixed
		 */
		private $cached_organisations = null;

		/**
		 * Uses the Singleton pattern to load 1 instance of this class at maximum.
		 *
		 * @static
		 * @return SUCCache
		 */
		public static function instance(): SUCCache {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Get grootboeken and set cache.
		 *
		 * @return array|null the grootboeken (array) if succeeded, null otherwise.
		 */
		public function get_grootboeken(): ?array {
			if ( isset( $this->cached_grootboeken ) ) {
				if ( false === $this->cached_grootboeken ) {
					return null;
				} else {
					return $this->cached_grootboeken;
				}
			} else {
				$snelstart_client = SUCSnelstartClient::instance();
				if ( isset( $snelstart_client ) ) {
					try {
						$this->cached_grootboeken = $snelstart_client->grootboeken();

						return $this->cached_grootboeken;
					} catch ( SUCAPIException $e ) {
						$this->cached_grootboeken = false;

						return null;
					}
				} else {
					$this->cached_grootboeken = false;

					return null;
				}
			}
		}

		/**
		 * Get invoices and set cache.
		 *
		 * @return array|null the invoices (array) if succeeded, null otherwise.
		 */
		public function get_invoices(): ?array {
			if ( isset( $this->cached_invoices ) ) {
				if ( false === $this->cached_invoices ) {
					return null;
				} else {
					return $this->cached_invoices;
				}
			} else {
				$uphance_client = SUCUphanceClient::instance();
				if ( isset( $uphance_client ) ) {
					try {
						$this->cached_invoices = $uphance_client->invoices()->result;

						return $this->cached_invoices;
					} catch ( SUCAPIException $e ) {
						$this->cached_invoices = false;

						return null;
					}
				} else {
					$this->cached_invoices = false;

					return null;
				}
			}
		}

		/**
		 * Get invoices and set cache.
		 *
		 * @return array|null the invoices (array) if succeeded, null otherwise.
		 */
		public function get_credit_notes(): ?array {
			if ( isset( $this->cached_credit_notes ) ) {
				if ( false === $this->cached_credit_notes ) {
					return null;
				} else {
					return $this->cached_credit_notes;
				}
			} else {
				$uphance_client = SUCUphanceClient::instance();
				if ( isset( $uphance_client ) ) {
					try {
						$this->cached_credit_notes = $uphance_client->credit_notes()->result;

						return $this->cached_credit_notes;
					} catch ( SUCAPIException $e ) {
						$this->cached_credit_notes = false;

						return null;
					}
				} else {
					$this->cached_credit_notes = false;

					return null;
				}
			}
		}


		/**
		 * Get organisations and set cache.
		 *
		 * @return array|null the organisations (array) if succeeded, null otherwise.
		 */
		public function get_organisations(): ?array {
			if ( isset( $this->cached_organisations ) ) {
				if ( false === $this->cached_organisations ) {
					return null;
				} else {
					return $this->cached_organisations;
				}
			} else {
				$uphance_client = SUCUphanceClient::instance();
				if ( isset( $uphance_client ) ) {
					try {
						$this->cached_organisations = $uphance_client->organisations();

						return $this->cached_organisations;
					} catch ( SUCAPIException $e ) {
						$this->cached_organisations = false;
						return null;
					}
				} else {
					$this->cached_organisations = false;

					return null;
				}
			}
		}
	}
}
