<?php
/**
 * Integer Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/fields/class-settingsfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( 'IntField' ) ) {
	/**
	 * Integer Field for Settings.
	 *
	 * @class IntField
	 */
	class IntField extends SettingsField {

		/**
		 * Minimum value for this IntField.
		 *
		 * @var int|null
		 */
		private ?int $minimum;

		/**
		 * Maximum value for this IntField.
		 *
		 * @var int|null
		 */
		private ?int $maximum;

		/**
		 * Constructor of IntField.
		 *
		 * @param string        $id the slug-like ID of the setting.
		 * @param string        $name the name of the setting.
		 * @param ?int          $default the default value of the setting.
		 * @param bool          $can_be_null whether the setting can be null.
		 * @param string        $hint the hint to display next to the setting.
		 * @param int|null      $minimum the minimum value for the IntField, when null no minimum value is specified.
		 * @param int|null      $maximum the maximum value for the IntField, when null no maximum value is specified.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false or when $minimum is
		 * larger than $maximum.
		 */
		public function __construct( string $id, string $name, ?int $default, ?callable $renderer = null, bool $can_be_null = false, string $hint = '', ?int $minimum = null, ?int $maximum = null, ?array $conditions = null ) {
			if ( isset( $minimum ) && isset( $maximum ) && $maximum <= $minimum ) {
				throw new SettingsConfigurationException( 'Minimum must be smaller than maximum.' );
			}

            if ( is_null( $conditions ) ) {
                $conditions = array();
            }

			parent::__construct( $id, $name, $default, $renderer, $can_be_null, $hint, $conditions );
			$this->minimum = $minimum;
			$this->maximum = $maximum;
		}

		/**
		 * Check whether this IntField has a minimum value registered.
		 *
		 * @return bool true when this IntField has a minimum value.
		 */
		public function has_minimum(): bool {
			return isset( $this->minimum );
		}

		/**
		 * Check whether this IntField has a maximum value registered.
		 *
		 * @return bool true when this IntField has a maximum value.
		 */
		public function has_maximum(): bool {
			return isset( $this->maximum );
		}

		/**
		 * Get the minimum value.
		 *
		 * @return int|null the minimum value.
		 */
		public function get_minimum(): ?int {
			return $this->minimum;
		}

		/**
		 * Get the maximum value.
		 *
		 * @return int|null the maximum value.
		 */
		public function get_maximum(): ?int {
			return $this->maximum;
		}

		public function sanitize( $value_to_sanitize ): ?int {
			if ( $value_to_sanitize === '' || is_null( $value_to_sanitize ) ) {
                return null;
            } else {
                return intval( $value_to_sanitize );
            }
		}

        public function validate( $value_to_validate ): bool {
            if ( ! is_null( $value_to_validate ) && ! is_int( $value_to_validate ) ) {
                return false;
            }

            if ( is_null( $value_to_validate ) ) {
                return $this->can_be_null;
            }

	        if ( isset( $minimum ) && $value_to_validate < $minimum ) {
		        return false;
	        } else if ( isset( $maximum ) && $value_to_validate > $maximum ) {
		        return false;
	        } else {
		        return true;
	        }
        }

		/**
		 * Render this IntField.
		 *
		 * @return void
		 */
		public function render( array $args ): void {
			$value        = $this->get_value(); ?>
			<label><?php echo esc_html( $this->rendered_hint() ); ?>
				<input type="number" name="<?php echo esc_attr( $this->id ); ?>"
				       value="<?php echo esc_attr( $value ); ?>"
					<?php if ( $this->has_minimum() ) : ?>
						min="<?php echo esc_attr( $this->minimum ); ?>"
					<?php endif; ?>
					<?php if ( $this->has_maximum() ) : ?>
						max="<?php echo esc_attr( $this->maximum ); ?>"
					<?php endif; ?>
					<?php if ( ! $this->can_be_null ) : ?>
						required
					<?php endif; ?>
				/>
			</label>
			<?php
		}

		public function serialize(): ?string {
			if ( is_null( $this->value ) ) {
				return null;
			}

			return strval( $this->value );
		}

		public function deserialize( ?string $serialized_value ) {
			if ( is_null( $serialized_value ) ) {
				return null;
			}

			return intval( $serialized_value );
		}

		/**
		 * Create an IntField from an array of values.
		 *
		 * @param array $initial_values values to pass to IntField constructor.
		 *
		 * @return IntField the created IntField.
		 * @throws SettingsConfigurationException When IntField creation failed.
		 */
		public static function from_array( array $initial_values ): IntField {
			return new self(
				$initial_values['id'],
				$initial_values['name'],
				isset( $initial_values['default'] ) ? $initial_values['default'] : null,
                isset( $initial_values['renderer'] ) ? $initial_values[ 'renderer' ] : null,
				isset( $initial_values['can_be_null'] ) ? $initial_values['can_be_null'] : false,
				isset( $initial_values['hint'] ) ? $initial_values['hint'] : '',
				isset( $initial_values['minimum'] ) ? $initial_values['minimum'] : null,
				isset( $initial_values['maximum'] ) ? $initial_values['maximum'] : null,
                isset( $initial_values['conditions'] ) ? $initial_values['conditions'] : null,
			);
		}
	}
}
