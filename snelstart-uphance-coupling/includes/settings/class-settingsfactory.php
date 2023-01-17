<?php
/**
 * Settings Factory.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settings.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingspage.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsgroup.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingssection.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( 'SettingsFactory' ) ) {
	/**
	 * Settings Factory.
	 *
	 * @class SettingsFactory
	 */
	class SettingsFactory {

		/**
		 * Registered setting types.
		 *
		 * @var array
		 */
		public static array $setting_types = array();

		/**
		 * Register a class extending SettingsField to the $setting_types.
		 *
		 * @param string $type a name of the type to register.
		 * @param mixed  $class the class to use for the registered name.
		 *
		 * @return void
		 */
		public static function register_setting_type( string $type, $class ) {
			self::$setting_types[ $type ] = $class;
		}

		/**
		 * Create settings from a setting array.
		 *
		 * @param array $settings_configuration a settings configuration in array format.
		 *
		 * @return Settings a Settings object.
		 * @throws ReflectionException When a setting could not be created due to it not implementing the from_array
		 * method.
		 * @throws SettingsConfigurationException When a setting type used in the settings configuration is not
		 * registered.
		 */
		public static function create_settings( array $settings_configuration ): Settings {
			$group_name = $settings_configuration['group_name'];
			$settings_name = $settings_configuration['name'];
			$settings_values = $settings_configuration['settings'];

			$settings = array();
			foreach( $settings_values as $settings_value ) {
				$settings[ $settings_value['id'] ] = SettingsFactory::create_setting( $settings_value );
			}

			return new Settings( $settings_name, $group_name, $settings );
		}

		/**
		 * @throws ReflectionException
		 * @throws SettingsConfigurationException
		 */
		public static function create_setting( array $setting_configuration ): SettingsField {
			$type = $setting_configuration['type'];
			if ( isset( self::$setting_types[ $type ] ) ) {
				$from_array = new ReflectionMethod( self::$setting_types[ $type ], 'from_array' );
				return $from_array->invoke( null, $setting_configuration );
			} else {
				throw new SettingsConfigurationException( "Setting type $type is not registered." );
			}
		}

		public static function create_settings_group( array $settings_group ): SettingsGroup {
			$page_title = $settings_group['page_title'];
			$menu_title = $settings_group['menu_title'];
			$capability_needed = $settings_group['capability_needed'];
			$menu_slug = $settings_group['menu_slug'];
			$icon = $settings_group['icon'];
			$position = $settings_group['position'];
			$settings_pages = $settings_group['settings_pages'];

			$settings_pages_obj = array();
			foreach( $settings_pages as $settings_page ) {
				$settings_pages_obj[] = SettingsFactory::create_settings_page( $settings_page );
			}
			return new SettingsGroup( $page_title, $menu_title, $capability_needed, $menu_slug, $icon, $position, $settings_pages_obj );
		}

		public static function create_settings_page( array $settings_page ): SettingsPage {
			$page_title = $settings_page['page_title'];
			$menu_title = $settings_page['menu_title'];
			$capability_needed = $settings_page['capability_needed'];
			$menu_slug = $settings_page['menu_slug'];
			$renderer = $settings_page['renderer'];
			$position = isset( $settings_page['position'] ) ? $settings_page['position'] : 1;
			$settings_sections = $settings_page['settings_sections'];

			$settings_sections_obj = array();
			foreach ( $settings_sections as $settings_section ) {
				$settings_sections_obj[] = SettingsFactory::create_settings_section( $settings_section );
			}

			return new SettingsPage( $page_title, $menu_title, $capability_needed, $menu_slug, $renderer, $settings_sections_obj, $position );
		}

		public static function create_settings_section( array $settings_section ): SettingsSection {
			$id = $settings_section[ 'id' ];
			$name = $settings_section[ 'name' ];
			$renderer = isset( $settings_section[ 'renderer'] ) ? $settings_section[ 'renderer' ] : null;
			$settings_str = $settings_section['settings'];

			return new SettingsSection( $id, $name, $renderer, $settings_str );
		}
	}
}
