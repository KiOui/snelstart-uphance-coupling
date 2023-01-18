<?php
/**
 * Boolean Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/fields/class-choicefield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( 'BoolField' ) ) {
	/**
	 * Boolean Field for Settings.
	 *
	 * @class IntField
	 */
	class BoolField extends ChoiceField {

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
		public function __construct( string $id, string $name, ?bool $default, ?callable $renderer = null, string $hint = '', ?array $conditions = null ) {
            if ( is_null( $conditions ) ) {
                $conditions = array();
            }

            if ( ! is_null( $default ) ) {
                if ( $default === true ) {
                    $default = 'on';
                } else {
                    $default = 'off';
                }
            }

			parent::__construct( $id, $name, [
                    'on' => 'Yes',
                    'off' => 'No',
            ], $default, $renderer, false, $hint, $conditions );
		}

		public function serialize(): ?string {
			if ( $this->value === 'on' ) {
				return 'true';
			} else {
				return 'false';
			}
		}

		public function sanitize( $value_to_sanitize ): ?string {
			if ( is_null( $value_to_sanitize ) || $value_to_sanitize === '' ) {
				return 'off';
			}
			if ( is_bool( $value_to_sanitize ) ) {
				if ( $value_to_sanitize === true ) {
					return 'on';
				} else {
					return 'off';
				}
			}
			return strval( $value_to_sanitize );
		}

		public function set_value( $value ): bool {
			$sanitized_value = $this->sanitize( $value );
			if ( $this->validate( $sanitized_value ) ) {
				$this->value = $sanitized_value;
				return true;
			} else {
                return false;
			}
		}

		public function set_value_force( $value ) {
			if ( is_bool( $value ) ) {
				if ( $value === true ) {
					$this->value = 'on';
				} else {
					$this->value = 'off';
				}
			} else {
				$this->value = $value;
			}
		}

		public function get_value(): bool {
			return $this->value === 'on';
		}

		/**
		 * Render this ChoiceField.
		 *
		 * @return void
		 */
		public function render( array $args ): void {
			$current_value    = $this->get_value(); ?>
			<label for="<?php echo esc_attr( $this->id ); ?>"><?php echo esc_html( $this->rendered_hint() ); ?></label>
			<select name="<?php echo esc_attr( $this->id ); ?>">
				<option
					<?php if ( $current_value === true ) : ?>
						selected
					<?php endif; ?>
					value="on">Yes</option>
				<option
					<?php if ( $current_value === false ) : ?>
						selected
					<?php endif; ?>
					value="off">No</option>
			</select>
			<?php
		}

		public function deserialize( ?string $serialized_value ): string {
			if ( $serialized_value === 'true' ) {
				return 'on';
			} else {
				return 'off';
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
