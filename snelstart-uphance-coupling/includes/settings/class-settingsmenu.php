<?php
/**
 * Settings Menu.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SettingsMenu' ) ) {
	/**
	 * Menu for Settings.
	 *
	 * @class SettingsMenu
	 */
	class SettingsMenu {

		/**
		 * Page title of the SettingsMenu.
		 *
		 * @var string
		 */
		private string $page_title;

		/**
		 * Menu title of the SettingsMenu.
		 *
		 * @var string
		 */
		private string $menu_title;

		/**
		 * Needed WordPress capability to access the menu.
		 *
		 * @var string
		 */
		private string $capability_needed;

		/**
		 * Slug of the menu.
		 *
		 * @var string
		 */
		private string $menu_slug;

		/**
		 * Renderer of the menu page.
		 *
		 * @var callable
		 */
		private $renderer;

		/**
		 * Position of the menu page to render.
		 *
		 * @var int
		 */
		private int $position;

		/**
		 * Construct a SettingsMenu.
		 *
		 * @param string   $page_title page title of the SettingsMenu.
		 * @param string   $menu_title menu title of the SettingsMenu.
		 * @param string   $capability_needed WordPress' capability needed to access this menu.
		 * @param string   $menu_slug the slug of the menu.
		 * @param callable $renderer the renderer of the menu.
		 * @param int      $position the position of the menu page.
		 */
		public function __construct( string $page_title, string $menu_title, string $capability_needed, string $menu_slug, callable $renderer, int $position = 1 ) {
			$this->page_title = $page_title;
			$this->menu_title = $menu_title;
			$this->capability_needed = $capability_needed;
			$this->menu_slug = $menu_slug;
			$this->renderer = $renderer;
			$this->position = $position;
		}

		/**
		 * Register the menu in WordPress.
		 *
		 * @param string $parent_slug the slug of the parent to register this menu on.
		 *
		 * @return void
		 */
		public function do_register( string $parent_slug ) {
			add_submenu_page(
				$parent_slug,
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
