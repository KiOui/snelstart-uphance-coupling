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
include_once SUC_ABSPATH . 'includes/settings/conditions/class-settingscondition.php';

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
		 * The value of the setting.
		 *
		 * @var mixed
		 */
		protected $value;

		/**
		 * The optional renderer for this setting.
		 *
		 * @var ?callable
		 */
		protected $renderer;

		/**
		 * The conditions whether to show this setting.
		 *
		 * @var SettingsCondition[]
		 */
		protected array $conditions;

		/**
		 * Constructor of SettingsField.
		 *
		 * @param string    $id the slug-like ID of the setting.
		 * @param string    $name the name of the setting.
		 * @param mixed     $default the default value of the setting.
		 * @param ?callable $renderer an optional default renderer for the setting.
		 * @param bool      $can_be_null whether the setting can be null.
		 * @param string    $hint the hint to display next to the setting.
		 * @param ?array    $conditions optional array of SettingsConditions that determine whether to display this setting.
		 *
		 * @throws SettingsConfigurationException When $default is null and $can_be_null is false.
		 */
		public function __construct( string $id, string $name, $default, ?callable $renderer = null, bool $can_be_null = false, string $hint = '', ?array $conditions = null ) {
			if ( is_null( $default ) && ! $can_be_null ) {
				throw new SettingsConfigurationException( "Error while registering setting $id, setting can not be null but no default is provided." );
			}

			if ( is_null( $conditions ) ) {
				$conditions = array();
			}

			$this->id = $id;
			$this->name = $name;
			$this->can_be_null = $can_be_null;
			$this->hint = $hint;
			$this->default = $default;
			$this->conditions = $conditions;
			$this->value = null;
			$this->renderer = $renderer;
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
		 * Get the name of this setting.
		 *
		 * @return string The name of this setting.
		 */
		public function get_name(): string {
			return $this->name;
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
		 * Render this SettingsField.
		 *
		 * @param array $args The arguments passed by WordPress to render this setting.
		 *
		 * @return void
		 */
		abstract public function render( array $args ): void;

		/**
		 * Get the raw value of this setting given an option array.
		 *
		 * @return mixed the value of the key with $this->id in the $options array.
		 */
		public function get_value() {
			if ( is_null( $this->value ) && ! $this->can_be_null ) {
				return $this->default;
			} else {
				return $this->value;
			}
		}

		/**
		 * Get the conditions for this settings field.
		 *
		 * @return SettingsCondition[] The conditions for this settings field.
		 */
		public function get_conditions(): array {
			return $this->conditions;
		}

		/**
		 * Set the value of a setting.
		 *
		 * @param mixed $value The value of a setting.
		 *
		 * @return bool Whether the setting was overwritten.
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
		 * Force-set value for this setting (do not sanitize or validate beforehand).
		 *
		 * @param mixed $value The value to set this setting to.
		 *
		 * @return void
		 */
		public function set_value_force( $value ) {
			$this->value = $value;
		}

		/**
		 * Whether this setting is set.
		 *
		 * @return bool True when this setting is set, false otherwise.
		 */
		public function is_set(): bool {
			return null !== $this->value && '' !== $this->value;
		}

		/**
		 * Sanitize a value for this setting.
		 *
		 * @param mixed $value_to_sanitize the value to sanitize for this setting.
		 *
		 * @return mixed the sanitized value.
		 */
		abstract public function sanitize( $value_to_sanitize );

		/**
		 * Validate a value for this setting.
		 *
		 * @param $value_to_validate mixed the value to validate for this setting.
		 *
		 * @return bool whether the value could be validated correctly.
		 */
		abstract public function validate( $value_to_validate ): bool;

		/**
		 * Serialize this setting.
		 *
		 * @return string|null The serialized data, null when it is unset.
		 */
		abstract public function serialize(): ?string;

		/**
		 * Set the default value of this setting.
		 *
		 * @return void
		 */
		public function set_default() {
			$this->value = $this->default;
		}

		/**
		 * Deserialize data from a serialized value.
		 *
		 * @param string|null $serialized_value The serialized value.
		 *
		 * @return string|null Deserialized version of the serialized data.
		 */
		public function deserialize( ?string $serialized_value ) {
			$sanitized_value = $this->sanitize( $serialized_value );

			if ( $this->validate( $sanitized_value ) ) {
				return $sanitized_value;
			} else {
				return $this->default;
			}
		}

		/**
		 * Convert an array to a SettingsField.
		 *
		 * @param array $initial_values an array of values to pass to the SettingsField constructor.
		 *
		 * @return static a SettingsField.
		 */
		abstract public static function from_array( array $initial_values ): self;

		/**
		 * Whether this settings field should be shown.
		 *
		 * @param Settings $settings The other settings.
		 *
		 * @return bool True when the conditions all return true, false otherwise.
		 */
		public function should_be_shown( Settings $settings ): bool {
			foreach ( $this->conditions as $condition ) {
				if ( ! $condition->holds( $settings ) ) {
					return false;
				}
			}
			return true;
		}
	}
}
