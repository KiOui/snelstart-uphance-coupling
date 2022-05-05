<?php
/**
 * Password Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsfield.php';

if ( ! class_exists( 'PasswordField' ) ) {
	/**
	 * Password Field for Settings.
	 *
	 * @class PasswordField
	 */
	class PasswordField extends TextField {

		/**
		 * Render this PasswordField.
		 *
		 * @param string $setting_name the name of the setting to render this PasswordField for.
		 * @param array  $options the array of options.
		 *
		 * @return void
		 */
		public function render( string $setting_name, array $options ): void {
			$value        = $this->get_value( $options );
			$setting_id   = $this->get_setting_name( $setting_name ); ?>
			<label><p><?php echo esc_html( $this->rendered_hint() ); ?></p>
				<input type="password" name="<?php echo esc_attr( $setting_id ); ?>"
					   value="<?php echo esc_attr( $value ); ?>"
					<?php if ( ! $this->can_be_null ) : ?>
						required
					<?php endif; ?>
				/>
			</label>
			<?php
		}

		/**
		 * Create a PasswordField from an array of values.
		 *
		 * @param array $initial_values values to pass to PasswordField constructor.
		 *
		 * @return PasswordField the created PasswordField.
		 * @throws SettingsConfigurationException When PasswordField creation failed.
		 */
		public static function from_array( array $initial_values ): self {
			return new self(
				$initial_values['id'],
				$initial_values['name'],
				isset( $initial_values['renderer'] ) ? $initial_values['renderer'] : null,
				isset( $initial_values['default'] ) ? $initial_values['default'] : null,
				$initial_values['can_be_null'],
				$initial_values['hint'],
			);
		}
	}
}
