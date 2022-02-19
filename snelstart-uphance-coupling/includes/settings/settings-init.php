<?php
/**
 * Settings initialize.
 *
 * @package snelstart-uphance-coupling
 */

include_once SUC_ABSPATH . 'includes/settings/class-settingsfactory.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-intfield.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-positiveintfield.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-boolfield.php';

if ( !function_exists( 'initialize_settings_fields' ) ) {
	/**
	 * Initialize settings fields.
	 *
	 * @return void
	 */
	function initialize_settings_fields(): void {
		SettingsFactory::register_setting_type( 'int', 'IntField' );
		SettingsFactory::register_setting_type( 'positive_int', 'PositiveIntField' );
		SettingsFactory::register_setting_type( 'bool', 'BoolField' );
	}
}