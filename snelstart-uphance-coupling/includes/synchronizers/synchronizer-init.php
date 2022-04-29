<?php
/**
 * Settings initialize.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/synchronizers/class-synchronizer.php';
include_once SUC_ABSPATH . 'includes/synchronizers/class-succreditnotesynchronizer.php';
include_once SUC_ABSPATH . 'includes/synchronizers/class-sucinvoicesynchronizer.php';

if ( ! function_exists( 'initialize_synchronizers' ) ) {
	/**
	 * Initialize synchronizers.
	 *
	 * @param SUCUphanceClient   $uphance_client the Uphance client for the synchronizers.
	 * @param SUCSnelstartClient $snelstart_client the Snelstart client for the synchronizers.
	 *
	 * @return void
	 */
	function initialize_synchronizers( SUCUphanceClient $uphance_client, SUCSnelstartClient $snelstart_client ): void {
		SUCSynchronizer::register_synchronizer_class( SUCCreditNoteSynchronizer::$type, new SUCCreditNoteSynchronizer( $uphance_client, $snelstart_client ) );
		SUCSynchronizer::register_synchronizer_class( SUCInvoiceSynchronizer::$type, new SUCInvoiceSynchronizer( $uphance_client, $snelstart_client ) );
	}
}
