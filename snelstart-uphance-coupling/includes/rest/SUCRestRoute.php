<?php
/**
 * REST Route class.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . '/includes/synchronizers/class-sucsynchronizer.php';

if ( ! class_exists( 'SUCRestRoute' ) ) {
	/**
	 * Abstract REST Route class.
	 *
	 * @class SUCRestRoute
	 */
	abstract class SUCRestRoute {
		/**
		 * Abstract function for adding REST API endpoints.
		 *
		 * @return void
		 */
		public abstract function add_rest_api_endpoints(): void;
	}
}
