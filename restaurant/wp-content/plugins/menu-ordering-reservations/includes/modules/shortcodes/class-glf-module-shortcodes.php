<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Glf_Module_Shortcodes' ) ) {

	/**
	 * GloriaFood Gutenberg Blocks implementation
	 *
	 * @since 1.1.0
	 */
	class Glf_Module_Shortcodes {

		private static $_instance = null;

		public function __construct() {
			add_shortcode( 'restaurant-menu-and-ordering', array( 'Glf_Module_Shortcodes', 'add_ordering_shortcode' ) );
			add_shortcode( 'restaurant-reservations', array( 'Glf_Module_Shortcodes', 'add_reservations_shortcode' ) );
			add_shortcode( 'restaurant-full-menu', array( 'Glf_Module_Shortcodes', 'add_menu_shortcode' ) );
		}

		public static function add_ordering_shortcode( $atts ) {
			return self::add_shortcode( 'ordering', $atts );
		}

		public static function add_reservations_shortcode( $atts ) {
			return self::add_shortcode( 'reservations', $atts );
		}

		public static function add_shortcode( $type, $atts ) {
			extract( shortcode_atts( array(
				'ruid' => 'ruid'
			), $atts ) );


			if ( ! isset( $atts[ 'ruid' ] ) ) {
				$atts[ 'ruid' ] = '';
			}
			if ( ! isset( $atts[ 'rid' ] ) ) {
				$atts[ 'rid' ] = '';
			}


			$label     = '';
			$extraAttr = '';
			$extraCss  = '';
			switch ( $type ) {
				case 'ordering':
					$label = 'See MENU & Order';
					break;
				case 'reservations':
					$label     = 'Table Reservation';
					$extraAttr = 'data-glf-reservation="true"';
					$extraCss  = 'reservation';
					break;
			}
			// output all location labels
			// display only the selected label by using data-location attribute
			$labelArgs = array(
				'type'      => $type,
				'ruid'      => $atts[ 'ruid' ],
				'rid'       => $atts[ 'rid' ],
				'extraAttr' => $extraAttr,
				'extraCss'  => $extraCss,
			);

			if ( isset( $atts[ 'class' ] ) ) {
				$labelArgs[ 'class' ] = $atts[ 'class' ];
			}
			$custom_labels = Glf_Utils::get_all_locations_labels( $labelArgs, Glf_Utils::$_GLF->custom_css );
			$inline_css    = '';

			if ( ! empty( $custom_labels ) && is_array( $custom_labels ) ) {
				$label      = $custom_labels[ 'labels' ];
				$inline_css = '<style>' . ' .glf-' . $type . '-location > span{display:none;} ' . $custom_labels[ 'css' ] . '</style>';
			}


			if ( isset( $atts[ 'class' ] ) ) { // basic || custom
				$html = '<a href="#"><span class="glf-button-default ' . $atts[ 'class' ] . '" data-glf-cuid="" data-glf-ruid="' . $atts[ 'ruid' ] . '" ' . $extraAttr . '>' . $label . '</span></a>';
			} else {
				//$html = '<span class="glf-' . $type . '-location glf-button-' . $type . '-label glf-button-default glf-button ' . $extraCss . '" style=\'' . $customCss . '\'  data-glf-cuid="" data-glf-ruid="' . $atts['ruid'] . '" ' . $extraAttr . ' data-location="' . $atts['rid'] . '">' . $label . '</span>';
				$html = $label;
			}
			$html .= '<script src="https://www.fbgcdn.com/embedder/js/ewm2.js" defer async ></script>';
			$html .= $inline_css;

			return $html;
		}

		public static function add_menu_shortcode( $atts ) {
			extract( shortcode_atts( array( 'ruid' => '' ), $atts ) );

			if ( empty( $atts[ 'ruid' ] ) ) {
				return '';
			}
			$restaurant_menu = self::glf_mor_restaurant_menu( $atts[ 'ruid' ] );
			if ( ! $restaurant_menu ) {
				return '';
			}
			$html = '';
			if ( ! empty( $restaurant_menu->categories ) ) {
				foreach ( $restaurant_menu->categories as $cat_index => $category ) {
					if ( ! empty( $category->items ) ) {
						$html .= '<div class="glf-mor-restaurant-menu-category"><h3>' . $category->name . '</h3>';

						foreach ( $category->items as $item_index => $item ) {
							$picture = $item->picture ? '<picture>
                                            <img class="" alt="' . $item->name . '" src="' . $item->picture . '">
                                        </picture>' : '';
							$html    .= '<hr>
                            <div class="glf-mor-restaurant-menu-item"><div class="glf-mor-restaurant-menu-item-inner">' . $picture . '
                            <div style="width: 100%">
                                <div class="glf-mor-restaurant-menu-item-header">
                                            <h5 class="glf-mor-restaurant-menu-item-name">' . $item->name . '</h5>
                                            <div class="glf-mor-restaurant-menu-item-price" data-price="' . $item->price . '" data-currency="' . $restaurant_menu->currency . '">' . $item->price . ' ' . $restaurant_menu->currency . '</div>
                                </div>
                                ' . ( empty( $item->description ) ? '' : '<div class="glf-mor-restaurant-menu-item-description">' . $item->description . '</div>' ) . '
                            </div>
                        </div></div>';
						}

						$html .= '<hr></div>';
					}
				}
			}

			$locale = false;

			$restaurant_data_obj = Glf_Utils::glf_more_restaurant_data();

			if ( $restaurant_data_obj ) {
				foreach ( $restaurant_data_obj->restaurants as $restaurant ) {
					if ( $restaurant->uid === $atts[ 'ruid' ] ) {
						$locale = $restaurant->language_code . '-' . $restaurant->country_code;
					}
				}
			}


			if ( ! empty( $html ) ) {
				$html = '<div class="glf-mor-restaurant-menu-wrapper">' . $html . '</div>
            <script type="text/javascript">
                if (typeof jQuery != "undefined") {
                    jQuery(document).ready(function() {
                   jQuery(".glf-mor-restaurant-menu-item-price").each(function() {
                        const el=jQuery(this);
                        const price=parseFloat(el.data("price"));
                        const currency=el.data("currency");
                    
                        el.html(price.toLocaleString(' . ( $locale ? '\'' . $locale . '\'' : 'navigator.language' ) . ',{style:"currency",currency:currency}))
                    });
               });
                    }
            </script>';
			}

			return $html;
		}

		public static function glf_mor_restaurant_menu( $restaurantUid, $forceRefresh = false ) {
			if ( $forceRefresh ) {
				$restaurant_menu = self::glf_mor_restaurant_menu_get_and_cache( $restaurantUid );
			} else {
				$restaurant_menu = get_transient( 'glf_mor_restaurant_menu' . $restaurantUid );

				if ( false === $restaurant_menu ) {
					$restaurant_menu = self::glf_mor_restaurant_menu_get_and_cache( $restaurantUid );
				}
			}

			return $restaurant_menu;
		}

		public static function glf_mor_restaurant_menu_get_and_cache( $restaurantUid, $cacheTime = 3600 ) {
			$restaurant_menu = Glf_Utils::$_GLF->glf_mor_api_call( "/restaurant/$restaurantUid/menu?active=true&pictures=true" );
			set_transient( 'glf_mor_restaurant_menu' . $restaurantUid, $restaurant_menu, $cacheTime );

			return $restaurant_menu;
		}
	}

	new Glf_Module_Shortcodes();
}