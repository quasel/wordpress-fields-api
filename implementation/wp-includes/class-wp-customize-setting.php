<?php
/**
 * WordPress Customize Setting classes
 *
 * @package WordPress
 * @subpackage Customize
 * @since 3.4.0
 */

/**
 * Customize Setting class.
 *
 * Handles saving and sanitizing of settings.
 *
 * @since 3.4.0
 *
 * @see WP_Customize_Manager
 */
class WP_Customize_Setting extends WP_Fields_API_Field {

	/**
	 * @access public
	 * @var WP_Customize_Manager
	 */
	public $manager;

	/**
	 * @access public
	 * @var string
	 */
	public $type = 'theme_mod';

	/**
	 * Cached and sanitized $_POST value for the setting.
	 *
	 * @access private
	 * @var mixed
	 */
	private $_post_value;

	/**
	 * Constructor.
	 *
	 * Any supplied $args override class property defaults.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Customize_Manager $manager
	 * @param string               $id      An specific ID of the setting. Can be a
	 *                                      theme mod or option name.
	 * @param array                $args    Setting arguments.
	 */
	public function __construct( $manager, $id, $args = array() ) {

		if ( isset( $args['type'] ) ) {
			$this->type = $args['type'];
		}

		$this->manager = $manager;

		parent::__construct( $this->type, $id, $args );

		// Add compatibility hooks
		add_action( "fields_preview_{$this->id}",                                 array( $this, 'customize_preview_id' ) );
		add_action( "fields_preview_{$this->type}",                               array( $this, 'customize_preview_type' ) );
		add_action( 'fields_save_' . $this->type . '_' . $this->id_data['base'],  array( $this, 'customize_save' ) );
		add_filter( "fields_sanitize_{$this->type}_{$this->id}",                  array( $this, 'customize_sanitize' ) );
		add_filter( "fields_sanitize_js_{$this->type}_{$this->id}",               array( $this, 'customize_sanitize_js_value' ) );
		add_action( "fields_update_{$this->type}",                                array( $this, 'customize_update' ) );
		add_action( 'fields_value_' . $this->type . '_' . $this->id_data['base'], array( $this, 'customize_value' ) );

	}

	/**
	 * Handle previewing the setting by ID.
	 */
	public function customize_preview_id() {

		/**
		 * Fires when the {@see WP_Customize_Setting::preview()} method is called for settings
		 * not handled as theme_mods or options.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to the setting ID.
		 *
		 * @since 3.4.0
		 *
		 * @param WP_Customize_Setting $this {@see WP_Customize_Setting} instance.
		 */
		do_action( "customize_preview_{$this->id}", $this );

	}

	/**
	 * Handle previewing the setting by typw.
	 */
	public function customize_preview_type() {

		/**
		 * Fires when the {@see WP_Customize_Setting::preview()} method is called for settings
		 * not handled as theme_mods or options.
		 *
		 * The dynamic portion of the hook name, `$this->type`, refers to the setting type.
		 *
		 * @since 4.1.0
		 *
		 * @param WP_Customize_Setting $this {@see WP_Customize_Setting} instance.
		 */
		do_action( "customize_preview_{$this->object_type}", $this );

	}

	/**
	 * Check user capabilities and theme supports, and then save
	 * the value of the setting.
	 *
	 * @since 3.4.0
	 *
	 * @return false|null False if cap check fails or value isn't set.
	 */
	final public function customize_save() {

		/**
		 * Fires when the WP_Customize_Setting::save() method is called.
		 *
		 * The dynamic portion of the hook name, `$this->id_data['base']` refers to
		 * the base slug of the setting name.
		 *
		 * @since 3.4.0
		 *
		 * @param WP_Customize_Setting $this {@see WP_Customize_Setting} instance.
		 */
		do_action( 'customize_save_' . $this->id_data['base'], $this );

	}

	/**
	 * Sanitize an input.
	 *
	 * @since 3.4.0
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed Null if an input isn't valid, otherwise the sanitized value.
	 */
	public function customize_sanitize( $value ) {

		/**
		 * Filter a Customize setting value in un-slashed form.
		 *
		 * @since 3.4.0
		 *
		 * @param mixed                $value Value of the setting.
		 * @param WP_Customize_Setting $this  WP_Customize_Setting instance.
		 */
		return apply_filters( "customize_sanitize_{$this->id}", $value, $this );

	}

