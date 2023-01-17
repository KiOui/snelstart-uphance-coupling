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
		 * @param ?int          $default the default value of the setting.
		 * @param bool          $can_be_null whether the setting can be null.
		 * @param string        $hint the hint to display next to the setting.
		 * @param int|null      $minimum the minimum value for the IntField, when null no minimum value is specified.
		 * @param int|null      $maximum the maximum value for the IntField, when null no maximum value is specified.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false or when $minimum is
		 * larger than $maximum or when $minimum is smaller than 0.
		 */
		public function __construct( string $id, string $name, ?int $default, ?callable $renderer = null, bool $can_be_null = false, string $hint = '', ?int $minimum = null, ?int $maximum = null, ?array $conditions = null ) {
			if ( isset( $minimum ) && $minimum < 0 ) {
				throw new SettingsConfigurationException( 'A positive integer field can not have a negative minimum.' );
			}

			if ( is_null( $minimum ) ) {
				$minimum = 0;
			}

			if ( is_null( $conditions ) ) {
				$conditions = array();
			}

			parent::__construct( $id, $name, $default, $renderer, $can_be_null, $hint, $minimum, $maximum, $conditions );
		}
	}
}
