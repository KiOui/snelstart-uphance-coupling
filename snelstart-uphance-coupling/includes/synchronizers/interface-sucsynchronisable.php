<?php
/**
 * Synchronisable interface.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'Synchronisable' ) ) {
	/**
	 * Synchronisable Interface.
	 *
	 * @class SUCSynchronizer
	 */
	abstract class Synchronisable {

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

		public function __construct( SUCuphanceClient $uphance_client, SUCSnelstartClient $snelstart_client ) {
			$this->uphance_client = $uphance_client;
			$this->snelstart_client = $snelstart_client;
		}
		
		abstract public function run(): bool;
		abstract public function synchronize_one(string $id);
	}
}
