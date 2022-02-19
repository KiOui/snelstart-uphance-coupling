<?php
/**
 * Settings Field.
 *
 * @package snelstart-uphance-coupling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once SUC_ABSPATH . 'includes/settings/class-settingsconfigurationexception.php';

if ( !class_exists( "SettingsField" ) ) {
	/**
	 * Field for Settings.
	 *
	 * @class SettingsField
	 */
	abstract class SettingsField {

		protected string $id;
		protected string $name;
		protected $renderer;
		protected bool $can_be_null;
		protected string $hint;
        protected mixed $default;

		/**
		 * @throws SettingsConfigurationException when no default is provided and setting can not be null
		 */
		public function __construct( string $id, string $name, ?callable $renderer, mixed $default, bool $can_be_null = false, string $hint = "" ) {
            if ( is_null( $default ) && !$can_be_null ) {
                throw new SettingsConfigurationException( "Error while registering setting $id, setting can not be null but no default is provided." );
            }

			$this->id = $id;
			$this->name = $name;
            $this->renderer = $renderer;
			$this->can_be_null = $can_be_null;
			$this->hint = $hint;
            $this->default = $default;
		}

        public function get_id(): string {
            return $this->id;
        }

        public static function is_empty_setting( mixed $setting_value ): bool {
            return $setting_value === "" || is_null( $setting_value );
        }

		public function get_renderer(): callable {
			return $this->renderer ?? array( $this, 'render' );
		}

        public function actions_and_filters(): void {

        }

		public function do_register( string $page, string $section_id ): void {
			add_settings_field(
				$this->id,
				$this->name,
				$this->get_renderer(),
				$page,
				$section_id,
			);
		}

		public function get_value( array $options ): mixed {
            return $options[ $this->id ];
		}

		public function rendered_hint(): string {
			ob_start();
			?>
			<p><?php echo esc_html( $this->hint ); ?></p>
			<?php
			return ob_get_clean();
		}

        public function get_setting_name( string $setting_name ): string {
            return $setting_name . '[' . $this->id . ']';
        }

		public abstract function render( string $setting_name, array $options ): void;

		public abstract function validate( mixed $value_to_validate ): mixed;

        public abstract static function from_array( array $initial_values ): self;
	}
}