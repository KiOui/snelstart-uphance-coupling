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
		 * @param string      $id the slug-like ID of the setting.
		 * @param string      $name the name of the setting.
		 * @param string|null $default the default value of the setting.
		 * @param ?callable   $renderer an optional default renderer for the setting.
		 * @param bool        $can_be_null whether the setting can be null.
		 * @param string      $hint the hint to display next to the setting.
		 * @param ?array      $conditions optional array of SettingsConditions that determine whether to display this setting.
		 * @param ?array      $subscribers optional array of Subscribers that get called when this setting updates.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false.
		 */
		public function __construct( string $id, string $name, ?string $default, ?callable $renderer = null, bool $can_be_null = false, string $hint = '', ?array $conditions = null, ?array $subscribers = null ) {
			if ( is_null( $conditions ) ) {
				$conditions = array();
			}

			if ( is_null( $subscribers ) ) {
				$subscribers = array();
			}

			parent::__construct( $id, $name, $default, $renderer, $can_be_null, $hint, $conditions, $subscribers );
		}

		/**
		 * Validate a value for this setting.
		 *
		 * @param mixed $value_to_validate The value to validate.
		 *
		 * @return bool Whether the value can be set to the value for this setting (whether it was validated correctly).
		 */
		public function validate( $value_to_validate ): bool {
			if ( ! is_null( $value_to_validate ) && ! is_string( $value_to_validate ) ) {
				return false;
			}

			if ( is_null( $value_to_validate ) ) {
				return $this->can_be_null;
			}

			return true;
		}

		/**
		 * Sanitize a value for this setting.
		 *
		 * @param mixed $value_to_sanitize The value to sanitize.
		 *
		 * @return string|null The sanitized value.
		 */
		public function sanitize( $value_to_sanitize ): ?string {
			if ( is_null( $value_to_sanitize ) ) {
				return null;
			}

			return strval( $value_to_sanitize );
		}

		/**
		 * Render this TextField.
		 *
		 * @param array $args The arguments passed by WordPress to render this setting.
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
				isset( $initial_values['conditions'] ) ? $initial_values['conditions'] : null,
				isset( $initial_values['subscribers'] ) ? $initial_values['subscribers'] : null,
			);
		}
	}
}
