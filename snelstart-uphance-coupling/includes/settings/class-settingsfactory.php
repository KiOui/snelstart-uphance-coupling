<?php
/**
 * Settings Factory.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsmanager.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingspage.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsmenu.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( !class_exists( "SettingsFactory" ) ) {
	/**
	 * Settings Factory.
	 *
	 * @class SettingsFactory
	 */
	class SettingsFactory {

		public static array $setting_types = array();

		public static function register_setting_type( string $type, $class ) {
			SettingsFactory::$setting_types[$type] = $class;
		}

		/**
		 * @throws SettingsConfigurationException On unregistered settings type or on initialization exception.
		 * @throws ReflectionException On non recognized class.
		 */
		public static function create_settings( array $settings_configuration ): SettingsManager {
			$manager = new SettingsManager(
				$settings_configuration['group_name'],
				$settings_configuration['setting_name'],
				new SettingsPage(
					$settings_configuration['page']['page_title'],
					$settings_configuration['page']['menu_title'],
					$settings_configuration['page']['capability_needed'],
					$settings_configuration['page']['menu_slug'],
					$settings_configuration['page']['icon'],
					$settings_configuration['page']['position']
				)
			);
			foreach ( $settings_configuration['menu_pages'] as $menu_page_array ) {
				if ( isset( $menu_page_array['position'] ) ) {
					$manager->add_menu_page(
						$menu_page_array['page_title'],
						$menu_page_array['menu_title'],
						$menu_page_array['capability_needed'],
						$menu_page_array['menu_slug'],
						$menu_page_array['renderer'],
						$menu_page_array['position'],
					);
				} else {
					$manager->add_menu_page(
						$menu_page_array['page_title'],
						$menu_page_array['menu_title'],
						$menu_page_array['capability_needed'],
						$menu_page_array['menu_slug'],
						$menu_page_array['renderer'],
					);
				}
			}
			foreach ( $settings_configuration['sections'] as $section_array ) {
				$settings = array();
				foreach ( $section_array['settings'] as $setting_array ) {
					$type = $setting_array['type'];
					if ( isset( SettingsFactory::$setting_types[$type]) ) {
						$from_array = new ReflectionMethod( SettingsFactory::$setting_types[$type], 'from_array' );
						$setting_array['renderer'] = function() use ( $manager, $setting_array ) {
							$manager->render_settings_field( $setting_array['id'] );
						};
						$settings[] = $from_array->invoke( null, $setting_array );
					} else {
						throw new SettingsConfigurationException( "Setting Type $type not registered." );
					}
 				}
				$section_array['settings'] = $settings;
				$section = SettingsSection::from_array( $section_array );
				$manager->add_section( $section );
			}
			return $manager;
		}

	}
}