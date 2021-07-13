<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Glf_Module_Classic_Editor_MCE' ) ) {
	class Glf_Module_Classic_Editor_MCE {
		public function __construct() {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				add_action( 'wp_ajax_restaurant_system_insert_dialog', array( $this, 'mce_insert_dialog' ) );
			}
		}

		public function mce_insert_dialog() {
			Glf_Utils::glf_require_once( __DIR__ . '/mce/media-button.php' );
		}
	}

	new Glf_Module_Classic_Editor_MCE();
}
