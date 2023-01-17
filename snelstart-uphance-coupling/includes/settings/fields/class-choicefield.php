<?php
/**
 * Choice Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/fields/class-settingsfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( 'ChoiceField' ) ) {
	/**
	 * Choice Field for Settings.
	 *
	 * @class ChoiceField
	 */
	class ChoiceField extends SettingsField {

		/**
		 * A callable to get the choice values.
		 *
		 * @var array|callable|null
		 */
		private $choices_callable = null;

		/**
		 * An array of choice values.
		 *
		 * @var array|null
		 */
		private ?array $choices;

		/**
		 * Constructor of ChoiceField.
		 *
		 * @param string         $id the slug-like ID of the setting.
		 * @param string         $name the name of the setting.
		 * @param callable|array $choices either a callable or an array of choice values.
		 * @param ?string        $default the default value of the setting.
		 * @param bool           $can_be_null whether the setting can be null.
		 * @param string         $hint the hint to display next to the setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false or when the choices
		 * array is not a string => string array.
		 */
		public function __construct( string $id, string $name, $choices, ?string $default, ?callable $renderer, bool $can_be_null = false, string $hint = '', ?array $conditions = null ) {
            if ( is_null( $conditions ) ) {
                $conditions = array();
            }

			if ( ! is_callable( $choices ) ) {
				foreach ( $choices as $key => $choice_value ) {
					if ( gettype( $key ) !== 'string' || gettype( $choice_value ) !== 'string' ) {
						throw new SettingsConfigurationException( 'Choices array should have type string => string.' );
					}
				}

				if ( isset( $default ) && ! array_key_exists( $default, $choices ) ) {
					throw new SettingsConfigurationException( 'Default choice value is not present in choices array.' );
				}
				parent::__construct( $id, $name, $default, $renderer, $can_be_null, $hint, $conditions );
				$this->choices = $choices;
			} else {
				parent::__construct( $id, $name, $default, $renderer, $can_be_null, $hint, $conditions );
				$this->choices = null;
				$this->choices_callable = $choices;
			}
		}

		/**
		 * Get the value of a choice for a specific choice id.
		 *
		 * @param string $choice_id the choice id to get the value for.
		 *
		 * @return string|null the value of the choice with the choice id or null when the choice id was not found.
		 * @throws ReflectionException When the choices_callable function could not be called.
		 */
		public function get_choice_value( string $choice_id ): ?string {
			$choices = $this->get_choices();
			if ( array_key_exists( $choice_id, $choices ) ) {
				return $choices[ $choice_id ];
			} else {
				return null;
			}
		}

		/**
		 * Get all choices.
		 *
		 * @return array the choices for this ChoiceField.
		 * @throws ReflectionException When the choices_callable function was not callable.
		 */
		public function get_choices(): array {
			if ( is_null( $this->choices ) ) {
				$this->choices = call_user_func( $this->choices_callable );
			}

			return $this->choices;
		}

		/**
		 * Render this ChoiceField.
		 *
		 * @return void
		 * @throws ReflectionException When the choices_callable function was not callable.
		 */
		public function render( array $args ): void {
			$current_value        = $this->get_value();
			$setting_selected = false;
			$choices = $this->get_choices() ?>
			<label><?php echo esc_html( $this->rendered_hint() ); ?>
				<select name="<?php echo esc_attr( $this->id ); ?>">
					<?php if ( $this->can_be_null ) : ?>
						<option value="">----------</option>
					<?php endif; ?>
					<?php foreach ( $choices as $key => $value ) : ?>
						<option
							<?php if ( $key == $current_value ) : ?>
								selected
								<?php $setting_selected = true; ?>
							<?php endif; ?>
							value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
					<?php if ( ! $setting_selected && isset( $current_value ) && '' !== $current_value ) : ?>
						<option selected value="<?php echo esc_attr( $current_value ); ?>">
							<?php echo esc_html( sprintf( __( 'Currently set value %s', 'snelstart-uphance-coupling' ), $current_value ) ); ?>
						</option>
					<?php endif; ?>
				</select>
			</label>
			<?php
		}

        public function sanitize( $value_to_sanitize ): ?string {
	        if ( is_null( $value_to_sanitize ) || $value_to_sanitize === '' ) {
                return null;
            }
            return strval( $value_to_sanitize );
        }

        public function validate( $value_to_validate ): bool {
	        if ( ! is_null( $value_to_validate ) && ! is_string( $value_to_validate ) ) {
                return false;
            }

            if ( is_null( $value_to_validate ) ) {
                return $this->can_be_null;
            }

            return array_key_exists( $value_to_validate, $this->get_choices() );
        }

		public function serialize(): ?string {
			return $this->value;
		}

		public function deserialize( ?string $serialized_value ): ?string {
			return $serialized_value;
		}

		/**
		 * Create a ChoiceField from an array of values.
		 *
		 * @param array $initial_values values to pass to ChoiceField constructor.
		 *
		 * @return ChoiceField the created ChoiceField.
		 * @throws SettingsConfigurationException When ChoiceField creation failed.
		 */
		public static function from_array( array $initial_values ): self {
			return new self(
				$initial_values['id'],
				$initial_values['name'],
				$initial_values['choices'],
				isset( $initial_values['default'] ) ? $initial_values['default'] : null,
                isset( $initial_values['renderer'] ) ? $initial_values['renderer'] : null,
				isset( $initial_values['can_be_null'] ) ? $initial_values['can_be_null'] : false,
				isset( $initial_values['hint'] ) ? $initial_values['hint'] : '',
                isset( $initial_values['conditions'] ) ? $initial_values['conditions'] : null,
			);
		}
	}
}
