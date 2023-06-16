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
		 * @param string    $id the slug-like ID of the setting.
		 * @param string    $name the name of the setting.
		 * @param ?bool     $default the default value of the setting.
		 * @param ?callable $renderer an optional default renderer for the setting.
		 * @param string    $hint the hint to display next to the setting.
		 * @param ?array    $conditions optional array of SettingsConditions that determine whether to display this setting.
		 * @param ?array    $subscribers optional array of Subscribers that get called when this setting updates.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false.
		 */
		public function __construct( string $id, string $name, ?bool $default, ?callable $renderer = null, string $hint = '', ?array $conditions = null, ?array $subscribers = null ) {
			if ( is_null( $conditions ) ) {
				$conditions = array();
			}

			if ( is_null( $subscribers ) ) {
				$subscribers = array();
			}

			if ( ! is_null( $default ) ) {
				if ( true === $default ) {
					$default = 'on';
				} else {
					$default = 'off';
				}
			}

			parent::__construct(
				$id,
				$name,
				array(
					'on' => 'Yes',
					'off' => 'No',
				),
				$default,
				$renderer,
				false,
				$hint,
				$conditions,
				$subscribers
			);
		}

		/**
		 * Serialize this setting.
		 *
		 * @return string The serialized data.
		 */
		public function serialize(): string {
			if ( 'on' === $this->value ) {
				return 'true';
			} else {
				return 'false';
			}
		}

		/**
		 * Sanitize a value for this setting.
		 *
		 * @param mixed $value_to_sanitize The value to sanitize.
		 *
		 * @return string|null The sanitized value.
		 */
		public function sanitize( $value_to_sanitize ): ?string {
			if ( is_null( $value_to_sanitize ) || '' === $value_to_sanitize ) {
				return 'off';
			}
			if ( is_bool( $value_to_sanitize ) ) {
				if ( true === $value_to_sanitize ) {
					return 'on';
				} else {
					return 'off';
				}
			}
			return strval( $value_to_sanitize );
		}

		/**
		 * Set the value for this setting only if it validates correctly.
		 *
		 * @param mixed $value The non-sanitized value to set for this setting.
		 *
		 * @return bool Whether the value was set or not (whether it passed validation).
		 */
		public function set_value( $value ): bool {
			$sanitized_value = $this->sanitize( $value );
			if ( $this->validate( $sanitized_value ) ) {
				$this->value = $sanitized_value;
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Force set the value for this setting.
		 *
		 * @param mixed $value The value to set for this setting.
		 *
		 * @return void
		 */
		public function set_value_force( $value ) {
			if ( is_bool( $value ) ) {
				if ( true === $value ) {
					$this->value = 'on';
				} else {
					$this->value = 'off';
				}
			} else {
				$this->value = $value;
			}
		}

		/**
		 * Get the value of this setting.
		 *
		 * @return bool The value of this setting.
		 */
		public function get_value(): bool {
			return 'on' === $this->value;
		}

		/**
		 * Render this ChoiceField.
		 *
		 * @param array $args The arguments passed by WordPress to render this setting.
		 *
		 * @return void
		 */
		public function render( array $args ): void {
			$current_value    = $this->get_value(); ?>
			<label for="<?php echo esc_attr( $this->id ); ?>"><?php echo esc_html( $this->rendered_hint() ); ?></label>
			<select name="<?php echo esc_attr( $this->id ); ?>">
				<option
					<?php if ( true === $current_value ) : ?>
						selected
					<?php endif; ?>
					value="on">Yes</option>
				<option
					<?php if ( false === $current_value ) : ?>
						selected
					<?php endif; ?>
					value="off">No</option>
			</select>
			<?php
		}

		/**
		 * Deserialize data from a serialized value.
		 *
		 * @param string|null $serialized_value The serialized value.
		 *
		 * @return string Deserialized version of the serialized data.
		 */
		public function deserialize( ?string $serialized_value ): string {
			if ( 'true' === $serialized_value ) {
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
				isset( $initial_values['subscribers'] ) ? $initial_values['subscribers'] : null,
			);
		}
	}
}
