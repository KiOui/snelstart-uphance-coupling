<?php
/**
 * Synchronisable interface.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'SUCSynchronisable' ) ) {
	/**
	 * Synchronisable Interface.
	 *
	 * @class SUCSynchronisable
	 */
	abstract class SUCSynchronisable {

		/**
		 * The Uphance client to use for the synchronizer.
		 *
		 * @var SUCUphanceClient
		 */
		protected SUCUphanceClient $uphance_client;

		/**
		 * The Snelstart client to use for the synchronizer.
		 *
		 * @var SUCSnelstartClient
		 */
		protected SUCSnelstartClient $snelstart_client;

		/**
		 * Constructor.
		 *
		 * @param SUCuphanceClient   $uphance_client the Uphance client.
		 * @param SUCSnelstartClient $snelstart_client the Snelstart client.
		 */
		public function __construct( SUCuphanceClient $uphance_client, SUCSnelstartClient $snelstart_client ) {
			$this->uphance_client = $uphance_client;
			$this->snelstart_client = $snelstart_client;
		}

		/**
		 * Setup this class for a run() or synchronize_one().
		 *
		 * @return void
		 */
		abstract public function setup(): void;

		/**
		 * Method to synchronize multiple instances.
		 *
		 * The method setup() must be called before this method. The method after_run() must be called after this method.
		 *
		 * @return void
		 */
		abstract public function run(): void;

		/**
		 * Method to synchronize one instance.
		 *
		 * The method setup() must be called before this method.
		 *
		 * @param string $id the ID of the instance to synchronize.
		 *
		 * @return void
		 */
		abstract public function synchronize_one( string $id): void;

		/**
		 * Actions to execute after a run.
		 *
		 * @return void
		 */
		abstract public function after_run(): void;

		/**
		 * Whether this synchronizer should be enabled.
		 *
		 * @return bool whether this synchronizer is enabled.
		 */
		abstract public function enabled(): bool;
	}
}
