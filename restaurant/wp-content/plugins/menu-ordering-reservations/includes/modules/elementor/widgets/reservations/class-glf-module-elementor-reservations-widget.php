<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Glf_Module_Elementor_Reservations_Widget' ) ) {

	/**
	 * GloriaFood Elementor Widget implementation
	 *
	 * @since 1.5.0
	 */
	class Glf_Module_Elementor_Reservations_Widget extends \Elementor\Widget_Base {
		/**
		 * Set widget name
		 *
		 * Used by elementor to identify the widget
		 */
		public function get_name() {
			return 'glf_elementor_reservations';
		}

		/**
		 * Set widget title
		 *
		 * Visual widget label on the builder
		 */
		public function get_title() {
			return __( 'Reservations', 'menu-ordering-reservations' );
		}

		/**
		 * Set widget icon
		 *
		 * Visual widget icon css class
		 */
		public function get_icon() {
			return 'glf-elementor-reservations-widget-icon';
		}

		/**
		 * Set widget keywords
		 *
		 * Widget keywords used for searching
		 */
		public function get_keywords() {
			return [ 'restaurant', 'reservations', 'gloria', 'food' ];
		}

		/**
		 * Set widget category
		 *
		 * We are creating our own custom category
		 *
		 * Default values: 'base' , 'general'
		 */
		public function get_categories() {
			return [ 'gloria-food' ];
		}

		/**
		 * Register Reservations widget controls.
		 *
		 * Adds a select field for location and an input field for the custom css class.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function _register_controls() {

			$this->start_controls_section(
				'content_section',
				[
					'label' => __( 'Settings', 'menu-ordering-reservations' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			$select = Glf_Utils::glf_get_restaurants_dropdown_elementor();
			$this->add_control(
				'glf_ruid',
				[
					'label'   => __( 'Select restaurant', 'plugin-name' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => $select[ 'default' ],
					'options' => $select[ 'options' ],
				]
			);
			$this->add_control(
				'glf_class',
				[
					'label'   => __( 'Additional CSS class(es)', 'plugin-domain' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => ''
				]
			);

			$this->end_controls_section();

		}

		/**
		 * Render Reservations Button output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function render() {

			$settings = $this->get_settings_for_display();
			$atts     = array( 'ruid' => $settings[ 'glf_ruid' ] );

			if ( ! empty( $settings[ 'glf_class' ] ) ) {
				$atts[ 'class' ] = $settings[ 'glf_class' ];
			}

			echo Glf_Module_Shortcodes::add_shortcode( 'reservations', $atts );

		}
	}
}