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
		 * @return SettingsManager a SettingsManager created for the settings configuration.
		 * @throws ReflectionException When a setting could not be created due to it not implementing the from_array
		 * method.
		 * @throws SettingsConfigurationException When a setting type used in the settings configuration is not
		 * registered.
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
					$settings[] = self::create_setting( $manager, $setting_array );
				}
				$section_array['settings'] = $settings;
				$section = SettingsSection::from_array( $section_array );
				$manager->add_section( $section );
			}
			return $manager;
		}

		/**
		 * Create a SettingsField under a SettingsManager.
		 *
		 * @param SettingsManager $manager the SettingsManager to create the setting for.
		 * @param array           $setting_array an array representation of the SettingsField to create.
		 *
		 * @return SettingsField the created SettingsField.
		 * @throws SettingsConfigurationException | ReflectionException When the settings type stated in the setting
		 * array is not registered or the from_array method is not created for a SettingsField class registered.
		 */
		public static function create_setting( SettingsManager $manager, array $setting_array ): SettingsField {
			$type = $setting_array['type'];
			if ( isset( self::$setting_types[ $type ] ) ) {
				$from_array = new ReflectionMethod( self::$setting_types[ $type ], 'from_array' );
				$setting_array['renderer'] = function() use ( $manager, $setting_array ) {
					$manager->render_settings_field( $setting_array['id'] );
				};
				return $from_array->invoke( null, $setting_array );
			} else {
				throw new SettingsConfigurationException( "Setting Type $type not registered." );
			}
		}

	}
}
