<?php
/**
 * Settings Section.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( "SettingsSection" ) ) {
	/**
	 * Section for Settings.
	 *
	 * @class SettingsSection
	 */
	class SettingsSection {

		private string $id;
		private string $name;
		private $renderer;
		private array $settings;

		public function __construct( string $id, string $name, ?callable $renderer, array $settings = array() ) {
			$this->id = $id;
			$this->name = $name;
			$this->renderer = $renderer;
			$this->settings = $settings;
		}

		public function get_renderer(): callable {
			return $this->renderer ?? array( $this, 'render' );
		}

		public function do_register( string $page ) {
			add_settings_section(
				$this->id,
				$this->name,
				$this->get_renderer(),
				$page,
			);
			foreach ( $this->settings as $setting ) {
				$setting->do_register( $page, $this->id );
			}
		}

		public function get_settings(): array {
			return $this->settings;
		}

		public function get_id(): string {
			return $this->id;
		}

		public function render(): void {
			echo esc_html( $this->name );
		}

		public static function from_array( array $section_array ): self {
			return new SettingsSection(
				$section_array['id'],
				$section_array['name'],
				$section_array['renderer'],
				$section_array['settings'],
			);
		}
	}
}