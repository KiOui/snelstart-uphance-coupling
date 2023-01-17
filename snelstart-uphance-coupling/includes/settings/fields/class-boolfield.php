<?php
/**
 * Boolean Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/fields/class-settingsfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( 'BoolField' ) ) {
	/**
	 * Boolean Field for Settings.
	 *
	 * @class IntField
	 */
	class BoolField extends SettingsField {

		/**
		 * Constructor of BoolField.
		 *
		 * @param string        $id the slug-like ID of the setting.
		 * @param string        $name the name of the setting.
		 * @param mixed         $default the default value of the setting.
		 * @param string        $hint the hint to display next to the setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false.
		 */
		public function __construct( string $id, string $name, $default, ?callable $renderer = null, string $hint = '', ?array $conditions = null ) {
            if ( is_null( $conditions ) ) {
                $conditions = array();
            }

			parent::__construct( $id, $name, $default, $renderer, false, $hint, $conditions );
		}

		/**
		 * Render this BoolField.
		 *
		 * @return void
		 */
		public function render( array $args ): void {
			$value        = $this->get_value(); ?>
			<label><?php echo esc_html( $this->rendered_hint() ); ?>
				<input type="checkbox" name="<?php echo esc_attr( $this->id ); ?>"
					<?php checked( $value ); ?> />
			</label>
			<?php
		}

		public function sanitize( $value_to_sanitize ) {
            if ( is_null( $value_to_sanitize ) || $value_to_sanitize === '' ) {
                return null;
            }
            return boolval( $value_to_sanitize );
        }

        public function validate( $value_to_validate ): bool {
            if ( ! is_null( $value_to_validate ) && ! is_bool( $value_to_validate ) ) {
                return false;
            }

	        if ( is_null( $value_to_validate ) ) {
                return $this->can_be_null;
            }

            return true;
        }

		public function serialize(): ?string {
			if ( $this->value ) {
				return 'true';
			} else {
				return 'false';
			}
		}

		public function deserialize( ?string $serialized_value ): bool {
			if ( $serialized_value === 'true' ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Create a BoolField from an array of values.
		 *
		 * @param array $initial_values values to pass to BoolField constructor.
		 *
		 * @return BoolField the created BoolField.
		 * @throws SettingsConfigurationException When BoolField creation failed.
		 */
		public static function from_array( array $initial_values ): BoolField {
			return new self(
				$initial_values['id'],
				$initial_values['name'],
                isset( $initial_values['default'] ) ? $initial_values['default'] : null,
				isset( $initial_values['renderer'] ) ? $initial_values['renderer'] : null,
				isset( $initial_values['hint'] ) ? $initial_values['hint'] : '',
				isset( $initial_values['conditions'] ) ? $initial_values['conditions'] : null,
			);
		}
	}
}
