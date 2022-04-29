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

if ( ! class_exists( 'SettingsField' ) ) {
	/**
	 * Field for Settings.
	 *
	 * @class SettingsField
	 */
	abstract class SettingsField {

		/**
		 * The ID of the setting (as a slug).
		 *
		 * @var string
		 */
		protected string $id;

		/**
		 * The name of the setting.
		 *
		 * @var string
		 */
		protected string $name;

		/**
		 * The custom renderer of the setting section. Defaults to the default renderer of this class.
		 *
		 * @var callable|null
		 */
		protected $renderer;

		/**
		 * Whether this setting can be null.
		 *
		 * @var bool
		 */
		protected bool $can_be_null;

		/**
		 * The hint to display next to the setting.
		 *
		 * @var string
		 */
		protected string $hint;

		/**
		 * The default value of the setting.
		 *
		 * @var mixed
		 */
		protected $default;

		/**
		 * Constructor of SettingsField.
		 *
		 * @param string        $id the slug-like ID of the setting.
		 * @param string        $name the name of the setting.
		 * @param callable|null $renderer the custom renderer of the SettingsField.
		 * @param mixed         $default the default value of the setting.
		 * @param bool          $can_be_null whether the setting can be null.
		 * @param string        $hint the hint to display next to the setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false.
		 */
		public function __construct( string $id, string $name, ?callable $renderer, $default, bool $can_be_null = false, string $hint = '' ) {
			if ( is_null( $default ) && ! $can_be_null ) {
				throw new SettingsConfigurationException( "Error while registering setting $id, setting can not be null but no default is provided." );
			}

			$this->id = $id;
			$this->name = $name;
			$this->renderer = $renderer;
			$this->can_be_null = $can_be_null;
			$this->hint = $hint;
			$this->default = $default;
		}

		/**
		 * Get the slug-like ID of this SettingsField.
		 *
		 * @return string the slug-like ID.
		 */
		public function get_id(): string {
			return $this->id;
		}

		/**
		 * Check whether the value of the parameter is empty.
		 *
		 * @param mixed $setting_value the value to check.
		 *
		 * @return bool true when the $setting_value is an empty string or null.
		 */
		public static function is_empty_setting( $setting_value ): bool {
			return '' === $setting_value || is_null( $setting_value );
		}

		/**
		 * Get the renderer for this SettingsField.
		 *
		 * @return callable the renderer for this SettingsField.
		 */
		public function get_renderer(): callable {
			return $this->renderer ?? array( $this, 'render' );
		}

		/**
		 * Register this SettingsField in WordPress.
		 *
		 * @param string $page the page to register this SettingsField on.
		 * @param string $section_id the slug-like section ID to register this SettingsField on.
		 *
		 * @return void
		 */
		public function do_register( string $page, string $section_id ): void {
			add_settings_field(
				$this->id,
				$this->name,
				$this->get_renderer(),
				$page,
				$section_id,
			);
		}

		/**
		 * Get the raw value of this setting given an option array.
		 *
		 * @param array $options an option array.
		 *
		 * @return mixed the value of the key with $this->id in the $options array.
		 */
		public function get_value( array $options ) {
			if ( isset( $options[ $this->id ] ) ) {
				return $options[ $this->id ];
			}
			return null;
		}

		/**
		 * Render the SettingsField hint.
		 *
		 * @return string the HTML rendered SettingsField hint.
		 */
		public function rendered_hint(): string {
			ob_start();
			?>
			<?php echo esc_html( $this->hint ); ?>
			<?php
			return ob_get_clean();
		}

		/**
		 * Get the name of the setting to be rendered inside a form input field name attribute.
		 *
		 * @param string $setting_name the setting name of the setting this SettingsField is registered on.
		 *
		 * @return string the value of the name attribute to render in an input tag.
		 */
		public function get_setting_name( string $setting_name ): string {
			return $setting_name . '[' . $this->id . ']';
		}

		/**
		 * Render this SettingsField.
		 *
		 * @param string $setting_name the setting name of the setting this SettingsField is registered on.
		 * @param array  $options an options array.
		 *
		 * @return void
		 */
		abstract public function render( string $setting_name, array $options ): void;

		/**
		 * Validate a value for this setting.
		 *
		 * @param $value_to_validate mixed the value to validate for this setting.
		 *
		 * @return mixed the validated value.
		 */
		abstract public function validate( $value_to_validate );

		/**
		 * Convert an array to a SettingsField.
		 *
		 * @param array $initial_values an array of values to pass to the SettingsField constructor.
		 *
		 * @return static a SettingsField.
		 */
		abstract public static function from_array( array $initial_values ): self;
	}
}
