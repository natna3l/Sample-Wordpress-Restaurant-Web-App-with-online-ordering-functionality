<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * This class contains some utilities needed for the plugin.
 *
 * @since     1.1.0
 */
if ( ! class_exists( 'Glf_Utils' ) ) {

	class Glf_Utils {

		public static $_GLF = null;
		public static $_mor_restaurant_data = null;
		public static $_wp_options_data = null;


		public static function glf_save_user_data( $args ) {
			self::$_GLF->save_user_data( $args );
		}


		public static function glf_mor_remote_call( $url, $mode ) {

			switch ( $mode ) {
				case 'login':
					$action = 'login3';
					break;
				case 'forgot_password':
					$action = 'user/password_reset';
					break;
				default:
					$action = 'register';
			};

			$response = wp_remote_post( $url . $action, array(
					'method'  => 'POST',
					'headers' => array(),
					'body'    => $_POST,
				)
			);

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				die( "Something went wrong: $error_message" );
			}

			return json_decode( $response[ 'body' ] );
		}

		public static function iframe_src( $section ) {
			$params = array( 'parent_window' => 'wordpress' );

			switch ( $section ) {
				case 'menu':
					$params[ 'r' ]                    = 'app.admin.setup.menu_app.menu_editor';
					$params[ 'hide_top_menu' ]        = 'true';
					$params[ 'hide_left_menu' ]       = 'true';
					$params[ 'hide_left_navigation' ] = 'true';
					break;

				case 'setup':
					$params[ 'r' ]              = 'app.admin_ftu.setup';
					$params[ 'hide_top_menu' ]  = 'true';
					$params[ 'hide_left_menu' ] = 'true';
					break;


				default:
					break;
			}

			$src = self::$_GLF->get_glf_mor_token();
			$src .= strpos( $src, '?' ) ? '&' : '?1';

			foreach ( $params as $key => $value ) {
				$src .= "&$key=$value";
			}

			return $src;
		}

		public static function glf_get_restaurants() {
			return self::$_GLF->restaurants;
		}

		public static function glf_more_restaurant_data( $update = 'false', $return = 'true' ) {
			if ( self::$_mor_restaurant_data === null || $update === 'true' ) {
				self::$_mor_restaurant_data = get_option( 'glf_mor_restaurant_data' );
			}

			return ( $return === 'true' ) ? self::$_mor_restaurant_data : '';
		}

		public static function glf_wp_options_data( $update = 'false' ) {
			if ( self::$_wp_options_data === null || $update === 'true' ) {
				self::$_wp_options_data = get_option( 'glf_wordpress_options' );
			}

			return self::$_wp_options_data;
		}

		public static function glf_require_once( $file ) {
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}

		public static function glf_include( $file ) {
			if ( file_exists( $file ) ) {
				include $file;
			}
		}

		public static function glf_url_ends_with( $haystack, $needle ) {
			$length = strlen( $needle );
			if ( $length === 0 ) {
				return true;
			}

			return ( substr( $haystack, - $length ) === $needle );
		}

		public static function glf_mor_get_shortcode( $ruid, $type, $useCustomCss = false, $class = "" ) {
			$code = '[';

			$code .= $type === 'reservations' ? 'restaurant-reservations' : 'restaurant-menu-and-ordering';
			$code .= ' ruid="' . $ruid . '"';

			if ( $useCustomCss ) {
				$code .= ' class="' . $class . '"';
			}

			$code .= ']';

			return $code;
		}

		public static function glf_custom_css_check_and_set_defaults( $default_css ) {
			$restaurant_data_obj = self::glf_more_restaurant_data();
			if ( is_object( $restaurant_data_obj ) && ! isset( $restaurant_data_obj->restaurants ) ) {
				return;
			}
			/*
			 * Checking to see if WordPress options exist.
			 * If it doesn't exist we create a new Object and add our custom_css key
			 * */
			$glf_wordpress_options = self::glf_wp_options_data();
			if ( ! $glf_wordpress_options ) {
				$glf_wordpress_options = new stdClass();
				/*
				 * Backward compatibility check
				 * Use the 'glf_mor_restaurant_data->location_custom_css' data if it exists
				 * */
				$glf_wordpress_options->custom_css_by_location = ( isset( $restaurant_data_obj->location_custom_css ) ? $restaurant_data_obj->location_custom_css : array() );
			}
			// use the new $glf_wordpress_options->custom_css_by_location to update the default value
			$update_location_custom_css = array();
			foreach ( $restaurant_data_obj->restaurants as $restaurant ) {
				if ( ! isset( $glf_wordpress_options->custom_css_by_location[ $restaurant->uid ] ) ) {
					$custom_css = $default_css;
				} else {
					$custom_css = $glf_wordpress_options->custom_css_by_location[ $restaurant->uid ];
				}
				$update_location_custom_css[ $restaurant->uid ] = $custom_css;
			}
			$glf_wordpress_options->custom_css_by_location = $update_location_custom_css;
			update_option( 'glf_wordpress_options', $glf_wordpress_options );
		}

		/**
		 *
		 * Get all locations or just one location custom_css
		 *
		 * @param string $location_uid
		 *
		 * @return array|string|null
		 */
		public static function glf_get_locations_custom_css( $location_uid = '' ) {
			$glf_wordpress_options = self::glf_wp_options_data();
			if ( ! $glf_wordpress_options ) {
				return null;
			}
			$location_uid_custom_css = ( isset( $glf_wordpress_options->custom_css_by_location ) ? $glf_wordpress_options->custom_css_by_location : '' );
			if ( is_array( $location_uid_custom_css ) ) {
				if ( ! empty( $location_uid ) ) {
					$location_uid_custom_css = $location_uid_custom_css[ $location_uid ];
				}
			}

			return $location_uid_custom_css;
		}

		public static function get_all_locations_labels( $atts, $custom_css ) {
			$type                     = $atts[ 'type' ];
			$location                 = $atts[ 'ruid' ];
			$all_locations_custom_css = self::glf_get_locations_custom_css();
			if ( is_null( $all_locations_custom_css ) ) {
				self::glf_custom_css_check_and_set_defaults( $custom_css );
				$all_locations_custom_css = self::glf_get_locations_custom_css();
			}

			if ( is_array( $all_locations_custom_css ) ) {
				$labels = array( 'labels' => '', 'css' => '', 'customCSS' => '' );
				foreach ( $all_locations_custom_css as $location_ruid => $locations_custom_css ) {
					$label                 = ( $type === 'ordering' ? 'See MENU & Order' : 'Table Reservation' );
					$labels[ 'customCSS' ] = '';
					if ( isset ( $locations_custom_css[ $type ] ) ) {
						$label                 = $locations_custom_css[ $type ][ 'text' ];
						$labels[ 'customCSS' ] = self::get_custom_css_props_style( $locations_custom_css[ $type ] );
					}
					if ( isset( $atts[ 'class' ] ) ) {
						$labels_html = $label;
					} else {
						$labels_html = '<span class="glf-button-default glf-button ' . $atts[ 'extraCss' ] . '" style=\'' . $labels[ 'customCSS' ] . '\'  data-glf-cuid="" data-glf-ruid="' . $atts[ 'ruid' ] . '" ' . $atts[ 'extraAttr' ] . ' data-location="' . $location_ruid . '">' . $label . '</span>';
					}

					if ( empty( $location ) ) {
						$labels[ 'labels' ] .= $labels_html;
						$labels[ 'css' ]    .= ' .glf-' . $type . '-location' . '[data-location="' . $location_ruid . '"] > span[data-location="' . $location_ruid . '"]{ display:block; }';
					} else {
						if ( $location === $location_ruid ) {
							$labels[ 'labels' ] = $labels_html;
						}
					}
				}
			}

			return $labels;
		}

		/**
		 *
		 * Returns all custom css properties that exist.
		 *
		 * @param string $locations_custom_css
		 */
		public static function get_custom_css_props_style( $locations_custom_css ) {
			$customCSS = '';
			foreach ( $locations_custom_css as $key => $value ) {
				if ( $key !== 'text' && $key !== 'type' ) {
					$customCSS .= $key . ':' . $value . ( $key === 'color' ? ' !important; ' : '; ' );

				}
			}

			return $customCSS;
		}

		/**
		 *
		 * Set all locations or just one location custom_css
		 *
		 * @param array $custom_css
		 * @param string $location_uid
		 */
		public static function glf_set_locations_custom_css( $custom_css, $location_uid = '' ) {
			$glf_wordpress_options = self::glf_wp_options_data();
			if ( is_array( $custom_css ) ) {
				if ( ! empty( $location_uid ) ) {
					$glf_wordpress_options->custom_css_by_location[ $location_uid ] = $custom_css;
				} else {
					$glf_wordpress_options->custom_css_by_location = $custom_css;
				}
			}
			update_option( 'glf_wordpress_options', $glf_wordpress_options );
		}

		public static function glf_mor_get_restaurants() {
			$restaurant_data_obj = self::glf_more_restaurant_data();

			return isset( $restaurant_data_obj->restaurants ) ? $restaurant_data_obj->restaurants : null;
		}

		public static function glf_database_option_operation( $action, $option_name, $value = '' ) {
			$result = '';
			if ( $action === 'get' ) {
				$result = get_option( $option_name, false );
			} else {
				if ( $action === 'delete' ) {
					$result = delete_option( $option_name );
				} else {
					$action        = ( $action === 'update' && ! get_option( $option_name ) ) ? 'add' : $action;
					$function_name = $action . '_option';
					if ( function_exists( $function_name ) ) {
						$result = $function_name( $option_name, $value );
					}
				}
			}

			return $result;
		}

		// Sort restaurants and chains alphabetically by company_name.
		// Sort chain locations by name
		public static function glf_get_sorted_restaurants( $args = '' ) {
			$restaurants = empty( $args ) ? self::glf_get_restaurants() : $args;
			usort( $restaurants, function ( $x, $y ) {
				//compare all restaurants by company_name
				$test = strtolower( $x->company_name ) > strtolower( $y->company_name );

				//whenever two have the same company_name we sort the locations of that chain by location name
				if ( strtolower( $x->company_name ) === strtolower( $y->company_name ) ) {
					$test = strtolower( $x->name ) > strtolower( $y->name );
				}

				return $test;
			} );

			return $restaurants;
		}

		// output the select dropdown html for admin and publishing
		public static function glf_get_restaurants_dropdown( $id, $name, $property, $default_value, $onchange ) {
			?>
            <select name="<?php echo $name; ?>" id="<?php echo $id; ?>" onchange="<?php echo $onchange; ?>()">
				<?php
				foreach ( self::glf_get_sorted_restaurants() as $restaurant ) {
					$addChainName = $restaurant->is_chain ? '[' . ucwords( $restaurant->company_name ) . '] ' : '';
					$add_selected = '';
					if ( ! empty( $default_value ) && $default_value === $restaurant->uid ) {
						$add_selected = 'selected';
					}
					?>
                    <option value="<?php echo $restaurant->{$property}; ?>" <?= $add_selected; ?> data-uid="<?php echo $restaurant->uid ?>"><?php echo $addChainName . $restaurant->name; ?></option>
					<?php
				} ?>
            </select>
			<?php
		}

		// output the select dropdown html for admin and publishing
		public static function glf_get_restaurants_dropdown_elementor( $default_value = '' ) {
			$result = array(
				'default' => $default_value,
				'options' => array()
			);
			foreach ( self::glf_get_sorted_restaurants() as $restaurant ) {
				if ( $default_value === '' ) {
					$result[ 'default' ] = $restaurant->uid;
					$default_value       = $restaurant->uid;
				}
				$result[ 'options' ][ $restaurant->uid ] = ( $restaurant->is_chain ? '[' . ucwords( $restaurant->company_name ) . '] ' : '' ) . $restaurant->name;
			}

			return $result;
		}

		public static function var_dump( $value ) {
			echo '<pre style="left: 200px; position: relative;">';
			var_dump( $value );
			echo '</pre>';
		}

	}
}