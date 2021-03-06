<?php
/**
 * Settings class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'suc_convert_date_to_amount_of_days_until' ) ) {
	/**
	 * Convert a date as string to days until that date.
	 *
	 * @param string $date date as string (00-00-0000).
	 *
	 * @return bool|int false on failure, a positive integer when date is in the future, 0 when date is in the past.
	 */
	function suc_convert_date_to_amount_of_days_until( string $date ) {
		try {
			$date_obj = new DateTime( $date );
			$now = new DateTime();
			$interval = $date_obj->diff( $now );
			return max( 0, $interval->days );
		} catch ( Exception $e ) {
			return false;
		}
	}
}

if ( ! function_exists( 'suc_get_current_btw_soorten' ) ) {
	/**
	 * Get all current BTW types stored in Snelstart.
	 *
	 * @param SUCSnelstartClient $snelstart_client the Snelstart client to use.
	 *
	 * @return array the BTW types as an array.
	 * @throws SUCAPIException When retrieving BTW types fails.
	 */
	function suc_get_current_btw_soorten( SUCSnelstartClient $snelstart_client ): array {
		$btw_soorten = $snelstart_client->btwtarieven();

		return array_filter(
			$btw_soorten,
			function ( array $btw_soort ): bool {
				$now = new DateTime( 'now' );
				try {
					$from_date = new DateTime( $btw_soort['datumVanaf'] );
					$to_date   = new DateTime( $btw_soort['datumTotEnMet'] );
				} catch ( Exception $e ) {
					return false;
				}
				return $from_date < $now && $now <= $to_date;
			}
		);
	}
}

