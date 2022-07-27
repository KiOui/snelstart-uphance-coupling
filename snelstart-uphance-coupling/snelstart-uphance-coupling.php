<?php
/**
 * Plugin Name: Snelstart Uphance Coupling
 * Description: A utility to synchronize Snelstart and Uphance using API endpoints
 * Plugin URI: https://github.com/KiOui/snelstart-uphance-coupling
 * Version: 1.1.0
 * Author: Lars van Rhijn
 * Author URI: https://larsvanrhijn.nl/
 * Text Domain: snelstart-uphance-coupling
 * Domain Path: /languages/
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SUC_PLUGIN_FILE' ) ) {
	define( 'SUC_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'SUC_PLUGIN_URI' ) ) {
	define( 'SUC_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

include_once dirname( __FILE__ ) . '/includes/class-succore.php';

$GLOBALS['SUCCore'] = SUCCore::instance();
