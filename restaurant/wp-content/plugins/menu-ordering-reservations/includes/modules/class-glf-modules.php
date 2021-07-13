<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Glf_Modules' ) ) {
	class Glf_Modules {
		public function __construct() {
			foreach ( glob( __DIR__ . "/*", GLOB_ONLYDIR ) as $path ) {
				$module = $path . '/' . 'class-glf-module-' . basename( $path ) . '.php';
				Glf_Utils::glf_require_once( $module );
			}
		}
	}

	new Glf_Modules();
}
