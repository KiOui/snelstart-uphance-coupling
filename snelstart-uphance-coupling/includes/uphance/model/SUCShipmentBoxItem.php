<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/model/SUCJsonImportExport.php';
include_once SUC_ABSPATH . 'includes/uphance/model/SUCShipmentBoxItemLineQuantity.php';

class SUCShipmentBoxItem extends SUCJsonImportExport {

	public int $id;
	public int $quantity;
	public int $shipment_box_id;
	public int $shipment_line_quantity_id;
	public SUCShipmentBoxItemLineQuantity $shipment_line_quantity;

	function __construct( int $id, int $quantity, int $shipment_box_id, int $shipment_line_quantity_id, SUCShipmentBoxItemLineQuantity $shipment_line_quantity ) {
		$this->id = $id;
		$this->quantity = $quantity;
		$this->shipment_box_id = $shipment_box_id;
		$this->shipment_line_quantity_id = $shipment_line_quantity_id;
		$this->shipment_line_quantity = $shipment_line_quantity;
	}
}
