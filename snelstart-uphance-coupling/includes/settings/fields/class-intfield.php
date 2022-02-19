<?php
/**
 * Integer Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( "IntField" ) ) {
	/**
	 * Integer Field for Settings.
	 *
	 * @class IntField
	 */
	class IntField extends SettingsField {

		private ?int $minimum;
		private ?int $maximum;

		public function __construct( string $id, string $name, ?callable $renderer, ?int $default, bool $can_be_null = false, string $hint = "", ?int $minimum = null, ?int $maximum = null ) {
            if ( isset( $minimum ) && isset( $maximum ) && $maximum <= $minimum ) {
                throw new SettingsConfigurationException( "Minimum must be smaller than maximum." );
            }

			parent::__construct( $id, $name, $renderer, $default, $can_be_null, $hint );
			$this->minimum = $minimum;
			$this->maximum = $maximum;
		}

		public function has_minimum(): bool {
			return isset( $this->minimum );
		}

		public function has_maximum(): bool {
			return isset( $this->maximum );
		}

		public function get_minimum(): int {
			return $this->minimum;
		}

		public function get_maximum(): int {
			return $this->maximum;
		}

        public static function validate_int( mixed $to_validate, ?int $default, bool $can_be_null, ?int $minimum, ?int $maximum ) {
	        // Check for null
	        if ( $can_be_null ) {
		        if ( $to_validate === "" ) {
			        return null;
		        }
	        } else if ( isset( $default ) && self::is_empty_setting( $to_validate ) ) {
		        return $default;
	        }

	        // Check for minimum and maximum
	        $int_value = intval( $to_validate );
	        if ( isset( $minimum ) && $int_value < $minimum ) {
		        return $minimum;
	        } else if ( isset( $maximum ) && $int_value > $maximum ) {
		        return $maximum;
	        } else {
		        return $int_value;
	        }
        }

		public function get_value( array $options ): ?int {
			$parent_value = parent::get_value( $options );
            return IntField::validate_int( $parent_value, $this->default, $this->can_be_null, $this->minimum, $this->maximum );
		}

		public function render( string $setting_name, array $options ): void {
			$value        = $this->get_value( $options );
			$setting_id   = $this->get_setting_name( $setting_name ); ?>
            <label><?php echo $this->rendered_hint() ?>
                <input type="number" name="<?php echo esc_attr( $setting_id ) ?>"
                       value="<?php echo esc_attr( $value ); ?>"
                    <?php if ( $this->has_minimum() ): ?>
                        min="<?php echo esc_attr( $this->minimum ) ?>"
                    <?php endif; ?>
                    <?php if ( $this->has_maximum() ): ?>
                        max="<?php echo esc_attr( $this->maximum ) ?>"
                    <?php endif; ?>
                    <?php if ( !$this->can_be_null ): ?>
                        required
                    <?php endif; ?>
                />
            </label>
			<?php
		}

		public function validate( mixed $value_to_validate ): ?int {
			return IntField::validate_int( $value_to_validate, $this->default, $this->can_be_null, $this->minimum, $this->maximum );
		}

		/**
		 * @throws SettingsConfigurationException
		 */
		public static function from_array( array $initial_values ): IntField {
	        return new self(
		        $initial_values['id'],
		        $initial_values['name'],
                $initial_values['renderer'],
                $initial_values['default'],
                $initial_values['can_be_null'],
                $initial_values['hint'],
                $initial_values['minimum'],
                $initial_values['maximum'],
            );
        }
	}
}