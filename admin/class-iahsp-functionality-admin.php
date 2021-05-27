<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       gabrieltumbaga.com
 * @since      1.0.0
 *
 * @package    Iahsp_Functionality
 * @subpackage Iahsp_Functionality/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Iahsp_Functionality
 * @subpackage Iahsp_Functionality/admin
 * @author     Gabriel Tumbaga <gabriel@iahsp.com>
 */
class Iahsp_Functionality_Admin {

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
   * @param      string    $plugin_name       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  /**
   * Register the stylesheets for the admin area.
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

    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/iahsp-functionality-admin.css', array(), $this->version, 'all' );

  }

  /**
   * Register the JavaScript for the admin area.
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

    wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/iahsp-functionality-admin.js', array( 'jquery' ), $this->version, false );

  }

  public function display_extra_profile_fields ( $user ) {
    $resellcert = esc_attr( get_the_author_meta( 'resellCertificate', $user->ID ) );

    echo "
      <h2>SAVVY Resell Certificate</h2>
      <table class='form-table'>

        <tr>
          <th><label for='resell-cert'>URL</label></th>

          <td>
          <input type='text' name='resell-cert' id='resell-cert' value='{$resellcert}' class='regular-text' readonly /><br />
          <span class='description'>This is the Resell Certificate that you uploaded.</span>
          </td>
        </tr>

      </table>
    ";
  }

  //intentionally making this not editible from the profile page.
  //public function save_extra_profile_fields( $user_id ) {

    //if ( !current_user_can( 'edit_user', $user_id ) )
      //return false;

    //update_usermeta( $user_id, 'resell-cert', $_POST['resell-cert'] );
  //}

  public function check_if_user_uploaded_resell_certificate ($useremail) {
    // if pw is needed, use a 2nd arg and change the args from 1 to 2 on the add_action
    //echo "<pre>GABE WAS HERE!</pre>";
    $userObj = get_user_by('email', $useremail);
    $userID = $userObj->data->ID;
    $resellcert = esc_attr( get_the_author_meta( 'resellCertificate', $userID ) );
    error_log("my login hook worked correctly");
    if (empty($resellcert)) {
      header("Location: " . "/reseller-certificate-upload");
      //error_log("reseller cert was found empty. fwd user to resell cert upload page");
    } else {
      //error_log("reseller cert found as {$resellcert}");
    }
  }

}
