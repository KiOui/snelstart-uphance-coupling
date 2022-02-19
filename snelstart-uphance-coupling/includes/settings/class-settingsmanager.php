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

if ( !class_exists( "SettingsManager" ) ) {
	/**
	 * Manager for Settings.
	 *
	 * @class SettingsManager
	 */
	class SettingsManager {

		private string $group_name;
		private string $setting_name;
		private SettingsPage $page;
		private array $settings_sections;
		private array $settings;

		public function __construct( string $group_name, string $setting_name, SettingsPage $page, array $settings_sections = array() ) {
			$this->group_name = $group_name;
			$this->setting_name = $setting_name;
			$this->page = $page;
			$this->settings_sections = $settings_sections;
			$this->settings = $this->get_all_settings_for_sections();
		}

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

		public function add_section( SettingsSection $section ) {
			$this->settings_sections[] = $section;
			$this->settings = array_merge( $this->settings, $section->get_settings() );
		}

		private function get_all_settings_for_sections(): array {
			$all_settings = array();
			foreach ( $this->settings_sections as $section ) {
				$settings_in_section = $section->get_settings();
				$all_settings = array_merge($all_settings, $settings_in_section);
			}
			return $all_settings;
		}

		public function get_setting_name(): string {
			return $this->setting_name;
		}

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

		public function get_setting_with_id( string $field_id ): ?SettingsField {
			foreach( $this->settings as $setting ) {
				if ( $setting->get_id() === $field_id ) {
					return $setting;
				}
			}
			return null;
		}

		public function render_settings_field( string $field_id ): void {
			$options = get_option( $this->setting_name );
			$setting = $this->get_setting_with_id( $field_id );
			if ( !isset( $setting ) ) {
				throw new SettingsConfigurationException("Setting with id $field_id not registered.");
			}
			$setting->render( $this->setting_name, $options );
		}

		public function actions_and_filters(): void {
			add_action( 'admin_menu', array( $this->page, 'do_register' ) );
			add_action( 'admin_init', array( $this, 'do_register' ) );
		}

		public function validate( array $input ): array {
			$output = array();

			foreach ( $input as $input_id => $input_value ) {
				$setting = $this->get_setting_with_id( $input_id );
				if ( isset( $setting ) ) {
					$output[$input_id] = $setting->validate( $input_value );
				}
			}
			return $output;
		}

		public function get_value( $setting_id ): mixed {
			// TODO
			return null;
		}
	}
}