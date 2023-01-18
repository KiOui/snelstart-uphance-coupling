<?php
/**
 * Settings Group.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settings.php';

if ( ! class_exists( 'SettingsGroup' ) ) {
	/**
	 * Group for Settings.
	 *
	 * @class SettingsGroup
	 */
	class SettingsGroup {

		/**
		 * Title of SettingsPage.
		 *
		 * @var string
		 */
		private string $page_title;

		/**
		 * Menu title of SettingsPage.
		 *
		 * @var string
		 */
		private string $menu_title;

		/**
		 * Capability needed to access this SettingsPage.
		 *
		 * @var string
		 */
		private string $capability_needed;

		/**
		 * Slug of the SettingsPage.
		 *
		 * @var string
		 */
		private string $menu_slug;

		/**
		 * WordPress' icon for the SettingsPage.
		 *
		 * @var string
		 */
		private string $icon;

		/**
		 * Position of the SettingsPage.
		 *
		 * @var int
		 */
		private int $position;

		/**
		 * The settings pages in this group.
		 *
		 * @var SettingsPage[]
		 */
		private array $settings_pages;

		/**
		 * Construct a SettingsGroup.
		 *
		 * @param string         $page_title the page title.
		 * @param string         $menu_title the menu title.
		 * @param string         $capability_needed WordPress' capability needed to access this SettingsPage.
		 * @param string         $menu_slug slug of the SettingsPage.
		 * @param string         $icon WordPress' icon of the SettingsPage.
		 * @param int            $position the position to render this SettingsPage in.
		 * @param SettingsPage[] $settings_pages an array of SettingsMenu's to register under this SettingsPage.
		 */
		public function __construct( string $page_title, string $menu_title, string $capability_needed, string $menu_slug, string $icon, int $position, array $settings_pages = array() ) {
			$this->page_title = $page_title;
			$this->menu_title = $menu_title;
			$this->capability_needed = $capability_needed;
			$this->menu_slug = $menu_slug;
			$this->icon = $icon;
			$this->position = $position;
			$this->settings_pages = $settings_pages;
		}

		/**
		 * Register this SettingsPage and all SettingsMenu's registered in WordPress.
		 *
		 * @return void
		 */
		public function register( Settings $settings ) {
			$this->register_self();
			$this->register_settings_pages( $settings );
		}

		/**
		 * Register this group in WordPress.
		 *
		 * @return void
		 */
		public function register_self() {
			add_menu_page(
				$this->page_title,
				$this->menu_title,
				$this->capability_needed,
				$this->menu_slug,
				null,
				$this->icon,
				$this->position,
			);
		}

		/**
		 * Register all settings pages in WordPress.
		 *
		 * @param Settings $settings The settings to register.
		 *
		 * @return void
		 * @throws SettingsConfigurationException When a setting was not found in $settings.
		 */
		private function register_settings_pages( Settings $settings ) {
			foreach ( $this->settings_pages as $settings_page ) {
				$settings_page->register( $this->menu_slug, $settings );
			}
		}

		/**
		 * Get the slug of the SettingsPage.
		 *
		 * @return string slug of the SettingsPage.
		 */
		public function get_menu_slug(): string {
			return $this->menu_slug;
		}
	}
}
