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

if ( !class_exists( "SettingsPage" ) ) {
	/**
	 * Page for Settings.
	 *
	 * @class SettingsPage
	 */
	class SettingsPage {

		private string $page_title;
		private string $menu_title;
		private string $capability_needed;
		private string $menu_slug;
		private string $icon;
		private int $position;
		private array $menu_pages;

		function __construct( string $page_title, string $menu_title, string $capability_needed, string $menu_slug, string $icon, int $position, array $menu_pages = array() ) {
			$this->page_title = $page_title;
			$this->menu_title = $menu_title;
			$this->capability_needed = $capability_needed;
			$this->menu_slug = $menu_slug;
			$this->icon = $icon;
			$this->position = $position;
			$this->menu_pages = $menu_pages;
		}

		public function add_menu_page( SettingsMenu $menu ) {
			$this->menu_pages[] = $menu;
		}

		public function get_menu_pages(): array {
			return $this->menu_pages;
		}

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
			foreach ($this->menu_pages as $menu_page) {
				$menu_page->do_register( $this->menu_slug );
			}
		}

		public function get_menu_slug(): string {
			return $this->menu_slug;
		}
	}
}