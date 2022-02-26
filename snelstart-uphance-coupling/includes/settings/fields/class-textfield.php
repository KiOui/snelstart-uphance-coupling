<?php
/**
 * Text Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsfield.php';
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
		 * @param callable|null $renderer the custom renderer of the SettingsField.
		 * @param string|null         $default the default value of the setting.
		 * @param bool          $can_be_null whether the setting can be null.
		 * @param string        $hint the hint to display next to the setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false.
		 */
		public function __construct( string $id, string $name, ?callable $renderer, ?string $default, bool $can_be_null = false, string $hint = '' ) {
			parent::__construct( $id, $name, $renderer, $default, $can_be_null, $hint );
		}

		/**
		 * Validate a string value.
		 *
		 * @param mixed    $to_validate the value to validate.
		 * @param string|null $default the default value.
		 * @param bool     $can_be_null whether the value to validate can be null.
		 *
		 * @return string|null the validated value.
		 */
		public static function validate_string( $to_validate, ?string $default, bool $can_be_null ): ?string {
			if ( ! isset( $to_validate ) && isset( $default ) ) {
				return $default;
			}
			$string_value = strval( $to_validate );
			if ( '' === $string_value && $can_be_null ) {
				return null;
			} else {
				return $string_value;
			}
		}

		/**
		 * Get the value of this setting from an array of options.
		 *
		 * @param array $options the array of options.
		 *
		 * @return string|null a validated string value.
		 */
		public function get_value( array $options ): ?string {
			$parent_value = parent::get_value( $options );
			return self::validate_string( $parent_value, $this->default, $this->can_be_null );
		}

		/**
		 * Render this TextField.
		 *
		 * @param string $setting_name the name of the setting to render this TextField for.
		 * @param array  $options the array of options.
		 *
		 * @return void
		 */
		public function render( string $setting_name, array $options ): void {
			$value        = $this->get_value( $options );
			$setting_id   = $this->get_setting_name( $setting_name ); ?>
			<label><?php echo esc_html( $this->rendered_hint() ); ?>
				<input type="text" name="<?php echo esc_attr( $setting_id ); ?>"
					   value="<?php echo esc_attr( $value ); ?>"
					<?php if ( ! $this->can_be_null ) : ?>
						required
					<?php endif; ?>
				/>
			</label>
			<?php
		}

		/**
		 * Validate the value for this setting.
		 *
		 * @param mixed $value_to_validate the value to validate.
		 *
		 * @return ?string a validated string value.
		 */
		public function validate( $value_to_validate ): ?string {
			return self::validate_string( $value_to_validate, $this->default, $this->can_be_null );
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
				$initial_values['renderer'],
				$initial_values['default'],
				$initial_values['can_be_null'],
				$initial_values['hint'],
			);
		}
	}
}