if ( ! function_exists( 'suc_sanitize_boolean_default_false' ) ) {
	/**
	 * Sanitize boolean value (default to false).
	 *
	 * @param $input
	 * @return bool
	 */
	function suc_sanitize_boolean_default_false( $input ): bool {
		$filtered = filter_var( $input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
		if ( is_null( $filtered ) ) {
			return false;
		} else {
			return $filtered;
		}
	}
}

if ( ! function_exists( 'convert_snelstart_payment_to_payment' ) ) {

	/**
	 * Convert an array to a SUCPayment.
	 *
	 * @param array $snelstart_payment a key => value array to be converted to an SUCPayment.
	 *
	 * @return SUCPayment an SUCPayment converted from the array.
	 * @throws Exception On conversion error.
	 */
	function convert_snelstart_payment_to_payment( array $snelstart_payment ): SUCPayment {
		include_once SUC_ABSPATH . 'includes/model/class-sucpayment.php';
		return new SUCPayment( 'snelstart_' . $snelstart_payment['id'], floatval( $snelstart_payment['saldo'] ), $snelstart_payment['factuur_nummer'], $snelstart_payment['omschrijving'], new DateTime( $snelstart_payment['datum'] ), new DateTime( $snelstart_payment['datum'] ) );
	}
}

if ( ! function_exists( 'cron_runner_sync_all' ) ) {
	/**
	 * Synchronization runner for cron.
	 *
	 * @return void
	 */
	function cron_runner_sync_all(): void {
		include_once SUC_ABSPATH . 'includes/synchronizers/synchronizer-init.php';
		include_once SUC_ABSPATH . 'includes/synchronizers/class-sucsynchronizer.php';

		$uphance_client   = SUCUphanceClient::instance();
		$snelstart_client = SUCSnelstartClient::instance();
		$settings_manager = SUCSettings::instance()->get_manager();

		if ( ! isset( $uphance_client ) || ! isset( $snelstart_client ) ) {
			return;
		}

		$uphance_organisation = $settings_manager->get_value_by_setting_id( 'uphance_organisation' );

		if ( ! isset( $uphance_organisation ) ) {
			return;
		} else {
			try {
				$uphance_client->set_current_organisation( $uphance_organisation );
			} catch ( SUCAPIException $e ) {
				return;
			}
		}

		initialize_synchronizers( $uphance_client, $snelstart_client );

		foreach ( SUCSynchronizer::$synchronizer_classes as $type => $synchronizer_class ) {
			if ( $synchronizer_class->enabled() ) {
				try {
					$synchronizer_class->setup();
				} catch ( Exception $e ) {
					$error_log = new SUCErrorLogging();
					$error_log->set_error( $e, 'default', null, null );
					break;
				}
				$synchronizer_class->run();
				$synchronizer_class->after_run();
			}
		}
	}
}

if ( ! function_exists( 'suc_format_number' ) ) {
	/**
	 * Format a number to two decimals maximum.
	 *
	 * @param float $number the number to format.
	 * @param int   $decimals the amount of decimals to format to.
	 *
	 * @return string a string of the formatted number.
	 */
	function suc_format_number( float $number, int $decimals = 2 ): string {
		return number_format( $number, $decimals, '.', '' );
	}
}

if ( ! function_exists( 'suc_get_or_create_relatie_with_name' ) ) {
	/**
	 * Get or create relatie with specific name.
	 *
	 * @param $client SUCSnelstartClient the Snelstart client.
	 * @param $naam string the name of the client to create or retrieve.
	 *
	 * @return array|null An array with the relatie if succeeded, null if the relatie does not exist or multiple relaties were returned.
	 * @throws Exception|SUCAPIException On Exception with API or when multiple relaties were found.
	 */
	function get_or_create_relatie_with_name( SUCSnelstartClient $client, string $naam ): array {
		$naam_escaped = str_replace( "'", "''", $naam );
		$relaties = $client->relaties( null, null, "Naam eq '$naam_escaped'" );

		if ( count( $relaties ) === 1 ) {
			return $relaties[0];
		} else if ( count( $relaties ) > 1 ) {
			throw new Exception( sprintf( __( 'Multiple relaties found with name %s', 'snelstart-uphance-coupling' ), $naam ) );
		}
		return $client->add_relatie( array( 'Klant' ), $naam );
	}
}

if ( ! function_exists( 'suc_construct_btw_line_items' ) ) {
	/**
	 * Construct BTW line items for an invoice.
	 *
	 * @param array $items the item array.
	 *
	 * @return array an array with BTW line items, null if constructing the BTW line items failed.
	 */
	function suc_construct_btw_line_items( array $items ): array {
		include_once SUC_ABSPATH . 'includes/snelstart/class-sucbtw.php';
		$btw_items = array();
		foreach ( $items as $item ) {
			$price = $item['unit_price'];
			$tax_level = $item['tax_level'];
			$amount = array_reduce(
				$item['line_quantities'],
				function( int $carry, array $item ) {
					return $carry + $item['quantity'];
				},
				0
			);
			$tax_name = SUCBTW::convert_btw_amount_to_name( $tax_level );
			if ( key_exists( $tax_name, $btw_items ) ) {
				$btw_items[ $tax_name ]['btwBedrag'] = $btw_items[ $tax_name ]['btwBedrag'] + $price * $amount * $tax_level / 100;
			} else {
				$btw_items[ $tax_name ] = array(
					'btwSoort' => $tax_name,
					'btwBedrag' => $price * $amount * $tax_level / 100,
				);
			}
		}
		// Format all btw items such that they have a maximum of two decimals.
		foreach ( array_keys( $btw_items ) as $btw_items_key ) {
			$btw_items[ $btw_items_key ]['btwBedrag'] = suc_format_number( $btw_items[ $btw_items_key ]['btwBedrag'] );
		}
		return array_values( $btw_items );
	}
}

if ( ! function_exists( 'suc_construct_order_line_items' ) ) {
	/**
	 * Construct order line items for an invoice.
	 *
	 * @param array  $items the item array.
	 * @param SUCBTW $btw_converter the BTW converter.
	 *
	 * @return array|null an array with line items, null if constructing the line items failed.
	 * @throws Exception When construction of order line items failed.
	 */
	function suc_construct_order_line_items( array $items, SUCBTW $btw_converter ): array {
		$to_order = array();
		foreach ( $items as $item ) {
			$price = $item['unit_price'];
			$product_id = $item['product_id'];
			$product_name = $item['product_name'];
			$tax_level = $item['tax_level'];
			$amount = array_reduce(
				$item['line_quantities'],
				function( int $carry, array $item ) {
					return $carry + $item['quantity'];
				},
				0
			);
			$grootboekcode = $btw_converter->get_grootboekcode_for_tax_amount( $tax_level );
			$tax_type = $btw_converter->convert_btw_amount_to_type( $tax_level );
			if ( ! isset( $tax_type ) ) {
				throw new Exception( sprintf( __( 'Failed to get tax type for %.2F.', 'snelstart-uphance-coupling' ), $tax_level ) );
			}
			if ( ! isset( $grootboekcode ) ) {
				throw new Exception( sprintf( __( 'Failed to get the grootboekcode for tax level %.2F.', 'snelstart-uphance-coupling' ), $tax_level ) );
			}
			$tax_name   = $tax_type['btwSoort'];
			$to_order[] = array(
				'omschrijving' => "$amount x $product_id $product_name",
				'grootboek'    => array(
					'id' => $grootboekcode,
				),
				'bedrag'       => suc_format_number( $price * $amount ),
				'btwSoort'     => $tax_name,
			);
		}
		return $to_order;
	}
}
