<?php
/**
 * Settings Manager.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsmenu.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingssection.php';

if ( ! class_exists( 'SettingsManager' ) ) {
	/**
	 * Manager for Settings.
	 *
	 * @class SettingsManager
	 */
	class SettingsManager {

		/**
		 * The group name of the setting to register in WordPress.
		 *
		 * @var string
		 */
		private string $group_name;

		/**
		 * The name of the setting to register in WordPress.
		 *
		 * @var string
		 */
		private string $setting_name;

		/**
		 * The SettingsPage to render for the registered setting.
		 *
		 * @var SettingsPage
		 */
		private SettingsPage $page;

		/**
		 * An array of SettingsSections registered for this Setting.
		 *
		 * @var array
		 */
		private array $settings_sections;

		/**
		 * An array of SettingsFields registered for this Setting.
		 *
		 * @var array
		 */
		private array $settings;

		/**
		 * Construct a SettingsManager.
		 *
		 * @param string       $group_name group name of the setting to register in WordPress.
		 * @param string       $setting_name setting name of the setting to register in WordPress.
		 * @param SettingsPage $page the page to render for this setting.
		 * @param array        $settings_sections an array of SettingsSections to render on the SettingsPage of this setting.
		 */
		public function __construct( string $group_name, string $setting_name, SettingsPage $page, array $settings_sections = array() ) {
			$this->group_name = $group_name;
			$this->setting_name = $setting_name;
			$this->page = $page;
			$this->settings_sections = $settings_sections;
			$this->settings = $this->get_all_settings_for_sections();
		}

		/**
		 * Create a SettingsMenu and add it to the SettingsPage of this setting.
		 *
		 * @param string   $page_title the page title for the SettingsMenu.
		 * @param string   $menu_title the menu title for the SettingsMenu.
		 * @param string   $capability_needed the capability needed to access the SettingsMenu.
		 * @param string   $menu_slug the slug of the SettingsMenu.
		 * @param callable $renderer the renderer for the SettingsMenu.
		 * @param int      $position the position where to render the SettingsMenu.
		 *
		 * @return void
		 */
		public function add_menu_page( string $page_title, string $menu_title, string $capability_needed, string $menu_slug, callable $renderer, int $position = 1 ) {
			$this->page->add_menu_page(
				new SettingsMenu(
					$page_title,
					$menu_title,
					$capability_needed,
					$menu_slug,
					$renderer,
					$position,
				)
			);
		}

		/**
		 * Get a SettingsSection by the section ID.
		 *
		 * @param string $section_id the section ID to get the SettingsSection for.
		 *
		 * @return SettingsSection|null null when the SettingsSection is not found, the SettingsSection with the
		 * $section_id otherwise.
		 */
		public function get_section_by_id( string $section_id ): ?SettingsSection {
			foreach ( $this->settings_sections as $section ) {
				if ( $section->get_id() === $section_id ) {
					return $section;
				}
			}
			return null;
		}

		/**
		 * Add a SettingsSection to this setting.
		 *
		 * @param SettingsSection $section the SettingsSection to add.
		 *
		 * @return void
		 */
		public function add_section( SettingsSection $section ) {
			$this->settings_sections[] = $section;
			$this->settings = array_merge( $this->settings, $section->get_settings() );
		}

		/**
		 * Add an array of SettingsFields to a SettingsSection registered for this setting.
		 *
		 * @param string $section_id the section ID of the SettingsSection to add the settings for.
		 * @param array  $settings an array of SettingsField to add to the SettingsSection.
		 *
		 * @return void
		 * @throws SettingsConfigurationException | ReflectionException When a SettingsField could not be created due
		 * to the from_array method not being found or when a SettingsField could not be found.
		 */
		public function add_settings( string $section_id, array $settings ) {
			$section = $this->get_section_by_id( $section_id );
			if ( isset( $section ) ) {
				foreach ( $settings as $setting_array ) {
					$setting = SettingsFactory::create_setting( $this, $setting_array );
					$this->add_setting( $section, $setting );
				}
			} else {
				throw new SettingsConfigurationException( "Section with ID $section_id not registered." );
			}
		}

		/**
		 * Add a SettingsField to a SettingsSection.
		 *
		 * @param string|SettingsSection $section_id either the section ID as a string, or a SettingsSection to add the
		 * setting for.
		 * @param SettingsField          $setting the SettingsField to add to the SettingsSection.
		 *
		 * @return void
		 * @throws SettingsConfigurationException When the section could not be found.
		 */
		public function add_setting( $section_id, SettingsField $setting ) {
			if ( gettype( $section_id ) === 'string' ) {
				$section = $this->get_section_by_id( $section_id );
			} else {
				$section = $section_id;
				$section_id = $section->get_id();
			}
			if ( isset( $section ) ) {
				$section->add_setting( $setting );
				$this->settings[] = $setting;
			} else {
				throw new SettingsConfigurationException( "Section with ID $section_id not registered." );
			}
		}

		/**
		 * Get all settings for the sections registered under this setting.
		 *
		 * @return array an array of SettingsFields for all registered sections under this setting.
		 */
		private function get_all_settings_for_sections(): array {
			$all_settings = array();
			foreach ( $this->settings_sections as $section ) {
				$settings_in_section = $section->get_settings();
				$all_settings = array_merge( $all_settings, $settings_in_section );
			}
			return $all_settings;
		}

		/**
		 * Get the name of this setting.
		 *
		 * @return string the name of this setting.
		 */
		public function get_setting_name(): string {
			return $this->setting_name;
		}

		/**
		 * Register this setting and all its sections in WordPress.
		 *
		 * @return void
		 */
		public function do_register(): void {
			register_setting(
				$this->group_name,
				$this->setting_name,
				array( $this, 'validate' )
			);
			foreach ( $this->settings_sections as $section ) {
				$section->do_register( $this->page->get_menu_slug() );
			}
		}

		/**
		 * Get a SettingsField registered under this setting with a specific ID.
		 *
		 * @param string $field_id the ID of the SettingsField to get.
		 *
		 * @return SettingsField|null null when the SettingsField is not found, the SettingsField with the
		 * $field_id otherwise.
		 */
		public function get_setting_with_id( string $field_id ): ?SettingsField {
			foreach ( $this->settings as $setting ) {
				if ( $setting->get_id() === $field_id ) {
					return $setting;
				}
			}
			return null;
		}

		/**
		 * Render a SettingsField.
		 *
		 * @param string $field_id the field ID of the SettingsField to render.
		 *
		 * @return void
		 * @throws SettingsConfigurationException When the SettingsField with the $field_id was not found.
		 */
		public function render_settings_field( string $field_id ): void {
			$options = get_option( $this->setting_name );
			$setting = $this->get_setting_with_id( $field_id );
			if ( ! isset( $setting ) ) {
				throw new SettingsConfigurationException( "Setting with id $field_id not registered." );
			}
			$setting->render( $this->setting_name, $options );
		}

		/**
		 * Add actions and filters to WordPress.
		 *
		 * @return void
		 */
		public function actions_and_filters(): void {
			add_action( 'admin_menu', array( $this->page, 'do_register' ) );
			add_action( 'admin_init', array( $this, 'do_register' ) );
		}

		/**
		 * Validate an array of setting values with their SettingsField validation methods.
		 *
		 * @param array $input an array of setting values to be validated.
		 *
		 * @return array validated setting values as an array.
		 */
		public function validate( array $input ): array {
			$output = array();

			foreach ( $input as $input_id => $input_value ) {
				$setting = $this->get_setting_with_id( $input_id );
				if ( isset( $setting ) ) {
					$output[ $input_id ] = $setting->validate( $input_value );
				}
			}
			return $output;
		}
	}
}