	/**
	 * Save the value of the setting, using the related API.
	 *
	 * @since 3.4.0
	 *
	 * @param mixed $value The value to update.
	 * @return mixed The result of saving the value.
	 */
	protected function customize_update( $value ) {

		/**
		 * Fires when the {@see WP_Customize_Setting::update()} method is called for settings
		 * not handled as theme_mods or options.
		 *
		 * The dynamic portion of the hook name, `$this->type`, refers to the type of setting.
		 *
		 * @since 3.4.0
		 *
		 * @param mixed                $value Value of the setting.
		 * @param WP_Customize_Setting $this  WP_Customize_Setting instance.
		 */
		do_action( 'customize_update_' . $this->type, $value, $this );

	}

	/**
	 * Fetch the value of the setting.
	 *
	 * @since 3.4.0
	 *
	 * @param string $default Default value for field
	 *
	 * @return mixed The value.
	 */
	public function customize_value( $default ) {

		/**
		 * Filter a Customize setting value not handled as a theme_mod or option.
		 *
		 * The dynamic portion of the hook name, `$this->id_date['base']`, refers to
		 * the base slug of the setting name.
		 *
		 * For settings handled as theme_mods or options, see those corresponding
		 * functions for available hooks.
		 *
		 * @since 3.4.0
		 *
		 * @param mixed $default The setting default value. Default empty.
		 * @param WP_Customize_Setting $this  {@see WP_Customize_Setting} instance.
		 */
		return apply_filters( 'customize_value_' . $this->id_data['base'], $default, $this );

	}

	/**
	 * Sanitize the setting's value for use in JavaScript.
	 *
	 * @since 3.4.0
	 *
	 * @param mixed $value The setting value.
	 *
	 * @return mixed The requested escaped value.
	 */
	public function customize_sanitize_js_value( $value ) {

		/**
		 * Filter a Customize setting value for use in JavaScript.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to the setting ID.
		 *
		 * @since 3.4.0
		 *
		 * @param mixed                $value The setting value.
		 * @param WP_Customize_Setting $this  {@see WP_Customize_Setting} instance.
		 */
		$value = apply_filters( "customize_sanitize_js_{$this->id}", $value, $this );

		return $value;

	}

	/**
	 * Fetch and sanitize the $_POST value for the setting.
	 *
	 * @since 3.4.0
	 *
	 * @param mixed $default A default value which is used as a fallback. Default is null.
	 * @return mixed The default value on failure, otherwise the sanitized value.
	 */
	final public function post_value( $default = null ) {

		// Check for a cached value
		if ( isset( $this->_post_value ) ) {
			return $this->_post_value;
		}

		// Call the manager for the post value
		$result = $this->manager->post_value( $this );

		$value = $default;

		if ( isset( $result ) ) {
			$this->_post_value = $value = $result;

			return $value;
		}

		return $value;

	}

}

/**
 * A setting that is used to filter a value, but will not save the results.
 *
 * Results should be properly handled using another setting or callback.
 *
 * @since 3.4.0
 *
 * @see WP_Customize_Setting
 */
class WP_Customize_Filter_Setting extends WP_Customize_Setting {

	/**
	 * Update value
	 *
	 * @since 3.4.0
	 *
	 * @param mixed $value The value to update.
	 *
	 * @return mixed The result of saving the value.
	 */
	public function update( $value ) {

		return null;

	}
}

/**
 * A setting that is used to filter a value, but will not save the results.
 *
 * Results should be properly handled using another setting or callback.
 *
 * @since 3.4.0
 *
 * @see WP_Customize_Setting
 */
final class WP_Customize_Header_Image_Setting extends WP_Customize_Setting {
	public $id = 'header_image_data';

	/**
	 * @since 3.4.0
	 *
	 * @param mixed $value The value to update.
	 *
	 * @return mixed The result of saving the value.
	 */
	public function update( $value ) {
		global $custom_image_header;

		// If the value doesn't exist (removed or random),
		// use the header_image value.
		if ( ! $value )
			$value = $this->manager->get_setting('header_image')->post_value();

		if ( is_array( $value ) && isset( $value['choice'] ) )
			$custom_image_header->set_header_image( $value['choice'] );
		else
			$custom_image_header->set_header_image( $value );
	}
}

/**
 * Customizer Background Image Setting class.
 *
 * @since 3.4.0
 *
 * @see WP_Customize_Setting
 */
final class WP_Customize_Background_Image_Setting extends WP_Customize_Setting {
	public $id = 'background_image_thumb';

	/**
	 * @since 3.4.0
	 *
	 * @param mixed $value The value to update.
	 *
	 * @return mixed The result of saving the value.
	 */
	public function update( $value ) {
		remove_theme_mod( 'background_image_thumb' );
	}
}
