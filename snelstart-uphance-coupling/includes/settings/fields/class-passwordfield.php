<?php
/**
 * Password Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/fields/class-settingsfield.php';

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
		 * @return void
		 */
		public function render( array $args ): void {
			$value        = $this->get_value(); ?>
			<label><?php echo esc_html( $this->rendered_hint() ); ?>
				<input type="password" name="<?php echo esc_attr( $this->id ); ?>"
				       value="<?php echo esc_attr( $value ); ?>"
					<?php if ( ! $this->can_be_null ) : ?>
						required
					<?php endif; ?>
				/>
			</label>
			<?php
		}

		/**
		 * Create a TextField from an array of values.
		 *
		 * @param array $initial_values values to pass to TextField constructor.
		 *
		 * @return PasswordField the created TextField.
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
				isset( $initial_values['conditions'] ) ? $initial_values['conditions'] : null
			);
		}
	}
}
