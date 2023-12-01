<?php
/**
 * Snelstart BTW
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCBTW' ) ) {
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
		 * Constant for BTW None amount.
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
		 * Grootboekcode for BTW Hoog post (shipping).
		 *
		 * @var string
		 */
		private string $grootboekcode_btw_hoog_shipping;

		/**
		 * Grootboekcode for BTW None post (shipping).
		 *
		 * @var string
		 */
		private string $grootboekcode_btw_geen_shipping;

		/**
		 * Tax types.
		 *
		 * @var array
		 */
		private array $tax_types;

		/**
		 * Constructor.
		 *
		 * @param string $grootboekcode_btw_hoog the grootboekcode for BTW hoog items.
		 * @param string $grootboekcode_btw_geen the grootboekcode for BTW laag items.
		 * @param string $grootboekcode_btw_hoog_shipping the grootboekcode for BTW hoog items (shipping only).
		 * @param string $grootboekcode_btw_geen_shipping the grootboekcode for BTW laag items (shipping only).
		 * @param array  $tax_types the tax types registered in Snelstart.
		 */
		public function __construct( string $grootboekcode_btw_hoog, string $grootboekcode_btw_geen, string $grootboekcode_btw_hoog_shipping, string $grootboekcode_btw_geen_shipping, array $tax_types ) {
			$this->grootboekcode_btw_hoog = $grootboekcode_btw_hoog;
			$this->grootboekcode_btw_geen = $grootboekcode_btw_geen;
			$this->grootboekcode_btw_hoog_shipping = $grootboekcode_btw_hoog_shipping;
			$this->grootboekcode_btw_geen_shipping = $grootboekcode_btw_geen_shipping;
			$this->tax_types              = $tax_types;
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
		 * @param bool  $is_shipping whether to retrieve the grootboekcode for a shipping item.
		 *
		 * @return string|null the grootboekcode ID or null if it does not exist.
		 */
		public function get_grootboekcode_for_tax_amount( float $btw_amount, bool $is_shipping = false ): ?string {
			if ( $is_shipping ) {
				if ( self::$btw_none == $btw_amount ) {
					return $this->grootboekcode_btw_geen_shipping;
				} else if ( self::$btw_hoog == $btw_amount ) {
					return $this->grootboekcode_btw_hoog_shipping;
				}
			} elseif ( self::$btw_none === $btw_amount ) {
					return $this->grootboekcode_btw_geen;
			} else if ( self::$btw_hoog === $btw_amount ) {
				return $this->grootboekcode_btw_hoog;
			}
			return null;
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
}
