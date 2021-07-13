<?php
/** If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Glf_Module_Widgets' ) ) {

	/**
	 * GloriaFood Widget/s Loader Class
	 *
	 * @since 1.1.0
	 */
	class Glf_Module_Widgets {

		public function __construct() {

			Glf_Utils::glf_require_once( GLF_PLUGIN_DIR . 'includes/modules/widgets/class-glf-mor-widget.php' );
			add_action( 'widgets_init', array( $this, 'widget_init' ) );
		}

		public function widget_init() {
			register_widget( 'Glf_Mor_Widget' );
		}
	}

	new Glf_Module_Widgets();
}