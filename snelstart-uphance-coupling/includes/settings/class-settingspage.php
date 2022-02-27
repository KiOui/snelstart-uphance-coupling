<?php
/**
 * Settings Page.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsmenu.php';

if ( ! class_exists( 'SettingsPage' ) ) {
	/**
	 * Page for Settings.
	 *
	 * @class SettingsPage
	 */
	class SettingsPage {

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
		 * Array of SettingsMenu's.
		 *
		 * @var array
		 */
		private array $menu_pages;

		/**
		 * Construct a SettingsPage.
		 *
		 * @param string $page_title the page title.
		 * @param string $menu_title the menu title.
		 * @param string $capability_needed WordPress' capability needed to access this SettingsPage.
		 * @param string $menu_slug slug of the SettingsPage.
		 * @param string $icon WordPress' icon of the SettingsPage.
		 * @param int    $position the position to render this SettingsPage in.
		 * @param array  $menu_pages an array of SettingsMenu's to register under this SettingsPage.
		 */
		public function __construct( string $page_title, string $menu_title, string $capability_needed, string $menu_slug, string $icon, int $position, array $menu_pages = array() ) {
			$this->page_title = $page_title;
			$this->menu_title = $menu_title;
			$this->capability_needed = $capability_needed;
			$this->menu_slug = $menu_slug;
			$this->icon = $icon;
			$this->position = $position;
			$this->menu_pages = $menu_pages;
		}

		/**
		 * Add a SettingsMenu to this SettingsPage.
		 *
		 * @param SettingsMenu $menu the SettingsMenu to add.
		 *
		 * @return void
		 */
		public function add_menu_page( SettingsMenu $menu ) {
			$this->menu_pages[] = $menu;
		}

		/**
		 * Get an array of SettingsMenu's registered under this SettingsPage.
		 *
		 * @return array array of SettingsMenu's.
		 */
		public function get_menu_pages(): array {
			return $this->menu_pages;
		}

		/**
		 * Register this SettingsPage and all SettingsMenu's registered in WordPress.
		 *
		 * @return void
		 */
		public function do_register() {
			add_menu_page(
				$this->page_title,
				$this->menu_title,
				$this->capability_needed,
				$this->menu_slug,
				null,
				$this->icon,
				$this->position,
			);
			foreach ( $this->menu_pages as $menu_page ) {
				$menu_page->do_register( $this->menu_slug );
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
