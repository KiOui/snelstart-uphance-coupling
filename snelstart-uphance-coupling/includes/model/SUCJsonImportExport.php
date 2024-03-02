<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class SUCJsonImportExport {

	function to_json(): string {
		return json_encode( $this );
	}

	static function from_json( array $json ): self {
		$method_called_on_class = get_called_class();
		$object_properties = get_class_vars( $method_called_on_class );
		$parameters = array();
		foreach ( $object_properties as $property_name => $_nothing ) {
			$reflection_property = new ReflectionProperty( $method_called_on_class, $property_name );

			if ( is_null( $reflection_property->getType() ) ) {
				throw new SUCJsonDecodeError( "No type found for property $property_name in class $method_called_on_class." );
			}

			$nullable = $reflection_property->getType()->allowsNull();
			$default_value = $reflection_property->getDefaultValue();
			$type = $reflection_property->getType()->getName();

			$parameters[ $property_name ] = suc_get_value_from_key( $json, $reflection_property, $type, $nullable, $default_value );
		}
		return new $method_called_on_class( ...$parameters );
	}
}
