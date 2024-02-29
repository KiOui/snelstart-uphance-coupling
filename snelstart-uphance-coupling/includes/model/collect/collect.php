<?php

if ( ! class_exists( "SUCJsonDecodeError") ) {
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
         * @param string      $message the message.
         */
        public function __construct( string $message ) {
            parent::__construct( $message );
        }
    }
}


if ( ! function_exists( "suc_get_string_from_json") ) {
    /**
     * Get a string from a JSON object.
     *
     * @param array $json The JSON object.
     * @param string $key The key to get from the JSON object (this function checks if this is a string type).
     * @param bool $raise_error Whether to raise an error if the key is not found. If this is false, a default will
     * be returned.
     * @param string|null $default The default to return if an error should not be raised on failure to find the key
     * in the JSON object.
     *
     * @return string|null The data at the key in the JSON object. Will return the default parameter if raise_error is
     * set to false and the key is not found in the JSON object.
     *
     * @throws SUCJsonDecodeError When the key was not found in the JSON object and raise_error is set to true.
     */
    function suc_get_string_from_json( array $json, string $key, bool $raise_error = true, ?string $default = null ): ?string {
        if ( array_key_exists( $key, $json ) ) {
            return strval( $json[$key] );
        } else {
            if ( $raise_error ) {
                throw new SUCJsonDecodeError("Key '$key' not found in JSON object '" . json_encode( $json ) . "'.");
            } else {
                return $default;
            }
        }
    }
}

if ( ! function_exists( "suc_get_int_from_json") ) {
    /**
     * Get an integer from a JSON object.
     *
     * @param array $json The JSON object.
     * @param string $key The key to get from the JSON object (this function checks if this is an integer type).
     * @param bool $raise_error Whether to raise an error if the key is not found. If this is false, a default will
     * be returned.
     * @param string|null $default The default to return if an error should not be raised on failure to find the key
     * in the JSON object.
     *
     * @return string|null The data at the key in the JSON object. Will return the default parameter if raise_error is
     * set to false and the key is not found in the JSON object.
     *
     * @throws SUCJsonDecodeError When the key was not found in the JSON object and raise_error is set to true. Also
     * when the value was found in the JSON object, but it could not be converted to a number.
     */
    function suc_get_int_from_json( array $json, string $key, bool $raise_error = true, ?int $default = null ): ?int {
        if ( array_key_exists( $key, $json ) ) {
            $value = $json[$key];
            if ( is_numeric( $value ) ) {
                return intval( $value );
            } else {
                throw new SUCJsonDecodeError("Value '$value' is not a numeric, encountered '" . json_encode( $value ) . "'.");
            }
        } else {
            if ( $raise_error ) {
                throw new SUCJsonDecodeError("Key '$key' not found in JSON object '" . json_encode( $json ) . "'.");
            } else {
                return $default;
            }
        }
    }
}

if ( ! function_exists( "suc_get_array_from_json") ) {
    /**
     * Get an array from a JSON object.
     *
     * @param array $json The JSON object.
     * @param string $key The key to get from the JSON object (this function checks if this is an array type).
     * @param bool $raise_error Whether to raise an error if the key is not found. If this is false, a default will
     * be returned.
     * @param string|null $default The default to return if an error should not be raised on failure to find the key
     * in the JSON object.
     *
     * @return string|null The data at the key in the JSON object. Will return the default parameter if raise_error is
     * set to false and the key is not found in the JSON object.
     *
     * @throws SUCJsonDecodeError When the key was not found in the JSON object and raise_error is set to true. Also
     * when the value was found in the JSON object, but it is not an array.
     */
    function suc_get_array_from_json( array $json, string $key, bool $raise_error = true, ?array $default = null ): ?array {
        if ( array_key_exists( $key, $json ) ) {
            $value = $json[$key];
            if ( is_array( $value ) ) {
                return $value;
            } else {
                throw new SUCJsonDecodeError("Value '$value' is not an array, encountered '" . json_encode( $value ) . "'.");
            }
        } else {
            if ( $raise_error ) {
                throw new SUCJsonDecodeError("Key '$key' not found in JSON object '" . json_encode( $json ) . "'.");
            } else {
                return $default;
            }
        }
    }
}

if ( ! function_exists( "suc_get_bool_from_json") ) {
    /**
     * Get a boolean from a JSON object.
     *
     * @param array $json The JSON object.
     * @param string $key The key to get from the JSON object (this function checks if this is a boolean type).
     * @param bool $raise_error Whether to raise an error if the key is not found. If this is false, a default will
     * be returned.
     * @param string|null $default The default to return if an error should not be raised on failure to find the key
     * in the JSON object.
     *
     * @return string|null The data at the key in the JSON object. Will return the default parameter if raise_error is
     * set to false and the key is not found in the JSON object.
     *
     * @throws SUCJsonDecodeError When the key was not found in the JSON object and raise_error is set to true. Also
     *  when the value was found in the JSON object, but it is not a boolean.
     */
    function suc_get_bool_from_json( array $json, string $key, bool $raise_error = true, ?bool $default = null ): ?bool {
        if ( array_key_exists( $key, $json ) ) {
            $value = $json[$key];
            if ( is_bool( $value ) ) {
                return $value;
            } else {
                throw new SUCJsonDecodeError("Value '$value' is not a boolean, encountered '" . json_encode( $value ) . "'.");
            }
        } else {
            if ( $raise_error ) {
                throw new SUCJsonDecodeError("Key '$key' not found in JSON object '" . json_encode( $json ) . "'.");
            } else {
                return $default;
            }
        }
    }
}