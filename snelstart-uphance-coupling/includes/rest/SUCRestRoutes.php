<?php
/**
 * Rest Routes class.
 *
 * This class contains all REST Routes in the plugin.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . '/includes/synchronizers/class-sucsynchronizer.php';

if ( ! class_exists( 'SUCRestRoutes' ) ) {
	/**
	 * Rest Routes class.
	 *
	 * @class SUCRestRoutes
	 */
	class SUCRestRoutes {
		/**
		 * Registered REST routes.
		 *
		 * @var array
		 */
		public static array $rest_routes = array();

		/**
		 * Register a class extending SUCRestRoute to the $rest_routes.
		 *
		 * @param string $type a name of the type to register.
		 * @param mixed  $class the class to use for the registered name.
		 *
		 * @return void
		 */
		public static function register_rest_route( string $type, $class ) {
			self::$rest_routes[ $type ] = $class;
		}

		/**
		 * Retrieve a REST route from the store.
		 *
		 * @param string $type The type of the REST Route to retrieve.
		 *
		 * @return SUCRestRoute|null Either an instance of SUCRestRoute or null when the type was not found.
		 */
		public static function get_rest_route( string $type ): ?SUCRestRoute {
			if ( array_key_exists( $type, self::$rest_routes ) ) {
				return self::$rest_routes[ $type ];
			} else {
				return null;
			}
		}

		/**
		 * Registers REST routes for each class in this store.
		 *
		 * This function is hooked into rest_api_init.
		 *
		 * @return void
		 */
		public static function register_rest_routes() {
			foreach ( self::$rest_routes as $rest_route ) {
				$rest_route->add_rest_api_endpoints();
			}
		}
	}
}
