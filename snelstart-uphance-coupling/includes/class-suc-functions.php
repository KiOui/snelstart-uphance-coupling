<?php

include_once SUC_ABSPATH . 'includes/snelstart/class-snelstart-synchronizer.php';

if ( ! function_exists( 'suc_get_current_btw_soorten' ) ) {
	/**
	 * Get all current BTW types stored in Snelstart.
	 *
	 * @param SUCSnelstartClient $snelstart_client the Snelstart client to use
	 *
	 * @return array the BTW types as an array
	 * @throws SUCAPIException when retrieving BTW types fails
	 */
	function suc_get_current_btw_soorten( SUCSnelstartClient $snelstart_client ): array {
		$btw_soorten = $snelstart_client->btwtarieven();

		return array_filter( $btw_soorten, function ( array $btw_soort ): bool {
			$now = new DateTime( 'now' );
			try {
				$from_date = new DateTime( $btw_soort['datumVanaf'] );
				$to_date   = new DateTime( $btw_soort['datumTotEnMet'] );
			} catch ( Exception $e ) {
				SUCLogging::instance()->write( "Failed to create date objects for BTW soort " . $btw_soort['btwSoort'] . ", the following Exception occurred: " . $e );

				return false;
			}

			return $from_date < $now && $now <= $to_date;
		} );
	}
}
if ( ! function_exists( 'cron_runner_sync_invoices' ) ) {
	/**
	 * Synchronize invoices to Snelstart.
	 *
	 * @return bool true on success, false when an error occurred
	 */
	function cron_runner_sync_invoices(): bool {
		SUCLogging::instance()->write( "Starting CRON runner for synchronizing invoices." );

		$settings = get_option( 'suc_settings' );

		$invoice_from                     = isset( $settings ) ? $settings['uphance_synchronise_invoices_from'] : null;
		$max_to_sync                      = isset( $settings ) ? $settings['max_invoices_to_synchronize'] : null;
		$snelstart_grootboekcode_btw_hoog = isset( $settings ) ? $settings['snelstart_grootboekcode_btw_hoog'] : null;
		$snelstart_grootboekcode_btw_geen = isset( $settings ) ? $settings['snelstart_grootboekcode_btw_geen'] : null;
		$uphance_organisation = isset( $settings ) ? $settings['uphance_organisation'] : null;

		$uphance_client   = SUCUphanceClient::instance();
		$snelstart_client = SUCSnelstartClient::instance();

		if ( ! isset( $uphance_client ) ) {
			SUCLogging::instance()->write( __( "Uphance client could not be instantiated, are all required settings set?", "snelstart-uphance-coupling" ) );
			return false;
		}
		if ( ! isset( $snelstart_client ) ) {
			SUCLogging::instance()->write( __( "Snelstart client could not be instantiated, are all required settings set?", "snelstart-uphance-coupling") );
			return false;
		}

		if ( ! isset( $uphance_organisation ) && $uphance_organisation !== "") {
			SUCLogging::instance()->write( __( "Uphance organisation ID not set, please set this setting before running.", "snelstart-uphance-coupling" ) );
			return false;
		} else {
			try {
				$uphance_client->set_current_organisation( $uphance_organisation );
			} catch (SUCAPIException $e) {
				SUCLogging::instance()->write( $e );
				SUCLogging::instance()->write( __( "An error occurred while setting the Uphance Organisation.", "snelstart-uphance-coupling" ) );
				return false;
			}
		}

		try {
			if ( isset( $invoice_from ) && $invoice_from !== "" ) {
				$invoices = $uphance_client->invoices( $invoice_from )->result;
			} else {
				$invoices = $uphance_client->invoices()->result;
			}
		} catch ( SUCAPIException $e ) {
			SUCLogging::instance()->write( $e );
			SUCLogging::instance()->write( __( "An exception occurred while getting invoice data from Uphance.", "snelstart-uphance-coupling" ) );

			return false;
		}

		$invoices = $invoices["invoices"];

		if ( sizeof( $invoices ) > 0 ) {
			if ( isset( $max_to_sync ) && $max_to_sync !== "" ) {
				$invoices = array_slice( $invoices, 0, $max_to_sync );
			}

			for ( $i = 0; $i < sizeof( $invoices ); $i ++ ) {
				try {
					$invoices[ $i ]["customer"] = $uphance_client->customer_by_id( $invoices[ $i ]['company_id'] )["customer"];
				} catch ( SUCAPIException $e ) {
					$invoice_number = $invoices[ $i ]["invoice_number"];
					SUCLogging::instance()->write( $e );
					SUCLogging::instance()->write( __( "Could not retrieve customer for invoice $invoice_number.", "snelstart-uphance-coupling" ) );
					$invoices[ $i ]["customer"] = null;
				}
			}

			try {
				$invoice_synchronizer = new SUCSnelstartSynchronizer( $snelstart_client, $snelstart_grootboekcode_btw_hoog, $snelstart_grootboekcode_btw_geen );
			} catch ( SUCAPIException $e ) {
				SUCLogging::instance()->write( $e );
				SUCLogging::instance()->write( __( "Failed to create Snelstart Synchronizer.", "snelstart-uphance-coupling" ) );
				return false;
			}

			$invoice_synchronizer->sync_invoices_to_snelstart( $invoices );
			$latest_invoice                                = $invoices[ sizeof( $invoices ) - 1 ]["id"];
			$settings['uphance_synchronise_invoices_from'] = $latest_invoice;
			update_option( 'suc_settings', $settings );
			SUCLogging::instance()->write( __( "Succeeded synchronization. Latest invoice id: $latest_invoice.", "snelstart-uphance-coupling") );
		} else {
			SUCLogging::instance()->write( __( "No new invoices found to synchronize.", "snelstart-uphance-coupling") );
		}

		return true;
	}
}