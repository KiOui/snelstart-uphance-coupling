<?php
/**
 * Choice Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/fields/class-textfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( 'CallableChoiceField' ) ) {
	/**
	 * Choice Field for Settings.
	 *
	 * @class ChoiceField
	 */
	class CallableChoiceField extends TextField {

		/**
		 * A callable to get the choice values.
		 *
		 * @var callable
		 */
		private $choices_callable;

		/**
		 * An array of choice values.
		 *
		 * @var array|null|false
		 */
		private $choices = null;

		/**
		 * Constructor of ChoiceField.
		 *
		 * @param string    $id the slug-like ID of the setting.
		 * @param string    $name the name of the setting.
		 * @param callable  $choices_callable either a callable or an array of choice values.
		 * @param ?string   $default the default value of the setting.
		 * @param ?callable $renderer an optional default renderer for the setting.
		 * @param bool      $can_be_null whether the setting can be null.
		 * @param string    $hint the hint to display next to the setting.
		 * @param ?array    $conditions optional array of SettingsConditions that determine whether to display this setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false or when the choices
		 * array is not a string => string array.
		 */
		public function __construct( string $id, string $name, $choices_callable, ?string $default, ?callable $renderer, bool $can_be_null = false, string $hint = '', ?array $conditions = null ) {
			if ( is_null( $conditions ) ) {
				$conditions = array();
			}

			parent::__construct( $id, $name, $default, $renderer, $can_be_null, $hint, $conditions );
			$this->choices_callable = $choices_callable;
		}

		/**
		 * Get all choices.
		 *
		 * @return array|false the choices for this CallableChoiceField.
		 * @throws ReflectionException When the choices_callable function was not callable.
		 */
		public function get_choices() {
			if ( is_null( $this->choices ) ) {
				$this->choices = call_user_func( $this->choices_callable );
				if ( ! is_array( $this->choices ) ) {
					$this->choices = false;
				}
			}

			// At this point, choices is either false or an array of choice values.
			return $this->choices;
		}

		/**
		 * Render this CallableChoiceField.
		 *
		 * @param array $args The arguments passed by WordPress to render this setting.
		 *
		 * @return void
		 * @throws ReflectionException When the choices_callable function was not callable.
		 */
		public function render( array $args ): void {
			$current_value    = $this->get_value();
			$setting_selected = false;
			$choices = $this->get_choices();
			if ( false === $choices ) {
				parent::render( $args );
			} else {
				?>
				<label for="<?php echo esc_attr( $this->id ); ?>"><?php echo esc_html( $this->rendered_hint() ); ?></label>
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
				<?php
			}
		}

		/**
		 * Serialize this setting.
		 *
		 * @return string|null The serialized data, null when it is unset.
		 */
		public function serialize(): ?string {
			return $this->value;
		}

		/**
		 * Deserialize data from a serialized value.
		 *
		 * @param string|null $serialized_value The serialized value.
		 *
		 * @return string|null Deserialized version of the serialized data.
		 */
		public function deserialize( ?string $serialized_value ): ?string {
			return $serialized_value;
		}

		/**
		 * Create a ChoiceField from an array of values.
		 *
		 * @param array $initial_values values to pass to ChoiceField constructor.
		 *
		 * @return CallableChoiceField the created ChoiceField.
		 */
		public static function from_array( array $initial_values ): self {
			return new self(
				$initial_values['id'],
				$initial_values['name'],
				$initial_values['callable'],
				isset( $initial_values['default'] ) ? $initial_values['default'] : null,
				isset( $initial_values['renderer'] ) ? $initial_values['renderer'] : null,
				isset( $initial_values['can_be_null'] ) ? $initial_values['can_be_null'] : false,
				isset( $initial_values['hint'] ) ? $initial_values['hint'] : '',
				isset( $initial_values['conditions'] ) ? $initial_values['conditions'] : null,
			);
		}
	}
}
