<?php
/**
 * Settings Menu.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( "SettingsMenu" ) ) {
	/**
	 * Menu for Settings.
	 *
	 * @class SettingsMenu
	 */
	class SettingsMenu {

		private string $page_title;
		private string $menu_title;
		private string $capability_needed;
		private string $menu_slug;
		private $renderer;
		private int $position;

		function __construct( string $page_title, string $menu_title, string $capability_needed, string $menu_slug, callable $renderer, int $position = 1 ) {
			$this->page_title = $page_title;
			$this->menu_title = $menu_title;
			$this->capability_needed = $capability_needed;
			$this->menu_slug = $menu_slug;
			$this->renderer = $renderer;
			$this->position = $position;
		}

		public function do_register( string $menu_slug ) {
			add_submenu_page(
				$menu_slug,
				$this->page_title,
				$this->menu_title,
				$this->capability_needed,
				$this->menu_slug,
				$this->renderer,
				$this->position,
			);
		}
	}
}