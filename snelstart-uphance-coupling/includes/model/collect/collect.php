<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SUCJsonDecodeError' ) ) {
	class SUCJsonDecodeError extends Exception {
		/**
		 * Reason for Exception.
		 *
		 * @var string|null
		 */
		public ?string $reason;

		/**
		 * Headers for Exception.
		 *
		 * @var array
		 */
		public array $headers;

		/**
		 * Constructor.
		 *
		 * @param string $message the message.
		 */
		public function __construct( string $message ) {
			parent::__construct( $message );
		}
	}
}

if ( ! function_exists( 'suc_get_value_from_key' ) ) {
	function suc_get_value_from_key( array $json, ReflectionProperty $reflection_property, string $type, bool $nullable = false, mixed $default = null ): mixed {
		$key = $reflection_property->getName();

		if ( array_key_exists( $key, $json ) ) {
			$value = $json[ $key ];

			if ( is_null( $value ) ) {
				if ( $nullable ) {
					return null;
				} else {
					throw new SUCJsonDecodeError( "Key '$key' is nullable and was found in JSON object but is set to null: '" . json_encode( $json ) . "'." );
				}
			}

			if ( 'string' === $type ) {
				return strval( $value );
			} else if ( 'int' === $type ) {
				if ( is_numeric( $value ) ) {
					return intval( $value );
				} else {
					throw new SUCJsonDecodeError( "Key '$key' is not a numeric, encountered '" . json_encode( $value ) . "'." );
				}
			} else if ( 'bool' === $type ) {
				if ( is_bool( $value ) ) {
					return $value;
				} else {
					throw new SUCJsonDecodeError( "Key '$key' is not a boolean, encountered '" . json_encode( $value ) . "'." );
				}
			} else if ( 'array' === $type ) {
				if ( ! is_array( $value ) ) {
					throw new SUCJsonDecodeError( "Key '$key' is not an array, encountered '" . json_encode( $value ) . "'." );
				}

				// This might be a specific type of array.
				$doc_comment = $reflection_property->getDocComment();
				if ( false === $doc_comment ) {
					// No doc comment so any array is fine.
					return $value;
				}

				// Doc comment, so we should extract the class name from it.
				$array_class_name = suc_extract_value_from_doc_comment( $doc_comment );
				if ( is_null( $array_class_name ) ) {
					// No specific class name for the array.
					return $value;
				}

				if ( ! class_exists( $array_class_name ) ) {
					throw new SUCJsonDecodeError( "Found class '$array_class_name' in doc comment for key '$key' but this class does not exist." );
				}

				if ( ! is_subclass_of( $array_class_name, 'SUCJsonImportExport' ) ) {
					throw new SUCJsonDecodeError( "Tried to run 'from_json' method for key '$key' on class '$array_class_name' but this class does not have SUCJsonImportExport as superclass so the method might not exist." );
				}

				$reflection_method = new ReflectionMethod( $array_class_name, 'from_json' );
				$return_value = array();

				foreach ( $value as $one_value ) {
					$return_value[] = $reflection_method->invoke( null, $one_value );
				}

				return $return_value;
			} else if ( class_exists( $type ) ) {
				if ( is_subclass_of( $type, 'SUCJsonImportExport' ) ) {
					$reflection_method = new ReflectionMethod( $type, 'from_json' );
					return $reflection_method->invoke( null, $value );
				} else {
					throw new SUCJsonDecodeError( "Tried to run 'from_json' method for key '$key' on class '$type' but this class does not have SUCJsonImportExport as superclass so the method might not exist." );
				}
			} else {
				throw new SUCJsonDecodeError( "Encountered unknown type for key '$key' and type '$type'." );
			}
		} elseif ( is_null( $default ) && ! $nullable ) {
				throw new SUCJsonDecodeError( "Key '$key' was not found in JSON object and no default was specified: '" . json_encode( $json ) . "'." );
		} else {
			return $default;
		}
	}
}

if ( ! function_exists( 'suc_extract_value_from_doc_comment' ) ) {
	function suc_extract_value_from_doc_comment( string $doc_comment ): ?string {
		$re = '/@var (?<class_name>\S*)\[\]( .*)*/';
		$matches = null;
		$match_amount = preg_match( $re, $doc_comment, $matches, PREG_UNMATCHED_AS_NULL );
		if ( 0 !== $match_amount ) {
			return $matches['class_name'];
		} else {
			return null;
		}
	}
}
