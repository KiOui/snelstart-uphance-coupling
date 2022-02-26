<?php
/**
 * Choice Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsfield.php';
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
		 * @param callable|null  $renderer the custom renderer of the SettingsField.
		 * @param callable|array $choices either a callable or an array of choice values.
		 * @param ?string        $default the default value of the setting.
		 * @param bool           $can_be_null whether the setting can be null.
		 * @param string         $hint the hint to display next to the setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false or when the choices
		 * array is not a string => string array.
		 */
		public function __construct( string $id, string $name, ?callable $renderer, $choices, ?string $default, bool $can_be_null = false, string $hint = '' ) {
			if ( ! is_callable( $choices ) ) {
				foreach ( $choices as $key => $value ) {
					if ( gettype( $key ) !== 'string' || gettype( $value ) !== 'string' ) {
						throw new SettingsConfigurationException( 'Choices array should have type string => string.' );
					}
				}

				if ( isset( $default ) && ! array_key_exists( $default, $choices ) ) {
					throw new SettingsConfigurationException( 'Default choice value is not present in choices array.' );
				}
				parent::__construct( $id, $name, $renderer, $default, $can_be_null, $hint );
				$this->choices = $choices;
			} else {
				parent::__construct( $id, $name, $renderer, $default, $can_be_null, $hint );
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
		 * Validate a choice value.
		 *
		 * @param mixed       $to_validate the value to validate.
		 * @param array       $choices the choices the value can have.
		 * @param string|null $default the default value.
		 * @param bool        $can_be_null whether the value to validate can be null.
		 *
		 * @return string|null the validated value.
		 */
		public static function validate_choice( $to_validate, array $choices, ?string $default, bool $can_be_null ): ?string {
			if ( ! isset( $to_validate ) && isset( $default ) ) {
				return $default;
			}

			$string_value = strval( $to_validate );
			if ( empty( $string_value ) && $can_be_null ) {
				return null;
			} else if ( ! array_key_exists( $string_value, $choices ) && $can_be_null ) {
				return null;
			} else if ( array_key_exists( $string_value, $choices ) ) {
				return $string_value;
			} else {
				return '';
			}
		}

		/**
		 * Get the value of this setting from an array of options.
		 *
		 * @param array $options the array of options.
		 *
		 * @return string|null a validated string value.
		 * @throws ReflectionException When the choices_callable function was not callable.
		 */
		public function get_value( array $options ): ?string {
			$parent_value = parent::get_value( $options );
			return self::validate_choice( $parent_value, $this->get_choices(), $this->default, $this->can_be_null );
		}

		/**
		 * Render this ChoiceField.
		 *
		 * @param string $setting_name the name of the setting to render this ChoiceField for.
		 * @param array  $options the array of options.
		 *
		 * @return void
		 * @throws ReflectionException When the choices_callable function was not callable.
		 */
		public function render( string $setting_name, array $options ): void {
			$current_value        = $this->get_value( $options );
			$setting_id   = $this->get_setting_name( $setting_name );
			$setting_selected = false;
			$choices = $this->get_choices() ?>
			<label><?php echo esc_html( $this->rendered_hint() ); ?>
				<select name="<?php echo esc_attr( $setting_id ); ?>">
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

		/**
		 * Validate the value for this setting.
		 *
		 * @param mixed $value_to_validate the value to validate.
		 *
		 * @return ?string a validated boolean value.
		 * @throws ReflectionException When the choices_callable function could not be called.
		 */
		public function validate( $value_to_validate ): ?string {
			return self::validate_choice( $value_to_validate, $this->get_choices(), $this->default, $this->can_be_null );
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
				$initial_values['renderer'],
				$initial_values['choices'],
				$initial_values['default'],
				$initial_values['can_be_null'],
				$initial_values['hint'],
			);
		}
	}
}
