<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       gabrieltumbaga.com
 * @since      1.0.0
 *
 * @package    Iahsp_Functionality
 * @subpackage Iahsp_Functionality/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Iahsp_Functionality
 * @subpackage Iahsp_Functionality/includes
 * @author     Gabriel Tumbaga <gabriel@iahsp.com>
 */
class Iahsp_Functionality {

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      Iahsp_Functionality_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function __construct() {
    if ( defined( 'IAHSP_FUNCTIONALITY_VERSION' ) ) {
      $this->version = IAHSP_FUNCTIONALITY_VERSION;
    } else {
      $this->version = '1.0.0';
    }
    $this->plugin_name = 'iahsp-functionality';

    $this->load_dependencies();
    $this->set_locale();
    $this->define_admin_hooks();
    $this->define_public_hooks();

  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Iahsp_Functionality_Loader. Orchestrates the hooks of the plugin.
   * - Iahsp_Functionality_i18n. Defines internationalization functionality.
   * - Iahsp_Functionality_Admin. Defines all hooks for the admin area.
   * - Iahsp_Functionality_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies() {

    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-iahsp-functionality-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-iahsp-functionality-i18n.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-iahsp-functionality-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-iahsp-functionality-public.php';

    $this->loader = new Iahsp_Functionality_Loader();

  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Iahsp_Functionality_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale() {

    $plugin_i18n = new Iahsp_Functionality_i18n();

    $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks() {

    $plugin_admin = new Iahsp_Functionality_Admin( $this->get_plugin_name(), $this->get_version() );

    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

    //SAVVY
    $this->loader->add_action( 'show_user_profile', $plugin_admin, 'display_extra_profile_fields' );
    $this->loader->add_action( 'edit_user_profile', $plugin_admin, 'display_extra_profile_fields' );
    //intentionally not making this editible from profile page
    //$this->loader->add_action( 'personal_options_update', $plugin_admin, 'save_extra_profile_fields' );
    //$this->loader->add_action( 'save_extra_profile_fields', $plugin_admin, 'save_extra_profile_fields' );
                                                                                            //priority 10, request 2 args
    // for now, commenting it out.  currently can't find a propper hook that firebase auth
    // does.  so Christa is fine for now, not requiring the user to upload their resale cert
    //$this->loader->add_filter( 'login_redirect', $plugin_admin, 'check_if_user_uploaded_resell_certificate', 1, 3 );

  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_public_hooks() {

    $plugin_public = new Iahsp_Functionality_Public( $this->get_plugin_name(), $this->get_version() );

    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
    $this->loader->add_action( 'woocommerce_checkout_order_review', $plugin_public, 'prevent_checkout_if_user_expired' );
    $this->loader->add_action( 'after_setup_theme', $plugin_public, 'add_woocommerce_support' );
    $this->loader->add_action( 'login_enqueue_scripts', $plugin_public, 'custom_login_page_logo' );
    $this->loader->add_filter( 'woocommerce_account_menu_items', $plugin_public, 'remove_items_woo_myaccount_nav', 99 );

    // SHOTCODES HERE
    add_shortcode( 'iahsp_user_registration', array($plugin_public, 'custom_registration_shortcode') );
    add_shortcode( 'iahsp_reseller_certificate', array($plugin_public, 'reseller_certificate_upload_form_shortcode') );

  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run() {
    $this->loader->run();
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name() {
    return $this->plugin_name;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     1.0.0
   * @return    Iahsp_Functionality_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader() {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version() {
    return $this->version;
  }

}
