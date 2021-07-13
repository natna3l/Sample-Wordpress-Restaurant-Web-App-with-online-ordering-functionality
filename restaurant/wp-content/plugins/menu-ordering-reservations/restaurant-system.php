<?php
/*
  Plugin Name: Menu - Ordering - Reservations
  Plugin URI: https://www.gloriafood.com/wordpress-restaurant-plugin
  Description: This plugin is all you need to turn your restaurant website into an online business. Using a simple and friendly interface you get a restaurant menu, online food ordering and restaurant booking system. All free, no fees, no hidden costs, no commissions - for unlimited food orders and restaurant reservations.

  Version: 1.5.1
  Author: GloriaFood
  Author URI: https://www.gloriafood.com/
  License: GPLv2+
  Text Domain: menu-ordering-reservations

  @package  RestaurantSystem
  @category Core
  @author   GLOBALFOOD
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! defined( 'GLF_PHP_COMPARE' ) ) {
	define( 'GLF_PHP_COMPARE', version_compare( PHP_VERSION, '5.3', '<' ) );
}

if ( ! defined( 'GLF_PLUGIN_DIR' ) ) {
	define( 'GLF_PLUGIN_DIR', trailingslashit( ( GLF_PHP_COMPARE ? dirname( __FILE__ ) : __DIR__ ) ) );
}

if ( ! defined( 'GLF_PLUGIN_URL' ) ) {
	define( 'GLF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'GLF_BASE_URL' ) ) {
	define( 'GLF_BASE_URL', 'https://www.restaurantlogin.com/' );
}
if ( ! defined( 'GLF_API_URL' ) ) {
	define( 'GLF_API_URL', GLF_BASE_URL . 'api/' );
}

class GLF_Restaurant_System {
	var $version = '1.5.1',
		$api_token = null,
		$custom_css = null,
		$auth_domain = null,
		$auth_token = null,
		$restaurants = null,
		$user = null;

	private static $_instance = null;

	// Constructor
	private function __construct() {
		if ( ! class_exists( 'Glf_Utils' ) ) {
			require_once GLF_PLUGIN_DIR . '/includes/utils/class-glf-utils.php';
		}
		$this->glf_system_config();
		if ( wp_doing_ajax() ) {
			$this->glf_system_ajax();
		} else {
			if ( is_admin() ) {
				$this->glf_system_admin();
			} else {
				$this->glf_system_frontend();
			}
		}
		$this->glf_system_init();
	}

	public function glf_system_config() {
		$this->admin_language = substr( get_user_locale(), 0, 2 );
		$this->load_user_data();

		Glf_Utils::glf_require_once( GLF_PLUGIN_DIR . 'includes/modules/class-glf-modules.php' );
	}

	public function glf_system_ajax() {
		add_action( 'wp_ajax_restaurant_system_customize_button', array( $this, 'customize_button_dialog' ) );
		add_action( 'wp_ajax_glf_set_default_location', array( $this, 'glf_set_default_location' ) );
	}
	//store the admin dropdonw location selected.
	// Value to be used to pre-select the location in publishing
	public function glf_set_default_location() {
		$location                                = isset( $_POST[ 'location' ] ) ? (string) $_POST[ 'location' ] : '';
		$glf_wordpress_options                   = Glf_Utils::glf_wp_options_data();
		$glf_wordpress_options->default_location = $location;
		Glf_Utils::glf_database_option_operation( 'update', 'glf_wordpress_options', $glf_wordpress_options );
		echo json_encode( array( 'message' => 'done', 'post' => $_POST ) );
		exit;
	}

	public function glf_system_admin() {
		add_action( 'admin_menu', array( $this, 'glf_mor_add_menu' ) );
		add_action( 'admin_bar_menu', array( $this, 'glf_mor_add_admin_bar_menu' ), 99 );
		add_action( 'media_buttons', array( $this, 'add_ordering_media_button' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_media_scripts' ) );
	}

	public function glf_system_frontend() {
		add_action( 'wp_print_styles', array( $this, 'add_public_media_scripts' ) );
	}

	public function glf_system_init() {
		register_activation_hook( __FILE__, array( 'GLF_Restaurant_System', 'glf_mor_install' ) );
		register_uninstall_hook( __FILE__, array( 'GLF_Restaurant_System', 'glf_mor_uninstall' ) );

		add_action( 'wpmu_new_blog', array( 'GLF_Restaurant_System', 'glf_mor_new_blog' ), 10, 6 );
	}

	public function generate_installation_id() {
		return wp_generate_uuid4();
	}

	public function glf_mor_add_admin_bar_menu() {
		global $wp_admin_bar, $pagenow;

		if ( $pagenow != 'admin.php' || ! $_GET[ 'page' ] || strpos( $_GET[ 'page' ], 'glf-' ) != 0 ) {
			return;
		}

		if ( ! isset( $this->restaurants[ 0 ] ) ) {
			return;
		}
		$menus = array(
			array(
				'id'    => 'glf-restaurant',
				'title' => '<img src="' . plugins_url( 'assets/images/logo.png', __FILE__ ) . '"> Restaurant Admin',
				'href'  => $this->get_glf_mor_token(),
				'meta'  => array(
					'target' => 'blank'
				)
			)
		);

		foreach ( apply_filters( 'render_webmaster_menu', $menus ) as $menu ) {
			$wp_admin_bar->add_menu( $menu );
		}
	}

	/*
	  * Actions perform at loading of admin menu
	*/
	public function glf_mor_add_menu() {
		$title = 'Menu - Ordering - Reservations';
		if ( current_user_can( 'manage_options' ) ) {
			add_menu_page( 'Menu - Ordering - Reservations', $title, 'manage_options', 'glf-admin', array(
				$this,
				'glf_mor_page_file_path'
			), plugins_url( 'assets/images/logo.png', __FILE__ ), '2.2.9' );

			add_submenu_page( 'glf-admin', $title . ' Dashboard', 'Dashboard', 'manage_options', 'glf-admin', array(
				$this,
				'glf_mor_page_file_path'
			) );

			$hook = add_submenu_page( 'glf-admin', $title . ' Publishing', 'Publishing', 'manage_options', 'glf-publishing', array(
				$this,
				'glf_mor_page_file_path'
			) );

			add_action( "load-$hook", array( $this, 'publishing_help' ) );

			add_submenu_page( 'glf-admin', $title . ' Extras', 'Extras', 'manage_options', 'glf-extras', array(
				$this,
				'glf_mor_page_file_path'
			) );

			add_submenu_page( 'glf-admin', $title . ' Partner Program', 'Partner Program', 'manage_options', 'glf-partner', array(
				$this,
				'glf_mor_page_file_path'
			) );

			add_options_page( $title . ' Options', $title, 'manage_options', 'glf-options', array( $this, 'glf_mor_page_file_path' ) );
		}
	}


	public function add_ordering_media_button() {
		?>
        <a id="glf-ordering" class="button thickbox" onclick="glf_mor_showThickBox('restaurant_system_insert_dialog')">
            <img src="<?= plugins_url( 'assets/images/logo.png', __FILE__ ) ?>"> Menu - Ordering - Reservations
        </a>
		<?php
	}


	public function customize_button_dialog() {
		Glf_Utils::glf_include( GLF_PLUGIN_DIR . 'includes/admin/pages/publishing/customize-button.php' );
	}

	public function publishing_help() {
		$current_screen = get_current_screen();

		// Screen Content
		if ( current_user_can( 'manage_options' ) ) {
			$help_sections = array(
				'customize'  => __( 'Customize the buttons', 'menu-ordering-reservations' ),
				'pages'      => __( 'Add buttons to pages', 'menu-ordering-reservations' ),
				'navigation' => __( 'Add buttons to the navigation', 'menu-ordering-reservations' ),
				'widget'     => __( 'Add buttons to sidebar or footer', 'menu-ordering-reservations' )
			);

			foreach ( $help_sections as $key => $title ) {
				ob_start();
				require( GLF_PLUGIN_DIR . 'includes/admin/pages/publishing/help/' . $key . '.php' );
				$content = ob_get_contents();
				ob_end_clean();

				$current_screen->add_help_tab(
					array(
						'id'      => $key,
						'title'   => $title,
						'content' => '<div class="glf-help-section">' . $content . '</div>'
					)
				);
			}

		}

		// Help Sidebar
		$current_screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'menu-ordering-reservations' ) . '</strong></p>' .
			'<p><a href="https://www.gloriafood.com/restaurant-ideas/add-online-ordering-button-wordpress" target="_blank">' . __( 'See the complete guide', 'menu-ordering-reservations' ) . '</a></p>'
		);
	}

	/*
	 * Actions perform on loading of left menu or settings pages
	 */
	public function glf_mor_page_file_path() {
		$pages  = array( 'admin', 'publishing', 'options', 'partner', 'extras' );
		$screen = get_current_screen();
		foreach ( $pages as $page ) {
			if ( Glf_Utils::glf_url_ends_with( $screen->base, $page ) !== false ) {
				if ( in_array( $page, array( 'admin', 'publishing' ) ) ) {
					$this->update_restaurants();
					Glf_Utils::glf_custom_css_check_and_set_defaults( $this->custom_css );
				}
				require( GLF_PLUGIN_DIR . 'includes/admin/pages/' . $page . '/' . $page . '.php' );
				break;
			}
		}
	}

	public function glf_check_if_error_and_display_it( $response ) {
		if ( is_wp_error( $response ) ) {
			$this->error = $response;
			require( GLF_PLUGIN_DIR . 'includes/admin/pages/options/options.php' );
			die;
		} else {
			$this->error = null;
		}
	}

	public function get_glf_mor_token( $target = 'admin' ) {
		if ( ! $this->is_authenticated() ) {
			return null;
		}
		$remoteUrl = $this->auth_domain . $this->auth_token . '/' . $target;
		$response  = wp_remote_post( $remoteUrl, array(
				'method'  => 'GET',
				'headers' => array()
			)
		);

		$this->glf_check_if_error_and_display_it( $response );
		$respone_body = json_decode( $response[ 'body' ] );
		if ( isset( $respone_body->errorDescription ) ) {
			$errors = new WP_Error();
			$errors->add( '1', $respone_body->errorDescription );
			$this->glf_check_if_error_and_display_it( $errors );
		}

		$url = $response[ 'body' ];
		if ( $target == 'admin' ) {
			$url .= '&language_code=' . $this->admin_language;
		}

		return $url;
	}

	public function glf_mor_api_call( $route, $method = 'GET', $body = '' ) {
		if ( ! $this->is_authenticated() ) {
			return null;
		}

		if ( ! $this->api_token ) {
			$api_token = $this->get_glf_mor_token( 'api' );
			$this->glf_check_if_error_and_display_it( $api_token );
			$this->api_token = $api_token;
		}
		$response = wp_remote_post( GLF_API_URL . $route, array(
				'method'  => 'GET',
				'headers' => array(
					'Authorization' => $this->api_token,
					'content-type'  => 'application/json'
				),
				'body'    => $body
			)
		);

		$this->glf_check_if_error_and_display_it( $response );

		$respone_body = json_decode( $response[ 'body' ] );
		if ( isset( $respone_body->errorDescription ) ) {
			$errors = new WP_Error();
			$errors->add( '1', $respone_body->errorDescription );
			$this->glf_check_if_error_and_display_it( $errors );
		}

		return $respone_body;
	}

	public function update_restaurants() {
		$restaurants = $this->glf_mor_api_call( 'user/restaurants' );
		$this->save_user_data( array( 'restaurants' => $restaurants ) );
		Glf_Utils::glf_more_restaurant_data( 'true', 'false' );
	}

	public function save_user_data( $options ) {
		$restaurant_data_obj = Glf_Utils::glf_more_restaurant_data();

		if ( ! $restaurant_data_obj ) {
			$restaurant_data_obj = new stdClass();
		}

		foreach ( $options as $key => $value ) {
			$restaurant_data_obj->$key = $value;
		}

		update_option( 'glf_mor_restaurant_data', $restaurant_data_obj );
	}

	/**
	 * Styling & JS: loading stylesheets and js for the plugin.
	 */
	public function add_media_scripts( $page ) {

		wp_enqueue_script( 'restaurant_system_media_btn_js', plugin_dir_url( __FILE__ ) . 'assets/js/wp-editor-glf-media-button.js', array(), $this->version );
		wp_enqueue_script( 'restaurant_system_clipboard_js', plugin_dir_url( __FILE__ ) . 'assets/js/clipboard.min.js', array(), '1.7.1' );
		wp_enqueue_script( 'restaurant_system_customize_btn_js', plugin_dir_url( __FILE__ ) . 'assets/js/admin-customize-button.js', array(), $this->version );
		wp_enqueue_script( 'restaurant_system_footer_js', plugin_dir_url( __FILE__ ) . 'assets/js/footer.js', array(), $this->version, true );
		wp_enqueue_style( 'restaurant_system_style', plugins_url( 'assets/css/style.css', __FILE__ ), false, $this->version );
		wp_enqueue_style( 'restaurant_system_public_style', plugins_url( 'assets/css/public-style.css', __FILE__ ), false, $this->version );


		if ( Glf_Utils::glf_url_ends_with( $page, 'partner' ) || Glf_Utils::glf_url_ends_with( $page, 'extras' ) ) {
			wp_enqueue_style( 'restaurant_system_website_style', plugins_url( 'assets/css/style-website.css', __FILE__ ), false, $this->version );
		}

		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
	}

	public function add_public_media_scripts( $page ) {
		wp_enqueue_style( 'restaurant_system_public_style', plugins_url( 'assets/css/public-style.css', __FILE__ ), false, $this->version );
	}

	/*
	 * Propagate action to the whole network
	 */
	public static function glf_mor_propagate_in_network( $networkwide, $action ) {
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $networkwide ) {
				$old_blog_id = $wpdb->blogid;

				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );

					if ( $action === 'install' ) {
						self::_glf_mor_install();
					} else {
						if ( $action === 'uninstall' ) {
							self::_glf_mor_uninstall();
						}
					}
				}

				switch_to_blog( $old_blog_id );

				return;
			}
		}

		if ( $action === 'install' ) {
			self::_glf_mor_install();
		} else {
			if ( $action === 'uninstall' ) {
				self::_glf_mor_uninstall();
			}
		}
	}

	/*
	 * Actions performed on plugin activation
	 */
	public static function glf_mor_install( $networkwide ) {
		self::glf_mor_propagate_in_network( $networkwide, 'install' );
	}

	public static function _glf_mor_install() {
		if ( ! get_option( 'glf_mor_installation_id' ) ) {
			update_option( 'glf_mor_installation_id', wp_generate_uuid4() );
		}
	}

	/*
	 * Actions performed on plugin uninstall
	 */
	public static function glf_mor_uninstall() {
		self::glf_mor_propagate_in_network( true, 'uninstall' );
	}

	public static function _glf_mor_uninstall() {
		delete_option( 'glf_mor_installation_id' );
		delete_option( 'glf_mor_restaurant_data' );
	}


	public function is_authenticated() {
		return $this->auth_token;
	}

	public function load_user_data() {
		$restaurant_data_obj = Glf_Utils::glf_more_restaurant_data();
		$pages               = array( 'auth_domain', 'auth_token', 'restaurants', 'user', 'custom_css' );

		foreach ( $pages as $key ) {
			$this->$key = $restaurant_data_obj && isset( $restaurant_data_obj->$key ) ? $restaurant_data_obj->$key : null;
		}
		$this->installation_id = get_option( 'glf_mor_installation_id' );
	}

	public function remove_user_data() {
		delete_option( 'glf_mor_restaurant_data' );
		$this->load_user_data();
	}

	/*
	 * Actions performed when a new blog is added to the multisite
	 */
	public static function glf_mor_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		global $wpdb;

		if ( is_plugin_active_for_network( 'menu-ordering-reservations/restaurant-system.php' ) ) {
			$old_blog_id = $wpdb->blogid;

			switch_to_blog( $blog_id );
			self::_glf_mor_install();

			switch_to_blog( $old_blog_id );
		}
	}

	public static function getInstance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new GLF_Restaurant_System();
		}

		Glf_Utils::$_GLF = self::$_instance;

		return self::$_instance;
	}
}

GLF_Restaurant_System::getInstance();
?>
