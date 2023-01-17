<?php


class Settings {

	private string $database_key;
	private string $group_name;

	/**
	 * @var SettingsField[]
	 */
	private array $settings;

	public function __construct( string $group_name, string $database_key, array $settings ) {
		$this->group_name = $group_name;
		$this->database_key = $database_key;
		$this->settings = $settings;
	}

	/**
	 * @throws SettingsConfigurationException
	 */
	public function get_value( string $setting_name ) {
		$setting = $this->get_field( $setting_name );
		if ( is_null( $setting ) ) {
			throw new SettingsConfigurationException( "This setting does not exist." );
		}
		return $setting->get_value();
	}

	public function register() {
		register_setting(
			$this->group_name,
			$this->database_key,
			[
				'type' => 'object',
				'show_in_rest' => false,
				'default' => null
			]
		);
	}

	/**
	 * @throws SettingsConfigurationException
	 */
	public function set_value( string $setting_name, $value ): bool {
		$setting = $this->get_field( $setting_name );
		if ( is_null( $setting ) ) {
			throw new SettingsConfigurationException( "This setting does not exist." );
		}
		return $setting->set_value( $value );
	}

	public function get_field( string $setting_name ): ?SettingsField {
		if ( isset( $this->settings[ $setting_name ] ) ) {
			return $this->settings[ $setting_name ];
		}
		return null;
	}

	function update_settings( array $setting_values ) {
		foreach ( $this->settings as $id => $setting_field ) {
			if ( isset( $setting_values[ $id ] ) ) {
				$setting_field->set_value( $setting_values[ $id ] );
			}
		}
	}

	public function serialize_settings(): array {
		$settings_to_save = array();
		foreach ( $this->settings as $id => $setting ) {
			$settings_to_save[ $id ] = $setting->serialize();
		}
		return $settings_to_save;
	}

	public function load_serialized_settings( array $serialized_setting_values ) {
		foreach ( $this->settings as $id => $setting ) {
			if ( isset( $serialized_setting_values[ $id ] ) ) {
				$deserialized_setting_value = $setting->deserialize( $serialized_setting_values[ $id ] );
				$setting->set_value_force( $deserialized_setting_value );
			} else {
				$setting->set_default();
			}
		}
	}

	public function initialize_settings() {
		$option_value = get_option( $this->database_key );
		if ( $option_value === false || ! is_array( $option_value ) ) {
			foreach( $this->settings as $setting ) {
				$setting->set_default();
			}
		} else {
			$this->load_serialized_settings( $option_value );
		}
	}

	function save_settings() {
		update_option( $this->database_key, $this->serialize_settings() );
	}
}