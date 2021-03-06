<?php
/**
 * WordPress Customize Panel classes
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.0.0
 */

/**
 * Customize Panel class.
 *
 * A UI container for sections, managed by the WP_Customize_Manager.
 *
 * @since 4.0.0
 *
 * @see WP_Customize_Manager
 */
class WP_Customize_Panel extends WP_Fields_API_Screen {

	/**
	 * WP_Customize_Manager instance.
	 *
	 * @access public
	 * @var WP_Customize_Manager
	 */
	public $manager;

	/**
	 * Constructor.
	 *
	 * Any supplied $args override class property defaults.
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      An specific ID for the panel.
	 * @param array                $args    Panel arguments.
	 */
	public function __construct( $manager, $id, $args = array() ) {

		$this->manager = $manager;

		parent::__construct( $this->type, $id, $args );

		add_action( "fields_api_screen_active_{$this->object_type}", array( $this, 'customize_panel_active' ), 10, 2 );
		add_action( "fields_api_render_screen_{$this->object_type}", array( $this, 'customize_render_panel' ) );
		add_action( "fields_api_render_screen_{$this->object_type}_{$this->id}", array( $this, 'customize_render_panel_id' ) );

	}

	/**
	 * Check whether panel is active to current Customizer preview.
	 *
	 * @access public
	 *
	 * @param bool                 $active  Whether the Fields API screen is active.
	 * @param WP_Fields_API_Screen  $screen {@see WP_Fields_API_Screen} instance.
	 *
	 * @return bool Whether the panel is active to the current preview.
	 */
	final public function customize_panel_active( $active, $screen ) {

		/**
		 * Filter response of {@see WP_Customize_Panel::active()}.
		 *
		 * @since 4.1.0
		 *
		 * @param bool                 $active  Whether the Customizer panel is active.
		 * @param WP_Customize_Panel   $screen {@see WP_Customize_Panel} instance.
		 */
		$active = apply_filters( 'customize_panel_active', $active, $screen );

		return $active;

	}

	/**
	 * Backwards compatibility for fields_api_render_panel
	 */
	public function customize_render_panel() {

		/**
		 * Fires before rendering a Customizer panel.
		 *
		 * @since 4.0.0
		 *
		 * @param WP_Customize_Panel $this WP_Customize_Panel instance.
		 */
		do_action( 'customize_render_panel', $this );

	}

	/**
	 * Backwards compatibility for fields_api_render_panel
	 */
	public function customize_render_panel_id() {

		/**
		 * Fires before rendering a specific Customizer panel.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the ID of the specific Customizer panel to be rendered.
		 *
		 * @since 4.0.0
		 */
		do_action( "customize_render_panel_{$this->id}" );

	}

	/**
	 * Render the screen container, and then its contents.
	 *
	 * @access protected
	 */
	protected function render() {
		$classes = 'accordion-section control-section control-panel control-panel-' . $this->type;
		?>
		<li id="accordion-panel-<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
			<h3 class="accordion-section-title" tabindex="0">
				<?php echo esc_html( $this->title ); ?>
				<span class="panel-reader-text"><?php _e( 'Press return or enter to open this panel' ); ?></span>
			</h3>
			<ul class="accordion-sub-container control-panel-content">
				<?php $this->render_content(); ?>
			</ul>
		</li>
		<?php
	}

	/**
	 * Render the sections that have been added to the panel.
	 *
	 * @since 4.1.0
	 * @access protected
	 */
	protected function render_content() {
		?>
		<li class="panel-meta accordion-section control-section<?php if ( empty( $this->description ) ) { echo ' cannot-expand'; } ?>">
			<div class="accordion-section-title" tabindex="0">
				<span class="preview-notice"><?php
					/* translators: %s is the site/panel title in the Customizer */
					echo sprintf( __( 'You are customizing %s' ), '<strong class="panel-title">' . esc_html( $this->title ) . '</strong>' );
				?></span>
			</div>
			<?php if ( ! empty( $this->description ) ) : ?>
				<div class="accordion-section-content description">
					<?php echo $this->description; ?>
				</div>
			<?php endif; ?>
		</li>
		<?php
	}
}
