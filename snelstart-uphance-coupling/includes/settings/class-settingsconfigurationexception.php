<?php
/**
 * Settings Configuration Exception.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SettingsConfigurationException' ) ) {
	/**
	 * Settings Configuration Exception.
	 *
	 * @class SettingsConfigurationException
	 */
	class SettingsConfigurationException extends Exception {

	}
}
