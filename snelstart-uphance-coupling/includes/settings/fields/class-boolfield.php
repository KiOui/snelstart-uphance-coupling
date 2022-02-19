<?php
/**
 * Boolean Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( "BoolField" ) ) {
	/**
	 * Boolean Field for Settings.
	 *
	 * @class IntField
	 */
	class BoolField extends SettingsField {

		public function __construct( string $id, string $name, ?callable $renderer, bool $default, string $hint = "" ) {
			parent::__construct( $id, $name, $renderer, $default, false, $hint );
		}

        public static function validate_bool( mixed $to_validate, bool $default ): bool {
            if ( ! isset( $to_validate ) ) {
                return $default;
            } else {
                return boolval( $to_validate );
            }
        }

		public function get_value( array $options ): ?int {
			$parent_value = parent::get_value( $options );
            return BoolField::validate_bool( $parent_value, $this->default );
		}

		public function render( string $setting_name, array $options ): void {
			$value        = $this->get_value( $options );
			$setting_id   = $this->get_setting_name( $setting_name ); ?>
            <label><?php echo $this->rendered_hint() ?>
                <input type="checkbox" name="<?php echo esc_attr( $setting_id ) ?>"
                    <?php checked( $value ); ?> />
            </label>
			<?php
		}

		public function validate( mixed $value_to_validate ): ?int {
			return BoolField::validate_bool( $value_to_validate, $this->default );
		}

		/**
		 * @throws SettingsConfigurationException
		 */
		public static function from_array( array $initial_values ): BoolField {
	        return new self(
		        $initial_values['id'],
		        $initial_values['name'],
                $initial_values['renderer'],
                $initial_values['default'],
                $initial_values['hint'],
            );
        }
	}
}