<?php

class SUCShipmentBoxItemLineQuantity
{

    private int $id;
    private string $size;
    private int $quantity;
    private string $sku_number;
    private int $sku_id;
    private int $inventory_id;
    private string $upc_number;
    private array $bundle_components;
    private string $warehouse_location;
    private string $box;

    function __construct( int $id, string $size, int $quantity, string $sku_number, int $sku_id, int $inventory_id, string $upc_number, array $bundle_components, string $warehouse_location, string $box) {
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

    function to_json(): string {
        return json_encode( $this );
    }

    abstract function from_json(mixed $json): SUCShipmentBoxItemLineQuantity {
        return new SUCShipmentBoxItemLineQuantity(

        );
    }
}