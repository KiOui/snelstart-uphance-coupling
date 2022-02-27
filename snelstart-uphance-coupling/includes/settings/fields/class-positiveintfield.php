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

if ( ! class_exists( 'PositiveIntField' ) ) {
	/**
	 * Positive Integer Field for Settings.
	 *
	 * @class PositiveIntField
	 */
	class PositiveIntField extends IntField {

		/**
		 * Constructor of PositiveIntField.
		 *
		 * @param string        $id the slug-like ID of the setting.
		 * @param string        $name the name of the setting.
		 * @param callable|null $renderer the custom renderer of the SettingsField.
		 * @param ?int          $default the default value of the setting.
		 * @param bool          $can_be_null whether the setting can be null.
		 * @param string        $hint the hint to display next to the setting.
		 * @param int|null      $minimum the minimum value for the IntField, when null no minimum value is specified.
		 * @param int|null      $maximum the maximum value for the IntField, when null no maximum value is specified.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false or when $minimum is
		 * larger than $maximum or when $minimum is smaller than 0.
		 */
		public function __construct( string $id, string $name, ?callable $renderer, ?int $default, bool $can_be_null = false, string $hint = '', ?int $minimum = null, ?int $maximum = null ) {
			if ( isset( $minimum ) && $minimum < 0 ) {
				throw new SettingsConfigurationException( 'A positive integer field can not have a negative minimum.' );
			}

			if ( is_null( $minimum ) ) {
				$minimum = 0;
			}

			parent::__construct( $id, $name, $renderer, $default, $can_be_null, $hint, $minimum, $maximum );
		}

		/**
		 * Create a PositiveIntField from an array of values.
		 *
		 * @param array $initial_values values to pass to PositiveIntField constructor.
		 *
		 * @return PositiveIntField the created PositiveIntField.
		 * @throws SettingsConfigurationException When PositiveIntField creation failed.
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
