<?php
/**
 * Synchronizer class.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCSynchronizer' ) ) {
	/**
	 * Synchronizer class.
	 *
	 * @class SUCSynchronizer
	 */
	class SUCSynchronizer {

		/**
		 * Registered synchronizer classes.
		 *
		 * @var array
		 */
		public static array $synchronizer_classes = array();

		/**
		 * Register a class extending Synchronisable to the $synchronizer_classes.
		 *
		 * @param string $type a name of the type to register.
		 * @param mixed  $class the class to use for the registered name.
		 *
		 * @return void
		 */
		public static function register_synchronizer_class( string $type, $class ) {
			self::$synchronizer_classes[ $type ] = $class;
		}

		public static function get_synchronizer_class( string $type ): ?SUCSynchronisable {
			if ( array_key_exists( $type, self::$synchronizer_classes ) ) {
				return self::$synchronizer_classes[ $type ];
			} else {
				return null;
			}
		}
	}
}
