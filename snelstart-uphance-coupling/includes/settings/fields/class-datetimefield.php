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

		public function __construct( string $id, string $name, ?callable $renderer, ?DateTime $default, bool $can_be_null = false, string $hint = '' ) {
			parent::__construct( $id, $name, $renderer, $default, $can_be_null, $hint );
		}

		/**
		 * @throws SettingsConfigurationException
		 */
		public static function validate_datetime( mixed $to_validate, ?DateTime $default, bool $can_be_null ): ?DateTime {
			// Check for null
			if ( $can_be_null ) {
				if ( $to_validate === '' ) {
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
				} else if ( isset( $default ) ) {
					return $default;
				} else {
					throw new SettingsConfigurationException( 'DateTime could not be converted and setting can not be null and no default is set.' );
				}
			}
		}

		public function render( string $setting_name, array $options ): void {
			$value        = $this->get_value( $options );
			$setting_id   = $this->get_setting_name( $setting_name ); ?>
			<label><?php echo $this->rendered_hint(); ?>
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
		 * @throws SettingsConfigurationException
		 */
		public function get_value( array $options ): ?DateTime {
			$parent_value = parent::get_value( $options );
			return self::validate_datetime( $parent_value, $this->default, $this->can_be_null );
		}

		/**
		 * @throws SettingsConfigurationException
		 */
		public function validate( mixed $value_to_validate ): ?string {
			$datetime = self::validate_datetime( $value_to_validate, $this->default, $this->can_be_null );
			if ( isset( $datetime ) ) {
				return $datetime->format( 'Y-m-d\TH:i:sP' );
			} else {
				return null;
			}
		}

		/**
		 * @throws SettingsConfigurationException
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
