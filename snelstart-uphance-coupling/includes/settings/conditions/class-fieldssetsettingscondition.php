<?php

include_once SUC_ABSPATH . 'includes/settings/conditions/class-settingscondition.php';

class FieldsSetSettingsCondition extends SettingsCondition {

	private array $field_names;

	public function __construct( array $field_names ) {
		$this->field_names = $field_names;
	}

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