<?php
/**
 * Snelstart BTW
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/snelstart/class-sucsnelstartclient.php';

if ( ! class_exists( 'SUCSnelstartSynchronizer' ) ) {
	/**
	 * Snelstart BTW
	 *
	 * @class SUCBTW
	 */
	class SUCBTW {

		/**
		 * Constant for BTW Hoog amount.
		 *
		 * @var float
		 */
		public static float $btw_hoog = 21.0;

		/**
		 * Constanct for BTW None amount.
		 *
		 * @var float
		 */
		public static float $btw_none = 0.0;

		/**
		 * Constant for BTW Hoog post.
		 *
		 * @var string
		 */
		public static string $btw_name_hoog = 'VerkopenHoog';

		/**
		 * Constant for BTW None post.
		 *
		 * @var string
		 */
		public static string $btw_name_none = 'VerkopenVerlegd';

		/**
		 * Grootboekcode for BTW Hoog post.
		 *
		 * @var string
		 */
		private string $grootboekcode_btw_hoog;

		/**
		 * Grootboekcode for BTW None post.
		 *
		 * @var string
		 */
		private string $grootboekcode_btw_geen;

		/**
		 * Tax types.
		 *
		 * @var array
		 */
		private array $tax_types;

		/**
		 * Constructor.
		 *
		 * @param array $tax_types Tax type array.
		 */
		public function __construct() {
			$manager = SUCSettings::instance()->get_manager();
			$this->grootboekcode_btw_hoog = $manager->get_value_by_setting_id('snelstart_grootboekcode_btw_hoog');
			$this->grootboekcode_btw_geen = $manager->get_value_by_setting_id('snelstart_grootboekcode_btw_geen');
			$this->tax_types = $tax_types;
		}

		/**
		 * Convert a BTW amount to BTW name.
		 *
		 * @param float $btw_amount the BTW amount to convert.
		 *
		 * @return string|null the BTW name or null if it does not exist.
		 */
		public static function convert_btw_amount_to_name( float $btw_amount ): ?string {
			if ( self::$btw_none === $btw_amount ) {
				return self::$btw_name_none;
			} else if ( self::$btw_hoog === $btw_amount ) {
				return self::$btw_name_hoog;
			} else {
				return null;
			}
		}

		/**
		 * Get a grootboekcode for a tax amount.
		 *
		 * @param float $btw_amount the BTW amount to get the grootboekcode for.
		 *
		 * @return string|null the grootboekcode ID or null if it does not exist.
		 */
		private function get_grootboekcode_for_tax_amount( float $btw_amount, string $grootboekcode_btw_geen, string $grootboekcode_btw_hoog ): ?string {
			if ( self::$btw_none === $btw_amount ) {
				return $grootboekcode_btw_geen;
			} else if ( self::$btw_hoog === $btw_amount ) {
				return $grootboekcode_btw_hoog;
			} else {
				return null;
			}
		}

		/**
		 * Convert BTW amount to type.
		 *
		 * @param float $tax_level the tax level to convert.
		 *
		 * @return array|null the tax type or null if it does not exist.
		 */
		public function convert_btw_amount_to_type( float $tax_level ): ?array {
			foreach ( $this->tax_types as $tax_type ) {
				if ( $tax_type['btwPercentage'] === $tax_level ) {
					return $tax_type;
				}
			}
			return null;
		}
}
