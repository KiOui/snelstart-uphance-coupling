<?php
/**
 * Integer Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsfield.php';
include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( ! class_exists( 'IntField' ) ) {
	/**
	 * Integer Field for Settings.
	 *
	 * @class IntField
	 */
	class IntField extends SettingsField {

		/**
		 * Minimum value for this IntField.
		 *
		 * @var int|null
		 */
		private ?int $minimum;

		/**
		 * Maximum value for this IntField.
		 *
		 * @var int|null
		 */
		private ?int $maximum;

		/**
		 * Constructor of IntField.
		 *
		 * @param string        $id the slug-like ID of the setting.
		 * @param string        $name the name of the setting.
		 * @param callable|null $renderer the custom renderer of the SettingsField.
		 * @param ?int          $default the default value of the setting.
		 * @param bool          $can_be_null whether the setting can be null.
		 * @param string        $hint the hint to display next to the setting.
		 * @param int|null      $minimum the minimum value for the IntField, when null no minimum value is specified.
		 * @param int|null      $maximum the maximum value for the IntField, when null no maximum value is specified.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false or when $minimum is
		 * larger than $maximum.
		 */
		public function __construct( string $id, string $name, ?callable $renderer, ?int $default, bool $can_be_null = false, string $hint = '', ?int $minimum = null, ?int $maximum = null ) {
			if ( isset( $minimum ) && isset( $maximum ) && $maximum <= $minimum ) {
				throw new SettingsConfigurationException( 'Minimum must be smaller than maximum.' );
			}

			parent::__construct( $id, $name, $renderer, $default, $can_be_null, $hint );
			$this->minimum = $minimum;
			$this->maximum = $maximum;
		}

		/**
		 * Check whether this IntField has a minimum value registered.
		 *
		 * @return bool true when this IntField has a minimum value.
		 */
		public function has_minimum(): bool {
			return isset( $this->minimum );
		}

		/**
		 * Check whether this IntField has a maximum value registered.
		 *
		 * @return bool true when this IntField has a maximum value.
		 */
		public function has_maximum(): bool {
			return isset( $this->maximum );
		}

		/**
		 * Get the minimum value.
		 *
		 * @return int|null the minimum value.
		 */
		public function get_minimum(): ?int {
			return $this->minimum;
		}

		/**
		 * Get the maximum value.
		 *
		 * @return int|null the maximum value.
		 */
		public function get_maximum(): ?int {
			return $this->maximum;
		}

		/**
		 * Validate an integer value.
		 *
		 * @param mixed    $to_validate the value to validate.
		 * @param int|null $default the default value.
		 * @param bool     $can_be_null whether the value to validate can be null.
		 * @param int|null $minimum the minimum value.
		 * @param int|null $maximum the maximum value.
		 *
		 * @return int|null the validated value.
		 */
		public static function validate_int( $to_validate, ?int $default, bool $can_be_null, ?int $minimum, ?int $maximum ): ?int {
			// Check for null.
			if ( $can_be_null ) {
				if ( '' === $to_validate || is_null( $to_validate ) ) {
					return null;
				}
			} else if ( isset( $default ) && self::is_empty_setting( $to_validate ) ) {
				return $default;
			}

			// Check for minimum and maximum.
			$int_value = intval( $to_validate );
			if ( isset( $minimum ) && $int_value < $minimum ) {
				return $minimum;
			} else if ( isset( $maximum ) && $int_value > $maximum ) {
				return $maximum;
			} else {
				return $int_value;
			}
		}

		/**
		 * Get the value of this setting from an array of options.
		 *
		 * @param array $options the array of options.
		 *
		 * @return int|null a validated integer value.
		 */
		public function get_value( array $options ): ?int {
			$parent_value = parent::get_value( $options );
			return self::validate_int( $parent_value, $this->default, $this->can_be_null, $this->minimum, $this->maximum );
		}

		/**
		 * Render this IntField.
		 *
		 * @param string $setting_name the name of the setting to render this IntField for.
		 * @param array  $options the array of options.
		 *
		 * @return void
		 */
		public function render( string $setting_name, array $options ): void {
			$value        = $this->get_value( $options );
			$setting_id   = $this->get_setting_name( $setting_name ); ?>
			<label><p><?php echo esc_html( $this->rendered_hint() ); ?></p>
				<input type="number" name="<?php echo esc_attr( $setting_id ); ?>"
					   value="<?php echo esc_attr( $value ); ?>"
					<?php if ( $this->has_minimum() ) : ?>
						min="<?php echo esc_attr( $this->minimum ); ?>"
					<?php endif; ?>
					<?php if ( $this->has_maximum() ) : ?>
						max="<?php echo esc_attr( $this->maximum ); ?>"
					<?php endif; ?>
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
		 * @return ?int a validated integer value.
		 */
		public function validate( $value_to_validate ): ?int {
			return self::validate_int( $value_to_validate, $this->default, $this->can_be_null, $this->minimum, $this->maximum );
		}

		/**
		 * Create an IntField from an array of values.
		 *
		 * @param array $initial_values values to pass to IntField constructor.
		 *
		 * @return IntField the created IntField.
		 * @throws SettingsConfigurationException When IntField creation failed.
		 */
		public static function from_array( array $initial_values ): IntField {
			return new self(
				$initial_values['id'],
				$initial_values['name'],
				$initial_values['renderer'],
				$initial_values['default'],
				$initial_values['can_be_null'],
				$initial_values['hint'],
				$initial_values['minimum'],
				$initial_values['maximum'],
			);
		}
	}
}
