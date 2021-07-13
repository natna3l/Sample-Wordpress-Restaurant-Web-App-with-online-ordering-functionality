<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Glf_Module_Elementor' ) ) {

	/**
	 * GloriaFood Elementor Module implementation
	 *
	 * @since 1.5.0
	 */
	class Glf_Module_Elementor {

		public function __construct() {

			add_action( 'elementor/editor/wp_head', array( $this, 'elementor_panel_style' ) );
			add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_categories' ) );
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'glf_elementor_restaurant_widget' ) );

		}

		public function add_elementor_widget_categories( $elements_manager ) {

			$elements_manager->add_category(
				'gloria-food',
				[
					'title' => __( 'Gloria Food - Restaurant', 'menu-ordering-reservations' )
				]
			);
			
		}

		public function glf_elementor_restaurant_widget() {

			require_once 'widgets/menu-ordering/class-glf-module-elementor-ordering-widget.php';
			require_once 'widgets/reservations/class-glf-module-elementor-reservations-widget.php';
			require_once 'widgets/food-menu/class-glf-module-elementor-food-menu-widget.php';

			Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Glf_Module_Elementor_Ordering_Widget() );
			Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Glf_Module_Elementor_Reservations_Widget() );
			Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Glf_Module_Elementor_Food_Menu_Widget() );

		}

		public function elementor_panel_style() {
			wp_enqueue_style( 'glf_elementor_panel_style', plugins_url( 'assets/css/glf-elementor-widget.css', __FILE__ ), false, Glf_Utils::$_GLF->version );
		}
	}

	new Glf_Module_Elementor();
}