<?php
/**
 * Boolean Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsfield.php';
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
		 * @param callable|null $renderer the custom renderer of the SettingsField.
		 * @param mixed         $default the default value of the setting.
		 * @param string        $hint the hint to display next to the setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false.
		 */
		public function __construct( string $id, string $name, ?callable $renderer, $default, string $hint = '' ) {
			parent::__construct( $id, $name, $renderer, $default, false, $hint );
		}

		/**
		 * Validate a boolean.
		 *
		 * @param mixed $to_validate the value to validate.
		 * @param bool  $default the default value of the value to validate.
		 *
		 * @return bool the validated value.
		 */
		public static function validate_bool( $to_validate, bool $default ): bool {
			if ( ! isset( $to_validate ) ) {
				return $default;
			} else {
				return boolval( $to_validate );
			}
		}

		/**
		 * Get the value of this setting from an array of options.
		 *
		 * @param array $options the array of options.
		 *
		 * @return bool a validated boolean value.
		 */
		public function get_value( array $options ): bool {
			$parent_value = parent::get_value( $options );
			return self::validate_bool( $parent_value, $this->default );
		}

		/**
		 * Render this BoolField.
		 *
		 * @param string $setting_name the name of the setting to render this BoolField for.
		 * @param array  $options the array of options.
		 *
		 * @return void
		 */
		public function render( string $setting_name, array $options ): void {
			$value        = $this->get_value( $options );
			$setting_id   = $this->get_setting_name( $setting_name ); ?>
            <label><p><?php echo esc_html( $this->rendered_hint() ); ?></p>
				<input type="checkbox" name="<?php echo esc_attr( $setting_id ); ?>"
					<?php checked( $value ); ?> />
			</label>
			<?php
		}

		/**
		 * Validate the value for this setting.
		 *
		 * @param mixed $value_to_validate the value to validate.
		 *
		 * @return bool a validated boolean value.
		 */
		public function validate( $value_to_validate ): bool {
			return self::validate_bool( $value_to_validate, $this->default );
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
				$initial_values['renderer'],
				$initial_values['default'],
				$initial_values['hint'],
			);
		}
	}
}
