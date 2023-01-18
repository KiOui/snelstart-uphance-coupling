<?php
/**
 * Field Set Settings Condition.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/conditions/class-settingscondition.php';

if ( ! class_exists( 'FieldsSetSettingsCondition' ) ) {
	/**
	 * Fieldset Settings Condition class.
	 *
	 * @class FieldSetSettingsCondition
	 */
	class FieldsSetSettingsCondition extends SettingsCondition {

		/**
		 * The field names to check is_set for.
		 *
		 * @var array
		 */
		private array $field_names;

		/**
		 * Construct a FieldsSetSettingsCondition class instance.
		 *
		 * @param array $field_names The field names to check is_set for.
		 */
		public function __construct( array $field_names ) {
			$this->field_names = $field_names;
		}

		/**
		 * Whether this condition holds.
		 *
		 * @param Settings $settings The setting values.
		 *
		 * @return bool True if this condition holds, false otherwise.
		 */
		public function holds( Settings $settings ): bool {
			foreach ( $this->field_names as $field_name ) {
				$field = $settings->get_field( $field_name );
				if ( is_null( $field ) ) {
					return false;
				}
				if ( ! $field->is_set() ) {
					return false;
				}
			}

			return true;
		}
	}
}
