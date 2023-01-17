<?php
/**
 * Settings initialize.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsfactory.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-intfield.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-positiveintfield.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-boolfield.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-textfield.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-choicefield.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-passwordfield.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-datetimefield.php';
include_once SUC_ABSPATH . 'includes/settings/fields/class-callablechoicefield.php';

if ( ! function_exists( 'initialize_settings_fields' ) ) {
	/**
	 * Initialize settings fields.
	 *
	 * @return void
	 */
	function initialize_settings_fields(): void {
		SettingsFactory::register_setting_type( 'int', 'IntField' );
		SettingsFactory::register_setting_type( 'positive_int', 'PositiveIntField' );
		SettingsFactory::register_setting_type( 'bool', 'BoolField' );
		SettingsFactory::register_setting_type( 'text', 'TextField' );
		SettingsFactory::register_setting_type( 'choice', 'ChoiceField' );
		SettingsFactory::register_setting_type( 'password', 'PasswordField' );
		SettingsFactory::register_setting_type( 'datetime', 'DateTimeField' );
		SettingsFactory::register_setting_type( 'callable_choice', 'CallableChoiceField' );
	}
}
