<?php
/**
 * Text Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/fields/class-settingsfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( 'TextField' ) ) {
	/**
	 * Text Field for Settings.
	 *
	 * @class TextField
	 */
	class TextField extends SettingsField {

		/**
		 * Constructor of SettingsField.
		 *
		 * @param string        $id the slug-like ID of the setting.
		 * @param string        $name the name of the setting.
		 * @param string|null   $default the default value of the setting.
		 * @param bool          $can_be_null whether the setting can be null.
		 * @param string        $hint the hint to display next to the setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false.
		 */
		public function __construct( string $id, string $name, ?string $default, ?callable $renderer = null, bool $can_be_null = false, string $hint = '', ?array $conditions = null ) {
            if ( is_null( $conditions ) ) {
                $conditions = array();
            }

			parent::__construct( $id, $name, $default, $renderer, $can_be_null, $hint, $conditions );
		}

        public function validate( $value_to_validate ): bool {
	        if ( ! is_null( $value_to_validate ) && ! is_string( $value_to_validate ) ) {
                return false;
            }

            if ( is_null( $value_to_validate ) ) {
                return $this->can_be_null;
            }

            return true;
        }

        public function sanitize( $value_to_sanitize ): ?string {
	        if ( is_null( $value_to_sanitize ) ) {
                return null;
            }

            return strval( $value_to_sanitize );
        }

		/**
		 * Render this TextField.
		 *
		 * @param string $setting_name the name of the setting to render this TextField for.
		 *
		 * @return void
		 */
		public function render( array $args ): void {
			$value        = $this->get_value(); ?>
			<label><?php echo esc_html( $this->rendered_hint() ); ?>
				<input type="text" name="<?php echo esc_attr( $this->id ); ?>"
				       value="<?php echo esc_attr( $value ); ?>"
					<?php if ( ! $this->can_be_null ) : ?>
						required
					<?php endif; ?>
				/>
			</label>
			<?php
		}

		public function serialize(): ?string {
			return $this->value;
		}

		public function deserialize( ?string $serialized_value ): ?string {
			return $serialized_value;
		}

		/**
		 * Create a TextField from an array of values.
		 *
		 * @param array $initial_values values to pass to TextField constructor.
		 *
		 * @return TextField the created TextField.
		 * @throws SettingsConfigurationException When TextField creation failed.
		 */
		public static function from_array( array $initial_values ): self {
			return new self(
				$initial_values['id'],
				$initial_values['name'],
				isset( $initial_values['default'] ) ? $initial_values['default'] : null,
				isset( $initial_values['renderer'] ) ? $initial_values['renderer'] : null,
				isset( $initial_values['can_be_null'] ) ? $initial_values['can_be_null'] : false,
				isset( $initial_values['hint'] ) ? $initial_values['hint'] : '',
                isset( $initial_values['conditions'] ) ? $initial_values['conditions'] : null
			);
		}
	}
}
