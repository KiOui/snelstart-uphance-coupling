<?php
/**
 * Positive Integer Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/fields/class-intfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( "PositiveIntField" ) ) {
	/**
	 * Positive Integer Field for Settings.
	 *
	 * @class PositiveIntField
	 */
	class PositiveIntField extends IntField {


		public function __construct( string $id, string $name, ?callable $renderer, ?int $default, bool $can_be_null = false, string $hint = "", ?int $minimum = null, ?int $maximum = null ) {
			if ( isset( $minimum ) && $minimum < 0 ) {
				throw new SettingsConfigurationException( "A positive integer field can not have a negative minimum." );
			}

			if ( is_null( $minimum ) ) {
				$minimum = 0;
			}

			parent::__construct( $id, $name, $renderer, $default, $can_be_null, $hint, $minimum, $maximum );
		}

		/**
		 * @throws SettingsConfigurationException
		 */
		public static function from_array( array $initial_values ): PositiveIntField {
			return new self(
				$initial_values['id'],
				$initial_values['name'],
				$initial_values['renderer'],
				$initial_values['default'],
				$initial_values['can_be_null'],
				$initial_values['hint'],
				$initial_values['minimum'],
				$initial_values['maximum'],
			);
		}
	}
}