<?php
/**
 * Settings Section.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SettingsSection' ) ) {
	/**
	 * Section for Settings.
	 *
	 * @class SettingsSection
	 */
	class SettingsSection {

		/**
		 * The ID of the setting section (as a slug).
		 *
		 * @var string
		 */
		private string $id;

		/**
		 * The name of the setting section.
		 *
		 * @var string
		 */
		private string $name;

		/**
		 * The custom renderer of the setting section. Defaults to the default renderer of this class.
		 *
		 * @var callable|null
		 */
		private $renderer;

		/**
		 * The settings registered under this SettingsSection.
		 *
		 * @var string[]
		 */
		private array $settings;

		/**
		 * Construct a SettingsSection.
		 *
		 * @param string        $id the ID of the setting section to create (as a slug).
		 * @param string        $name the name of the settings section to create.
		 * @param callable|null $renderer the custom renderer of the section. Defaults to the default renderer.
		 * @param string[]      $settings te settings to add to this section (as an array of strings).
		 */
		public function __construct( string $id, string $name, ?callable $renderer = null, array $settings = array() ) {
			$this->id = $id;
			$this->name = $name;
			$this->renderer = $renderer;
			$this->settings = $settings;
		}

		/**
		 * Get the renderer for this SettingsSection.
		 *
		 * @return callable the renderer of the SettingSection.
		 */
		public function get_renderer(): callable {
			return $this->renderer ?? array( $this, 'render' );
		}

		/**
		 * Register this SettingsSection and all its registered SettingsFields in WordPress.
		 *
		 * @param string $page the slug of the SettingsPage to register this SettingsSection under.
		 *
		 * @return void
		 * @throws SettingsConfigurationException When a setting could not be found.
		 */
		public function register( string $page, Settings $settings ) {
			$this->register_self( $page );
			$this->register_settings( $page, $settings );
		}

		/**
		 * Register this settings section in WordPress.
		 *
		 * @param string $page The page to register the section on.
		 *
		 * @return void
		 */
		public function register_self( string $page ) {
			add_settings_section(
				$this->id,
				$this->name,
				$this->get_renderer(),
				$page,
			);
		}

		/**
		 * Register the settings in this settings section.
		 *
		 * @param string   $page The page to register the settings on.
		 * @param Settings $settings The settings.
		 *
		 * @throws SettingsConfigurationException When a setting with the defined key does not exist in $settings.
		 */
		public function register_settings( string $page, Settings $settings ) {
			foreach ( $this->settings as $setting_key ) {
				$setting_obj = $settings->get_field( $setting_key );
				if ( is_null( $setting_obj ) ) {
					throw new SettingsConfigurationException( esc_html( "Setting with key $setting_key does not exist." ) );
				}
				$conditions_hold = true;
				foreach ( $setting_obj->get_conditions() as $condition ) {
					if ( ! $condition->holds( $settings ) ) {
						$conditions_hold = false;
					}
				}
				if ( $conditions_hold ) {
					add_settings_field(
						$setting_obj->get_id(),
						$setting_obj->get_name(),
						$setting_obj->get_renderer(),
						$page,
						$this->id,
					);
				}
			}
		}

		/**
		 * Get all registered SettingsFields under this SettingsSection.
		 *
		 * @return string[]
		 */
		public function get_settings(): array {
			return $this->settings;
		}

		/**
		 * Add a SettingsField to the registered SettingsFields under this SettingsSection.
		 *
		 * @param SettingsField $setting the SettingsField to add to this SettingsSection.
		 *
		 * @return void
		 */
		public function add_setting( SettingsField $setting ) {
			$this->settings[] = $setting;
		}

		/**
		 * Get the slug identifier of this SettingsSection.
		 *
		 * @return string the slug identifier of this SettingsSection.
		 */
		public function get_id(): string {
			return $this->id;
		}

		/**
		 * Default renderer of this SettingsSection.
		 *
		 * @return void
		 */
		public function render(): void {
			echo esc_html( $this->name );
		}

		/**
		 * Construct a SettingsSection from an array. The array should have the following keys: 'id', 'name', 'renderer'
		 * and 'settings'. The value of these keys should correspond to the constructor arguments of SettingsSection.
		 *
		 * @param array $section_array an array passed to the constructor of SettingsSection.
		 *
		 * @return static SettingsSection
		 */
		public static function from_array( array $section_array ): self {
			return new SettingsSection(
				$section_array['id'],
				$section_array['name'],
				isset( $section_array['renderer'] ) ? $section_array['renderer'] : null,
				$section_array['settings'],
			);
		}
	}
}
