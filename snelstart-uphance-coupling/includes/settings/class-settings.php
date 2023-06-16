<?php
/**
 * Settings class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Settings' ) ) {
	/**
	 * Settings class.
	 *
	 * @class Settings
	 */
	class Settings {

		/**
		 * The database key for this setting.
		 *
		 * @var string
		 */

		private string $database_key;

		/**
		 * The WordPress group name for this setting.
		 *
		 * @var string
		 */
		private string $group_name;

		/**
		 * The settings fields belonging to this setting.
		 *
		 * @var SettingsField[]
		 */
		private array $settings;

		/**
		 * Construct a Settings class.
		 *
		 * @param string $group_name The group name.
		 * @param string $database_key The database key.
		 * @param array  $settings The settings to register under this setting.
		 */
		public function __construct( string $group_name, string $database_key, array $settings ) {
			$this->group_name   = $group_name;
			$this->database_key = $database_key;
			$this->settings     = $settings;
		}

		/**
		 * Get the value of a setting by name.
		 *
		 * @throws SettingsConfigurationException When the setting name is not found.
		 */
		public function get_value( string $setting_name ) {
			$setting = $this->get_field( $setting_name );
			if ( is_null( $setting ) ) {
				throw new SettingsConfigurationException( 'This setting does not exist.' );
			}

			return $setting->get_value();
		}

		/**
		 * Register the setting in WordPress.
		 *
		 * @return void
		 */
		public function register() {
			register_setting(
				$this->group_name,
				$this->database_key,
				array(
					'type'         => 'object',
					'show_in_rest' => false,
					'default'      => null,
				)
			);
		}

		/**
		 * Set the value of a setting.
		 *
		 * @throws SettingsConfigurationException When the name of the setting was not found.
		 */
		public function set_value( string $setting_name, $value ): bool {
			$setting = $this->get_field( $setting_name );
			if ( is_null( $setting ) ) {
				throw new SettingsConfigurationException( 'This setting does not exist.' );
			}

			return $setting->set_value( $value );
		}

		/**
		 * Get a settings field by name.
		 *
		 * @param string $setting_name The name of the settings field.
		 *
		 * @return SettingsField|null A SettingsField when the setting exists, null otherwise.
		 */
		public function get_field( string $setting_name ): ?SettingsField {
			if ( isset( $this->settings[ $setting_name ] ) ) {
				return $this->settings[ $setting_name ];
			}

			return null;
		}

		/**
		 * Update all settings with an array of key => value pairs.
		 *
		 * @param array $setting_values The key => value pairs to set the settings to.
		 *
		 * @return void
		 */
		public function update_settings( array $setting_values ) {
			foreach ( $this->settings as $id => $setting_field ) {
				if ( isset( $setting_values[ $id ] ) ) {
					$setting_field->set_value( $setting_values[ $id ] );
				}
			}
		}

		/**
		 * Serialize all settings to an array.
		 *
		 * @return array Serialized settings.
		 */
		public function serialize_settings(): array {
			$settings_to_save = array();
			foreach ( $this->settings as $id => $setting ) {
				$settings_to_save[ $id ] = $setting->serialize();
			}

			return $settings_to_save;
		}

		/**
		 * Load settings from a serialized array.
		 *
		 * @param array $serialized_setting_values The serialized array to load settings from.
		 *
		 * @return void
		 */
		public function load_serialized_settings( array $serialized_setting_values ) {
			foreach ( $this->settings as $id => $setting ) {
				if ( array_key_exists( $id, $serialized_setting_values ) ) {
					$deserialized_setting_value = $setting->deserialize( $serialized_setting_values[ $id ] );
					if ( ! $setting->set_initial_value( $deserialized_setting_value ) ) {
						$setting->set_default();
					}
				} else {
					$setting->set_default();
				}
			}
		}

		/**
		 * Initialize settings by either setting defaults or loading the serialized settings.
		 *
		 * @return void
		 */
		public function initialize_settings() {
			$option_value = get_option( $this->database_key );
			if ( false === $option_value || ! is_array( $option_value ) ) {
				foreach ( $this->settings as $setting ) {
					$setting->set_default();
				}
			} else {
				$this->load_serialized_settings( $option_value );
			}
		}

		/**
		 * Push current settings state to the database.
		 *
		 * @return void
		 */
		public function save_settings() {
			update_option( $this->database_key, $this->serialize_settings() );
		}
	}
}
