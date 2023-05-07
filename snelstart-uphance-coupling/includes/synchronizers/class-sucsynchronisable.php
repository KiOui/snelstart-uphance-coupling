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
		 * @throws Exception When setup of the class fails.
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

		/**
		 * Get the URL of an object.
		 *
		 * @param array $object The object to get the URL for.
		 *
		 * @return string A URL pointing to the Uphance resource.
		 */
		abstract public function get_url( array $object ): string;

		/**
		 * Check whether a successful synchronization already took place via another method.
		 *
		 * @param int $id The ID of the invoice.
		 *
		 * @return bool Whether the invoice was already successfully synchronized.
		 */
		public function object_already_successfully_synchronized( int $id ): bool {
			$posts = get_posts(
				array(
					'post_type' => 'suc_synchronized',
					'meta_query' => array(
						array(
							'key'     => 'id',
							'value'   => $id,
							'compare' => '=',
						),
						array(
							'key' => 'succeeded',
							'value' => true,
							'compare' => '=',
						),
						array(
							'key' => 'type',
							'value' => $this::$type,
							'compare' => '=',
						),
					),
				)
			);
			return count( $posts ) > 0;
		}

		/**
		 * Create a synchronized object.
		 *
		 * @param array       $object The object.
		 * @param bool        $succeeded Whether the synchronization succeeded.
		 * @param string      $source The source of the synchronization.
		 * @param string      $method The method of the synchronization.
		 * @param string|null $error_message A possible error message that occurred during synchronization.
		 *
		 * @return void
		 */
		abstract public function create_synchronized_object( array $object, bool $succeeded, string $source, string $method, ?string $error_message );

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
