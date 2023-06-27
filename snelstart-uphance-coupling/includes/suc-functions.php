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
		include_once SUC_ABSPATH . 'includes/model/SUCPayment.php';
		return new SUCPayment( 'snelstart_' . $snelstart_payment['id'], floatval( $snelstart_payment['saldo'] ), $snelstart_payment['factuur_nummer'], $snelstart_payment['omschrijving'], new DateTime( $snelstart_payment['datum'] ), new DateTime( $snelstart_payment['datum'] ) );
	}
}

if ( ! function_exists( 'run_synchronizer' ) ) {
	/**
	 * Run a synchronizer.
	 *
	 * @param SUCSynchronisable $synchronizer The synchronizer to run.
	 *
	 * @return void
	 */
	function run_synchronizer( SUCSynchronisable $synchronizer ) {
		try {
			$synchronizer->setup();
			$synchronizer->setup_objects();
		} catch ( Exception $e ) {
			// TODO: Maybe log some kind of exception here.
			return;
		}
		$synchronizer->run();
		$synchronizer->after_run();
	}
}

if ( ! function_exists( 'suc_send_daily_mail' ) ) {
	/**
	 * Send daily mail for failed synchronizations.
	 *
	 * @return void
	 */
	function suc_send_daily_mail(): void {
		$admin_email = SUCSettings::instance()->get_settings()->get_value( 'send_error_email_to' );
		if ( is_null( $admin_email ) ) {
			return;
		}

		$now = time();
		$posts = get_posts(
			array(
				'post_type' => 'suc_synchronized',
				'date_query' => array(
					'before' => gmdate( 'c', $now ),
					'after' => gmdate( 'c', strtotime( '- 1 days', $now ) ),
				),
				'numberposts' => -1,
			),
		);

		$summary = array();

		foreach ( $posts as $post ) {
			$post_meta = get_post_meta( $post->ID );

			$succeeded = $post_meta['succeeded'];
			$type = $post_meta['type'];
			$method = $post_meta['method'];

			if ( is_array( $succeeded ) && count( $succeeded ) > 0 ) {
				$succeeded = '1' === $succeeded[0];
			}

			if ( is_array( $type ) && count( $method ) > 0 ) {
				$type = strval( $type[0] );
			}

			if ( is_array( $method ) && count( $method ) > 0 ) {
				$method = $method[0];
			}

			if ( 'create' === $method || 'update' === $method || 'delete' === $method ) {
				if ( ! array_key_exists( $type, $summary ) ) {
					$summary[ $type ] = array(
						'create' => array(
							'succeeded' => 0,
							'not_succeeded' => 0,
						),
						'update' => array(
							'succeeded' => 0,
							'not_succeeded' => 0,
						),
						'delete' => array(
							'succeeded' => 0,
							'not_succeeded' => 0,
						),
					);
				}

				if ( $succeeded ) {
					$summary[ $type ][ $method ]['succeeded'] = $summary[ $type ][ $method ]['succeeded'] + 1;
				} else {
					$summary[ $type ][ $method ]['not_succeeded'] = $summary[ $type ][ $method ]['not_succeeded'] + 1;
				}
			}
		}

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$subject = 'Snelstart Uphance Coupling: Daily Summary';
		ob_start();
		?>
		<!DOCTYPE HTML>
			<html style="font-family: Roboto, sans-serif;">
				<head>
					<title>Snelstart Uphance Coupling: Summary Email</title>
				</head>
				<body style="margin: 0; padding: 0;">
					<div class="header" style="width: calc(100% - 30px);color: white;background-color: black;padding: 15px;">
						<div class="top-header" style="width: calc(100%-30px);display: flex;align-items: center;">
						</div>
						<div class="bottom-header">
							<p style="margin: 0;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
						</div>
					</div>
					<div class="salutation" style="padding: 15px;padding-top: 30px;width: calc(100%-30px);font-size: 30px;text-align: center;">
						Hallo!
					</div>

					<div class="text" style="display: flex;flex-wrap: wrap;justify-content: center;padding: 15px;align-items: stretch;width: calc(100%-30px);">
						<p style="max-width: 400px;">
							Dit is een automatische samenvatting email van de gesynchroniseerde objecten van de afgelopen dag. Hieronder een tabel met details.
						</p>
					</div>

					<div class="table-view" style="display: flex; flex-wrap: nowrap; overflow: scroll; justify-content: center; margin-bottom: 10px;">
						<table style="border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
							<thead style="border: 1px solid #c3c4c7;">
								<tr>
									<th style="padding: 10px;">
										Type
									</th>
									<th style="padding: 10px;">
										Create
									</th>
									<th style="padding: 10px;">
										Update
									</th>
									<th style="padding: 10px;">
										Delete
									</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $summary as $type => $data ) : ?>
									<tr style="background-color: #f6f7f7;">
										<td style="padding: 10px;">
											<?php echo esc_html( $type ); ?>
										</td>
										<td style="padding: 10px;<?php if ( $data['create']['succeeded'] > 0 ) : ?>
											color: green;
										<?php endif; ?>">
											<?php echo esc_html( $data['create']['succeeded'] ); ?> succeeded<br>
										</td>
										<td style="padding: 10px;<?php if ( $data['update']['succeeded'] > 0 ) : ?>
											color: green;
										<?php endif; ?>">
											<?php echo esc_html( $data['update']['succeeded'] ); ?> succeeded<br>
										</td>
										<td style="padding: 10px;<?php if ( $data['delete']['succeeded'] > 0 ) : ?>
											color: green;
										<?php endif; ?>">
											<?php echo esc_html( $data['delete']['succeeded'] ); ?> succeeded<br>
										</td>
									</tr>
									<tr>
										<td style="padding: 10px;">
										</td>
										<td style="padding: 10px; 
										<?php if ( $data['create']['not_succeeded'] > 0 ) : ?>
											color: red;
										<?php endif; ?>">
											<?php echo esc_html( $data['create']['not_succeeded'] ); ?> not succeeded
										</td>
										<td style="padding: 10px;
										<?php if ( $data['update']['not_succeeded'] > 0 ) : ?>
											color: red;
										<?php endif; ?>">
											<?php echo esc_html( $data['update']['not_succeeded'] ); ?> not succeeded
										</td>
										<td style="padding: 10px;
										<?php if ( $data['delete']['not_succeeded'] > 0 ) : ?>
											color: red;
										<?php endif; ?>">
											<?php echo esc_html( $data['delete']['not_succeeded'] ); ?> not succeeded
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<div class="greeting" style="text-align: center;">
						Met vriendelijke groet,<br> <strong><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong>
					</div>

					<div class="footer" style="text-align: center;padding-top: 30px;font-size: 10px;width: calc(100%-30px);">
						Deze e-mail is automatisch gegenereerd door <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. Antwoorden is niet mogelijk.
					</div>
				</body>
			</html>
		<?php
		$message = ob_get_clean();
		wp_mail( $admin_email, $subject, $message, $headers );
	}
}

