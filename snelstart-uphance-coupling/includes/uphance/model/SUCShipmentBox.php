<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/model/SUCJsonImportExport.php';
include_once SUC_ABSPATH . 'includes/uphance/model/SUCShipmentBoxItem.php';

class SUCShipmentBox extends SUCJsonImportExport {

	public int $id;
	public int $net_weight;
	public int $gross_weight;
	public string $dimensions;
	public ?int $box_weight;
	public int $shipment_id;

	/**
	 *
	 *
	 * @var SUCShipmentBoxItem[]
	 */
	public array $shipment_box_items;

	function __construct( int $id, int $net_weight, int $gross_weight, string $dimensions, ?int $box_weight, int $shipment_id, array $shipment_box_items ) {
		$this->id = $id;
		$this->net_weight = $net_weight;
		$this->gross_weight = $gross_weight;
		$this->dimensions = $dimensions;
		$this->box_weight = $box_weight;
		$this->shipment_id = $shipment_id;
		$this->shipment_box_items = $shipment_box_items;
	}
}
