<?php
/**
 * DateTime Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsfield.php';
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
		 * @param callable|null $renderer the custom renderer of the SettingsField.
		 * @param ?DateTime     $default the default value of the setting.
		 * @param bool          $can_be_null whether the setting can be null.
		 * @param string        $hint the hint to display next to the setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false.
		 */
		public function __construct( string $id, string $name, ?callable $renderer, ?DateTime $default, bool $can_be_null = false, string $hint = '' ) {
			parent::__construct( $id, $name, $renderer, $default, $can_be_null, $hint );
		}

		/**
		 * Validate a datetime value.
		 *
		 * @param mixed         $to_validate the value to validate.
		 * @param DateTime|null $default the default value.
		 * @param bool          $can_be_null whether the value to validate can be null.
		 *
		 * @return DateTime|null the validated value.
		 */
		public static function validate_datetime( $to_validate, ?DateTime $default, bool $can_be_null ): ?DateTime {
			// Check for null.
			if ( $can_be_null ) {
				if ( '' === $to_validate ) {
					return null;
				}
			} else if ( isset( $default ) && self::is_empty_setting( $to_validate ) ) {
				return $default;
			}

			try {
				return new DateTime( $to_validate );
			} catch ( Exception $e ) {
				if ( $can_be_null ) {
					return null;
				} else {
					return $default;
				}
			}
		}

		/**
		 * Render this DateTimeField.
		 *
		 * @param string $setting_name the name of the setting to render this DateTimeField for.
		 * @param array  $options the array of options.
		 *
		 * @return void
		 */
		public function render( string $setting_name, array $options ): void {
			$value        = $this->get_value( $options );
			$setting_id   = $this->get_setting_name( $setting_name ); ?>
			<label><?php echo esc_html( $this->rendered_hint() ); ?>
				<input type="datetime-local" name="<?php echo esc_attr( $setting_id ); ?>"
					   value="<?php echo esc_attr( $value->format( 'Y-m-d\TH:i' ) ); ?>"
					<?php if ( ! $this->can_be_null ) : ?>
						required
					<?php endif; ?>
				/>
			</label>
			<?php
		}

		/**
		 * Get the value of this setting from an array of options.
		 *
		 * @param array $options the array of options.
		 *
		 * @return DateTime|null a validated DateTime value.
		 */
		public function get_value( array $options ): ?DateTime {
			$parent_value = parent::get_value( $options );
			return self::validate_datetime( $parent_value, $this->default, $this->can_be_null );
		}

		/**
		 * Validate the value for this setting.
		 *
		 * @param mixed $value_to_validate the value to validate.
		 *
		 * @return ?string a validated string value.
		 */
		public function validate( $value_to_validate ): ?string {
			$datetime = self::validate_datetime( $value_to_validate, $this->default, $this->can_be_null );
			if ( isset( $datetime ) ) {
				return $datetime->format( 'Y-m-d\TH:i:sP' );
			} else {
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
				$initial_values['renderer'],
				$initial_values['default'],
				$initial_values['hint'],
			);
		}
	}
}
