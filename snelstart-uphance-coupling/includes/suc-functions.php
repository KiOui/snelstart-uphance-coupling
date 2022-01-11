<?php
/**
 * Settings class
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/snelstart/class-sucsnelstartsynchronizer.php';

if ( ! function_exists( 'suc_convert_date_to_amount_of_days_until' ) ) {
	/**
	 * Convert a date as string to days until that date.
	 *
	 * @param string $date date as string (00-00-0000)
	 *
	 * @return bool|int false on failure, a positive integer when date is in the future, 0 when date is in the past
	 */
	function suc_convert_date_to_amount_of_days_until( string $date ): bool|int {
		try {
			$date_obj = new DateTime($date);
			$now = new DateTime();
			$interval = $date_obj->diff($now);
			return max(0, $interval->days);
		} catch (Exception $e) {
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
					SUCLogging::instance()->write( sprintf( __( 'Failed to create date objects for BTW soort %1$s the following Exception occurred: %2$s', 'snelstart-uphance-coupling' ), $btw_soort['btwSoort'], $e ) );
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

if ( ! function_exists('convert_snelstart_payment_to_payment') ) {

	/**
	 * @throws Exception on conversion error
	 */
	function convert_snelstart_payment_to_payment( array $snelstart_payment ): SUCPayment {
		include_once SUC_ABSPATH . 'includes/model/class-sucpayment.php';
		return new SUCPayment( floatval( $snelstart_payment['saldo'] ), $snelstart_payment['factuurNummer'], $snelstart_payment['omschrijving'], new DateTime( $snelstart_payment['datum'] ), new DateTime( $snelstart_payment['datum'] ) );
	}
}

if ( ! function_exists( 'sync_invoices' ) ) {
	/**
	 * Synchronize invoices to Snelstart.
	 *
	 * @param SUCUphanceClient $uphance_client the Uphance client
	 * @param SUCSnelstartClient $snelstart_client the Snelstart client
	 *
	 * @return bool false when invoice synchronisation failed, true when it succeeded
	 */
	function sync_invoices( SUCUphanceClient $uphance_client, SUCSnelstartClient $snelstart_client ): bool {
		$settings = get_option( 'suc_settings' );

		if ( ! isset( $settings ) ) {
			SUCLogging::instance()->write( __( 'Settings are not specified. Synchronisation can not continue.', 'snelstart-uphance-coupling' ) );
			return false;
		}

		$invoice_from                     = $settings['uphance_synchronise_invoices_from'];
		$max_to_sync                      = $settings['max_invoices_to_synchronize'];
		$snelstart_grootboekcode_btw_hoog = $settings['snelstart_grootboekcode_btw_hoog'];
		$snelstart_grootboekcode_btw_geen = $settings['snelstart_grootboekcode_btw_geen'];

		try {
			if ( isset( $invoice_from ) && '' !== $invoice_from ) {
				$invoices = $uphance_client->invoices( $invoice_from )->result;
			} else {
				$invoices = $uphance_client->invoices()->result;
			}
		} catch ( SUCAPIException $e ) {
			SUCLogging::instance()->write( $e );
			SUCLogging::instance()->write( __( 'An exception occurred while getting invoice data from Uphance.', 'snelstart-uphance-coupling' ) );

			return false;
		}

		$invoices = $invoices['invoices'];

		if ( count( $invoices ) > 0 ) {
			if ( isset( $max_to_sync ) && '' !== $max_to_sync ) {
				$invoices = array_slice( $invoices, 0, $max_to_sync );
			}
			$amount_of_invoices = count( $invoices );
			for ( $i = 0; $i < $amount_of_invoices; $i ++ ) {
				try {
					$invoices[ $i ]['customer'] = $uphance_client->customer_by_id( $invoices[ $i ]['company_id'] )['customer'];
				} catch ( SUCAPIException $e ) {
					$invoice_number = $invoices[ $i ]['invoice_number'];
					SUCLogging::instance()->write( $e );
					SUCLogging::instance()->write( sprintf( __( 'Could not retrieve customer for invoice %s.', 'snelstart-uphance-coupling' ), $invoice_number ) );
					$invoices[ $i ]['customer'] = null;
				}
			}

			try {
				$invoice_synchronizer = new SUCSnelstartSynchronizer( $snelstart_client, $snelstart_grootboekcode_btw_hoog, $snelstart_grootboekcode_btw_geen );
			} catch ( SUCAPIException $e ) {
				SUCLogging::instance()->write( $e );
				SUCLogging::instance()->write( __( 'Failed to create Snelstart Synchronizer.', 'snelstart-uphance-coupling' ) );
				return false;
			}

			$invoice_synchronizer->sync_invoices_to_snelstart( $invoices );
			$latest_invoice                                = $invoices[ count( $invoices ) - 1 ]['id'];
			$settings['uphance_synchronise_invoices_from'] = $latest_invoice;
			update_option( 'suc_settings', $settings );
			SUCLogging::instance()->write( sprintf( __( 'Succeeded synchronization. Latest invoice id: %s.', 'snelstart-uphance-coupling' ), $latest_invoice ) );
		} else {
			SUCLogging::instance()->write( __( 'No new invoices found to synchronize.', 'snelstart-uphance-coupling' ) );
		}
		return true;
	}
}

if ( ! function_exists( 'sync_payments' ) ) {
	function sync_payments( SUCUphanceClient $uphance_client, SUCSnelstartClient $snelstart_client ): bool {
		include_once SUC_ABSPATH . 'includes/snelstart/model/class-sucsnelstartgrootboekmutatie.php';
		$settings = get_option( 'suc_settings' );

		if ( ! isset( $settings ) ) {
			SUCLogging::instance()->write( __( 'Settings are not specified. Synchronisation can not continue.', 'snelstart-uphance-coupling' ) );
			return false;
		}

		$snelstart_grootboekcode_debiteuren = $settings['snelstart_grootboekcode_debiteuren'];

		$grootboeken = $snelstart_client->grootboeken();
		$grootboekmutaties = $snelstart_client->grootboekmutaties( null, null, "Grootboek/Id eq guid'$snelstart_grootboekcode_debiteuren'" );
		$payments_with_debiteuren_obj = array_map(function ($grootboekmutatie) {
			return SUCSnelstartGrootboekmutatie::from_snelstart($grootboekmutatie);
		}, $payments_with_debiteuren);
		$payments_with_debiteuren_credit = array_filter($grootboekmutaties, function ($payment) {
			return true;
		});
		echo '<br>';
		echo '<pre>';
		print_r($payments_with_debiteuren_obj);
		echo '</pre>';
		exit;
	}

}

if ( ! function_exists( 'cron_runner_sync_all' ) ) {
	/**
	 * Synchronization runner for cron.
	 *
	 * @return bool true on success, false when an error occurred.
	 */
	function cron_runner_sync_all(): bool {
		SUCLogging::instance()->write( 'Starting CRON runner for synchronizing invoices.' );

		$settings = get_option( 'suc_settings' );

		if ( ! isset( $settings ) ) {
			SUCLogging::instance()->write( __( 'Settings are not specified. Synchronisation can not continue.', 'snelstart-uphance-coupling' ) );
			return false;
		}

		$uphance_client   = SUCUphanceClient::instance();
		$snelstart_client = SUCSnelstartClient::instance();

		if ( ! isset( $uphance_client ) ) {
			SUCLogging::instance()->write( __( 'Uphance client could not be instantiated, are all required settings set?', 'snelstart-uphance-coupling' ) );
			return false;
		}
		if ( ! isset( $snelstart_client ) ) {
			SUCLogging::instance()->write( __( 'Snelstart client could not be instantiated, are all required settings set?', 'snelstart-uphance-coupling' ) );
			return false;
		}

		$uphance_organisation = $settings['uphance_organisation'];

		if ( ! isset( $uphance_organisation ) && '' !== $uphance_organisation ) {
			SUCLogging::instance()->write( __( 'Uphance organisation ID not set, please set this setting before running.', 'snelstart-uphance-coupling' ) );
			return false;
		} else {
			try {
				$uphance_client->set_current_organisation( $uphance_organisation );
			} catch ( SUCAPIException $e ) {
				SUCLogging::instance()->write( $e );
				SUCLogging::instance()->write( __( 'An error occurred while setting the Uphance Organisation.', 'snelstart-uphance-coupling' ) );
				return false;
			}
		}

		if ( $settings['synchronize_invoices_to_snelstart'] == 1 ) {
			// Invoice synchronization
			if (! ( sync_invoices( $uphance_client, $snelstart_client ) ) ) {
				SUCLogging::instance()->write( __( 'Invoice synchronisation returned an error.', 'snelstart-uphance-coupling' ) );
			} else {
				SUCLogging::instance()->write( __( 'Invoice synchronisation succeeded successfully.', 'snelstart-uphance-coupling' ) );
			}
		} else {
			SUCLogging::instance()->write( __( 'Skipped invoice synchronization from Snelstart to Uphance because it is disabled in settings.', 'snelstart-uphance-coupling' ) );
		}

		if ( $settings['synchronize_payments_to_uphance'] == 1 ) {
			// Payments synchronization
			sync_payments( $uphance_client, $snelstart_client );
		} else {
			SUCLogging::instance()->write( __( 'Skipped payments synchronization from Uphance to Snelstart because it is disabled in settings.', 'snelstart-uphance-coupling' ) );
		}

		return true;
	}
}
