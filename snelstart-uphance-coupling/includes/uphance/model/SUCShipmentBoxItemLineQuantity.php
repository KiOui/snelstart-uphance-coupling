<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/model/SUCJsonImportExport.php';

class SUCShipmentBoxItemLineQuantity extends SUCJsonImportExport {


	public int $id;
	public string $size;
	public int $quantity;
	public string $sku_number;
	public int $sku_id;
	public int $inventory_id;
	public string $upc_number;
	public array $bundle_components;
	public string $warehouse_location;

	public string $box;

	function __construct( int $id, string $size, int $quantity, string $sku_number, int $sku_id, int $inventory_id, string $upc_number, array $bundle_components, string $warehouse_location, string $box ) {
		$this->id = $id;
		$this->size = $size;
		$this->quantity = $quantity;
		$this->sku_number = $sku_number;
		$this->sku_id = $sku_id;
		$this->inventory_id = $inventory_id;
		$this->upc_number = $upc_number;
		$this->bundle_components = $bundle_components;
		$this->warehouse_location = $warehouse_location;
		$this->box = $box;
	}
}
