<?php
/**
 * Text Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( "TextField" ) ) {
	/**
	 * Text Field for Settings.
	 *
	 * @class IntField
	 */
	class TextField extends SettingsField {

		public function __construct( string $id, string $name, ?callable $renderer, ?int $default, bool $can_be_null = false, string $hint = "" ) {
			parent::__construct( $id, $name, $renderer, $default, $can_be_null, $hint );
		}

        public static function validate_string( mixed $to_validate, ?string $default ): string {
	        if ( !isset( $to_validate ) && isset( $default ) ) {
                return $default;
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