if ( ! function_exists( 'cron_runner_sync_all' ) ) {
	/**
	 * Synchronization runner for cron.
	 *
	 * @return void
	 */
	function cron_runner_sync_all(): void {
		include_once SUC_ABSPATH . 'includes/synchronizers/class-sucsynchronizer.php';

		$uphance_client   = SUCUphanceClient::instance();
		$snelstart_client = SUCSnelstartClient::instance();
		$settings_manager = SUCSettings::instance()->get_settings();

		if ( ! isset( $uphance_client ) || ! isset( $snelstart_client ) ) {
			return;
		}

		$uphance_organisation = $settings_manager->get_value( 'uphance_organisation' );

		if ( ! isset( $uphance_organisation ) ) {
			return;
		} else {
			try {
				$uphance_client->set_current_organisation( $uphance_organisation );
			} catch ( SUCAPIException $e ) {
				return;
			}
		}

		foreach ( SUCSynchronizer::$synchronizer_classes as $type => $synchronizer_class ) {
			if ( $synchronizer_class->enabled() ) {
				run_synchronizer( $synchronizer_class );
			}
		}
		$settings_manager->save_settings();
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

if ( ! function_exists( 'suc_retrieve_address_information' ) ) {
	/**
	 * Get or create relatie with specific name.
	 *
	 * @param $customer array the Uphance customer.
	 *
	 * @return ?array An array with the relatie if succeeded, null if the relatie does not exist or multiple relaties were returned.
	 */
	function suc_retrieve_address_information( array $customer ): ?array {
		if ( array_key_exists( 'addresses', $customer ) ) {
			$addresses = $customer['addresses'];
			foreach ( $addresses as $address ) {
				if ( true === $address['default_for_shipping'] ) {
					return $address;
				}
			}
		}
		return null;
	}
}

if ( ! function_exists( 'suc_convert_address_information' ) ) {
	/**
	 * Convert Uphance address information to Snelstart Vestigingsadres information.
	 *
	 * @param $address array the Uphance address information.
	 *
	 * @return ?array An array with Snelstart Vestigingsadres information.
	 */
	function suc_convert_address_information( array $address ): ?array {
		include_once SUC_ABSPATH . 'includes/snelstart/class-suclanden.php';
		$snelstart_countries = SUCLanden::instance();
		$snelstart_country_found = $snelstart_countries->get_country_id_from_country_code( $address['country'] );
		if ( is_null( $snelstart_country_found ) ) {
			return null;
		}
		return array(
			'contactpersoon' => '',
			'straat' => $address['line_1'],
			'postcode' => $address['postcode'],
			'plaats' => $address['city'],
			'land' => array(
				'id' => $snelstart_country_found->id,
			),
		);
	}
}

if ( ! function_exists( 'suc_retrieve_vat_number' ) ) {
	/**
	 * Retrieve the VAT number from an Uphance Customer.
	 *
	 * @param $customer array the Uphance customer.
	 *
	 * @return ?string the VAT number.
	 */
	function suc_retrieve_vat_number( array $customer ): ?string {
		return \str_replace( ' ', '', $customer['vat_number'] );
	}
}

if ( ! function_exists( 'suc_get_or_create_relatie_with_name' ) ) {
	/**
	 * Get or create relatie with specific name.
	 *
	 * @param $client SUCSnelstartClient the Snelstart client.
	 * @param $customer array the customer from Uphance.
	 *
	 * @return array An array with the relatie if succeeded.
	 * @throws Exception|SUCAPIException On Exception with API or when multiple relaties were found.
	 */
	function get_or_create_relatie_with_name( SUCSnelstartClient $client, array $customer ): array {
		$naam = $customer['name'];
		// Snelstart relatie names can only be 50 characters long.
		if ( 50 < \strlen( $naam ) ) {
			$naam = \substr( $naam, 0, 50 );
		}
		$naam_escaped = str_replace( "'", "''", $naam );
		$relaties = $client->relaties( null, null, "Naam eq '$naam_escaped'" );

		if ( count( $relaties ) === 1 ) {
			return $relaties[0];
		} else if ( count( $relaties ) > 1 ) {
			throw new Exception( sprintf( __( 'Multiple relaties found with name %s', 'snelstart-uphance-coupling' ), $naam ) );
		}

		$address = suc_retrieve_address_information( $customer );
		if ( ! is_null( $address ) ) {
			$address = suc_convert_address_information( $address );
		}
		$vat_number = suc_retrieve_vat_number( $customer );

		return $client->add_relatie(
			array(
				'relatieSoort' => array(
					'Klant',
				),
				'naam' => $naam,
				'btwNummer' => $vat_number,
				'vestigingsAdres' => $address,
			)
		);
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

if ( ! function_exists( 'suc_get_shipping_choices' ) ) {
	/**
	 * Get shipping choices.
	 *
	 * @return array|null An array of string => string choices on succes, null on failure.
	 */
	function suc_get_shipping_choices(): ?array {
		include_once SUC_ABSPATH . 'includes/class-succache.php';
		$shipping_methods = SUCCache::instance()->get_shipping_methods();
		if ( is_null( $shipping_methods ) ) {
			return null;
		}
		$retvalue = array();
		foreach ( $shipping_methods['shipping_methods'] as $shipping_method ) {
			$retvalue[ strval( $shipping_method['name'] ) ] = $shipping_method['name'];
		}

		return $retvalue;
	}
}

if ( ! function_exists( 'suc_get_grootboek_choices' ) ) {
	/**
	 * Get choices for grootboek setting.
	 *
	 * @return array|null An array of string => string choices on succes, null on failure.
	 */
	function suc_get_grootboek_choices(): ?array {
		include_once SUC_ABSPATH . 'includes/class-succache.php';
		$grootboeken = SUCCache::instance()->get_grootboeken();
		if ( is_null( $grootboeken ) ) {
			return null;
		}
		$retvalue = array();
		foreach ( $grootboeken as $grootboek ) {
			$retvalue[ strval( $grootboek['id'] ) ] = $grootboek['nummer'] . ' (' . $grootboek['omschrijving'] . ', ' . $grootboek['rekeningCode'] . ')';
		}

		return $retvalue;
	}
}

if ( ! function_exists( 'suc_get_invoices_choices' ) ) {
	/**
	 * Get choices for invoice setting.
	 *
	 * @return array|null An array of string => string choices on succes, null on failure.
	 */
	function suc_get_invoices_choices(): ?array {
		$invoices = SUCCache::instance()->get_invoices();
		if ( false === $invoices ) {
			return null;
		}
		$retvalue = array();
		foreach ( $invoices['invoices'] as $invoice ) {
			$retvalue[ $invoice['id'] ] = $invoice['invoice_number'];
		}

		return $retvalue;
	}
}

if ( ! function_exists( 'suc_get_credit_notes_choices' ) ) {
	/**
	 * Get choices for credit note setting.
	 *
	 * @return array|null An array of string => string choices on succes, null on failure.
	 */
	function suc_get_credit_notes_choices(): ?array {
		$credit_notes = SUCCache::instance()->get_credit_notes();
		if ( false === $credit_notes ) {
			return null;
		}
		$retvalue = array();
		foreach ( $credit_notes['credit_notes'] as $credit_note ) {
			$retvalue[ $credit_note['id'] ] = $credit_note['credit_note_number'];
		}

		return $retvalue;
	}
}

if ( ! function_exists( 'suc_get_organisations_choices' ) ) {
	/**
	 * Get choices for organisations setting.
	 *
	 * @return array|null An array of string => string choices on succes, null on failure.
	 */
	function suc_get_organisations_choices(): ?array {
		$organisations = SUCCache::instance()->get_organisations();
		if ( is_null( $organisations ) ) {
			return null;
		}
		$retvalue = array();
		foreach ( $organisations['organisations'] as $organisation ) {
			$retvalue[ strval( $organisation['id'] ) ] = $organisation['name'];
		}

		return $retvalue;
	}
}

if ( ! function_exists( 'suc_reset_uphance_token_on_settings_change' ) ) {
	/**
	 * Reset Uphance token info when username/password settings change.
	 *
	 * @param string $setting_id The setting ID as string.
	 * @param mixed  $old_value The old value of the setting.
	 * @param mixed  $new_value The new value of the setting.
	 * @param array  $subscribers The list of subscribers subscribed to this event.
	 *
	 * @return void
	 */
	function suc_reset_uphance_token_on_settings_change( string $setting_id, $old_value, $new_value, array $subscribers ) {
		if ( $old_value !== $new_value ) {
			$uphance_client = SUCUphanceClient::instance();
			if ( ! is_null( $uphance_client ) ) {
				$uphance_client->reset_auth_token();
			}
		}
	}
}

if ( ! function_exists( 'suc_reset_snelstart_token_on_settings_change' ) ) {
	/**
	 * Reset Snelstart token info when authentication credentials settings change.
	 *
	 * @param string $setting_id The setting ID as string.
	 * @param mixed  $old_value The old value of the setting.
	 * @param mixed  $new_value The new value of the setting.
	 * @param array  $subscribers The list of subscribers subscribed to this event.
	 *
	 * @return void
	 */
	function suc_reset_snelstart_token_on_settings_change( string $setting_id, $old_value, $new_value, array $subscribers ) {
		if ( $old_value !== $new_value ) {
			$snelstart_client = SUCSnelstartClient::instance();
			if ( ! is_null( $snelstart_client ) ) {
				$snelstart_client->reset_auth_token();
			}
		}
	}
}
