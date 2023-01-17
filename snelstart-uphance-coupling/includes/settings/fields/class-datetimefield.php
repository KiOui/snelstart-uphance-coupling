<?php
/**
 * DateTime Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/fields/class-settingsfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( 'DateTimeField' ) ) {
	/**
	 * DateTime Field for Settings.
	 *
	 * @class DateTimeField
	 */
	class DateTimeField extends SettingsField {

		/**
		 * Constructor of DateTimeField.
		 *
		 * @param string        $id the slug-like ID of the setting.
		 * @param string        $name the name of the setting.
		 * @param ?DateTime     $default the default value of the setting.
		 * @param bool          $can_be_null whether the setting can be null.
		 * @param string        $hint the hint to display next to the setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false.
		 */
		public function __construct( string $id, string $name, ?DateTime $default, ?callable $renderer = null, bool $can_be_null = false, string $hint = '', ?array $conditions = null ) {
            if ( is_null( $conditions ) ) {
                $conditions = array();
            }

			parent::__construct( $id, $name, $default, $renderer, $can_be_null, $hint, $conditions );
		}

        public function sanitize( $value_to_sanitize ): ?DateTime {
	        if ( $value_to_sanitize === '' || ! is_string( $value_to_sanitize ) ) {
                return null;
            }

	        try {
		        return new DateTime( $value_to_sanitize );
	        } catch ( Exception $e ) {
		        return null;
	        }
        }

		public function validate( $value_to_validate ): bool {
            if ( ! is_null( $value_to_validate ) && get_class( $value_to_validate ) !== 'DateTime' ) {
                return false;
            }

            if ( is_null( $value_to_validate ) ) {
                return $this->can_be_null;
            }

            return true;
        }

		/**
		 * Render this DateTimeField.
		 *
		 * @param string $setting_name the name of the setting to render this DateTimeField for.
		 *
		 * @return void
		 */
		public function render( array $args ): void {
			$value        = $this->get_value(); ?>
			<label><?php echo esc_html( $this->rendered_hint() ); ?>
				<input type="datetime-local" name="<?php echo esc_attr( $this->id ); ?>"
				       value="<?php echo esc_attr( $value->format( 'Y-m-d\TH:i' ) ); ?>"
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
			} else {
				return $this->value->format( 'Y-m-d\TH:i:sP' );
			}
		}

		public function deserialize( ?string $serialized_value ): ?DateTime {
			if ( is_null( $serialized_value ) ) {
				return null;
			}

			try {
				return new DateTime( $serialized_value );
			} catch ( Exception $e ) {
				return null;
			}
		}

		/**
		 * Create a DateTimeField from an array of values.
		 *
		 * @param array $initial_values values to pass to DateTimeField constructor.
		 *
		 * @return DateTimeField the created DateTimeField.
		 * @throws SettingsConfigurationException When DateTimeField creation failed.
		 */
		public static function from_array( array $initial_values ): self {
			return new self(
				$initial_values['id'],
				$initial_values['name'],
				isset( $initial_values['default'] ) ? $initial_values['default'] : null,
                isset( $initial_values['renderer'] ) ? $initial_values['renderer'] : null,
                isset( $initial_values['can_be_null'] ) ? $initial_values['can_be_null'] : false,
				isset( $initial_values['hint'] ) ? $initial_values['hint'] : '',
                isset( $initial_values['conditions' ] ) ? $initial_values['conditions'] : null,
			);
		}
	}
}
