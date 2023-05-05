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
		 * Setup this class for a run() or synchronize_one().
		 *
		 * @return void
		 */
		abstract public function setup(): void;


		/**
		 * Setup the class objects for a run().
		 *
		 * @return void
		 */
		abstract public function setup_objects(): void;

		/**
		 * Method to synchronize multiple instances.
		 *
		 * The setup() and setup_objects() methods must be called before this method. The method after_run() must be called after this method.
		 *
		 * @return void
		 */
		abstract public function run(): void;

		abstract public function get_url( array $object ): string;

		abstract public function create_synchronized_object( array $object, bool $succeeded, ?string $error_message );

		/**
		 * Method to synchronize one instance.
		 *
		 * The setup() method must be called before this method.
		 *
		 * @param array $to_synchronize the data to synchronize.
		 *
		 * @throws SUCAPIException On Exception with the API.
		 * @return void
		 */
		abstract public function synchronize_one( array $to_synchronize ): void;

		/**
		 * Retrieve data of an object by its ID.
		 *
		 * @param int $id the ID of the object to retrieve.
		 *
		 * @throws SUCAPIException On Exception with the API.
		 * @return array
		 */
		abstract public function retrieve_object( int $id ): array;

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
