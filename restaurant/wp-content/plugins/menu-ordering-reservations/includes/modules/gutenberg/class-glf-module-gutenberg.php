<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Glf_Module_Gutenberg' ) ) {

	/**
	 * GloriaFood Gutenberg Blocks implementation
	 *
	 * @since 1.1.0
	 */
	class Glf_Module_Gutenberg {


		public function __construct() {
			add_action( 'init', array( $this, 'gloria_block_init' ) );
			add_action( 'init', array( $this, 'menu_ordering_reservations_set_script_translations' ) );
			// Hook scripts function into block editor hook
			add_action( 'enqueue_block_assets', array( $this, 'jsforwpblocks_scripts' ) );
		}

		public function gloria_block_init() {

			// Skip block registration if Gutenberg is not enabled/merged.
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			$restaurant_data_obj = Glf_Utils::glf_more_restaurant_data();
			if ( is_object( $restaurant_data_obj ) && isset( $restaurant_data_obj->restaurants ) ) {
				$restaurant_data_obj->restaurants = Glf_Utils::glf_get_sorted_restaurants( $restaurant_data_obj->restaurants );
				$ruid                             = [];
				if ( count( $restaurant_data_obj->restaurants ) !== 1 ) {
					foreach ( $restaurant_data_obj->restaurants as $restaurant ) {
						$addChainName = $restaurant->is_chain ? '[' . ucwords( $restaurant->company_name ) . '] ' : '';
						$ruid[]       = array( 'name' => $addChainName . $restaurant->name, 'uid' => $restaurant->uid );
					}
				} else {
					$ruid[] = array( 'name' => $restaurant_data_obj->restaurants[ 0 ]->name, 'uid' => $restaurant_data_obj->restaurants[ 0 ]->uid );
				}

				$dependencies          = array(
					'wp-blocks',
					'wp-i18n',
					'wp-element',
					'wp-components',
				);
				$glf_namespace         = 'menu-ordering-reservations/';
				$dir                   = GLF_PLUGIN_DIR . 'includes/modules/gutenberg/assets/js/';
				$url                   = GLF_PLUGIN_URL . 'includes/modules/gutenberg/assets/js/';
				$block_args_attributes = array(
					'ruid' => [
						'type'    => 'string',
						'default' => $restaurant_data_obj->restaurants[ 0 ]->uid
					],
				);
				$glf_blocks            = array(
					'menu-ordering-editor' => array(
						'script' => array(
							'handle'   => 'menu-ordering-editor',
							'src'      => 'menu-ordering/index.js',
							'deps'     => array_merge( $dependencies, [ 'wp-editor' ] ),
							'localize' => array(
								'obj_name' => 'js_data',
								'obj_data' => $ruid,
							),
						),
						'block'  => array(
							'name' => 'menu-ordering',
							'args' => array(
								'editor_script'   => 'menu-ordering-editor',
								'render_callback' => array( $this, 'menu_ordering_block_render' ),
								'attributes'      => $block_args_attributes,
							),
						),
					),
					'reservations-editor'  => array(
						'script' => array(
							'handle' => 'reservations-editor',
							'src'    => 'reservations/index.js',
							'deps'   => $dependencies,
						),
						'block'  => array(
							'name' => 'reservations',
							'args' => array(
								'editor_script'   => 'reservations-editor',
								'render_callback' => array( $this, 'menu_reservations_block_render' ),
								'attributes'      => $block_args_attributes,
							),
						),
					),
					'food-menu-editor'     => array(
						'script' => array(
							'handle' => 'food-menu-editor',
							'src'    => 'food-menu/index.js',
							'deps'   => $dependencies,
						),
						'block'  => array(
							'name' => 'food-menu',
							'args' => array(
								'editor_script'   => 'food-menu-editor',
								'render_callback' => array( $this, 'food_menu_block_render' ),
								'attributes'      => $block_args_attributes,
							),
						),
					),
				);

				foreach ( $glf_blocks as $key => $block ) {
					$script  = $block[ 'script' ];
					$filem   = $dir . $script[ 'src' ];
					$version = file_exists( $filem ) ? filemtime( (string) $filem ) : false;
					wp_register_script(
						$script[ 'handle' ],
						$url . $script[ 'src' ],
						$script[ 'deps' ],
						$version
					);

					if ( isset( $script[ 'localize' ] ) ) {
						$localize = $script[ 'localize' ];
						wp_localize_script(
							$key,
							$localize[ 'obj_name' ],
							$localize[ 'obj_data' ]
						);
					}

					$the_block = $block[ 'block' ];
					register_block_type(
						$glf_namespace . $the_block[ 'name' ],
						$the_block[ 'args' ]
					);
				}
				$ruid = "";
			}
		}

		public function menu_ordering_block_render( $atts ) {
			return Glf_Module_Shortcodes::add_shortcode( 'ordering', $atts );

		}

		public function menu_reservations_block_render( $atts ) {
			return Glf_Module_Shortcodes::add_shortcode( 'reservations', $atts );
		}

		public function food_menu_block_render( $atts ) {
			return Glf_Module_Shortcodes::add_menu_shortcode( $atts );
		}

		/**
		 * Enqueue block editor JavaScript and CSS
		 */
		public function jsforwpblocks_scripts() {
			wp_enqueue_style( 'online-css', 'https://www.fbgcdn.com/embedder/css/order-online.css' );
			// Enqueue frontend and editor JS
			$sharedBlockPath = 'https://www.fbgcdn.com/embedder/js/ewm2.js';
			wp_enqueue_script(
				'jsforwp-blocks-frontend-js',
				$sharedBlockPath
			);
		}

		public function menu_ordering_reservations_set_script_translations() {
			wp_set_script_translations( 'menu-ordering-editor', 'menu-ordering-reservations', GLF_PLUGIN_DIR . 'languages' );
		}
	}

	new Glf_Module_Gutenberg();
}