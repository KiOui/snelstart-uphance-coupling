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
if ( ! function_exists( 'cron_runner_sync_invoices' ) ) {
	/**
	 * Synchronize invoices to Snelstart.
	 *
	 * @return bool true on success, false when an error occurred.
	 */
	function cron_runner_sync_invoices(): bool {
		SUCLogging::instance()->write( 'Starting CRON runner for synchronizing invoices.' );

		$settings = get_option( 'suc_settings' );

		$invoice_from                     = isset( $settings ) ? $settings['uphance_synchronise_invoices_from'] : null;
		$max_to_sync                      = isset( $settings ) ? $settings['max_invoices_to_synchronize'] : null;
		$snelstart_grootboekcode_btw_hoog = isset( $settings ) ? $settings['snelstart_grootboekcode_btw_hoog'] : null;
		$snelstart_grootboekcode_btw_geen = isset( $settings ) ? $settings['snelstart_grootboekcode_btw_geen'] : null;
		$uphance_organisation = isset( $settings ) ? $settings['uphance_organisation'] : null;

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
