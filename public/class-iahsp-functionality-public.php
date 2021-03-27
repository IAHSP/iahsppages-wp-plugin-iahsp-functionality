<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       gabrieltumbaga.com
 * @since      1.0.0
 *
 * @package    Iahsp_Functionality
 * @subpackage Iahsp_Functionality/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Iahsp_Functionality
 * @subpackage Iahsp_Functionality/public
 * @author     Gabriel Tumbaga <gabriel@iahsp.com>
 */
class Iahsp_Functionality_Public {

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Iahsp_Functionality_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The Iahsp_Functionality_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/iahsp-functionality-public.css', array(), $this->version, 'all' );

  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Iahsp_Functionality_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The Iahsp_Functionality_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/iahsp-functionality-public.js', array( 'jquery' ), $this->version, false );

  }

  /**
   * Get user's email address, which will be their firebase email login, due to the firebase auth plugin,
   * and hit GCF to check if the user is expired or not.
   *
   * @since    1.0.0
   */
  public function prevent_checkout_if_user_expired() {
    // before we try anything, are they even logged in?
    if (is_user_logged_in()) {
      // get email
      $base_gcf_URL = getenv("IAHSP_GCF_URL");
      $url = $base_gcf_URL . "/savvy_check_exp/checkexp";

      $body = array(
        "email" => "gabriel@iahsp.com"
      );

      //hit the GCF
      $ch = curl_init();
      //curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
      curl_setopt($ch,CURLOPT_HTTPHEADER,array('Origin: https://pages.iahsp.com', 'Content-Type:application/json'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );

      $result = curl_exec($ch);
      $resultsJSON = json_decode($result);
      curl_close($ch);


      if ($resultsJSON->status == true) {
        $expDate = $resultsJSON->payload->expDate;
        $isExpired = $resultsJSON->payload->isExpired;

        if ($isExpired == true) {
          // user is expired, so lets redirect them.
          header("Location: " . "/please-register/");
        }
      }

      // check if expired
    } else {
      header("Location: " . "/please-register/");
    }


  } // /prevent_checkout_if_user_expired()

}
