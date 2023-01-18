<?php
/**
 * Settings Condition.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'SettingsCondition' ) ) {
	/**
	 * Settings Condition class.
	 *
	 * @class SettingsCondition
	 */
	abstract class SettingsCondition {
		/**
		 * Whether this condition holds.
		 *
		 * @param Settings $settings The setting values to use.
		 *
		 * @return bool True when this condition holds, false otherwise.
		 */
		abstract public function holds( Settings $settings ): bool;
	}
}
