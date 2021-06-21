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

  private $resellerCertificate;
  private $vendorCheck;

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

    $this->load_dependencies();

  }

  /**
   * Load the custom dependencies for this.
   *
   */
  private function load_dependencies() {

    /**
     * The class responsible for the reseller certificate upload form
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/reseller-certificate.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/vendor-check.php';
    $this->resellerCertificate = new Reseller_Certificate;
    $this->vendorCheck = new Vendor_Verification;

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

  public function add_woocommerce_support() {
    add_theme_support( 'woocommerce' );
  }

  public function remove_items_woo_myaccount_nav($navItems) {
    // Display the array items so we can see what to remove
    //echo "<pre>" . print_r($navItems, true) . "</pre>";

    $newItems = $navItems;

    $keysToRemove = [
      "downloads",
      "edit-account",
      "rma-requests",
      "following",
      "support-tickets"
    ];

    foreach($keysToRemove as $key) {
      if ((array_key_exists($key, $newItems)) !== false) {
        unset($newItems[$key]);
      }
    }

    return $newItems;
  }

  private function display_registration_form( $username, $password, $email, $first_name, $last_name, $nickname ) {
    // load the WP password strength JS script so we can make use of it on our form
    wp_enqueue_script( 'password-strength-meter' );

    echo '

    <form class="" method="post">
    <div class="mb-3">
    <label for="username">Username <strong>*</strong></label>
    <input class="form-control" type="text" name="username" value="' . ( isset( $username ) ? $username : null ) . '">
    </div>

    <div class="mb-3">
      <div class="mb-2">
        <label for="password">Password <strong>*</strong></label>
        <input class="form-control" onkeyup="pwStrength()" id="password" type="password" name="password" value="' . ( isset( $password ) ? $password : null ) . '">
        <span class="mb-2" id="password-strength"></span>
      </div>
      <div class="">
        <label for="password2">Re-type Password <strong>*</strong></label>
        <input class="form-control" onkeyup="checkPWsMatch()" id="password2" type="password2" name="password2" value="' . ( isset( $password2 ) ? $password2 : null ) . '">
        <span class="" id="passwords-match"></span>
      </div>
    </div>

    <div class="mb-3">
    <label for="email">Email <strong>*</strong></label>
    <input class="form-control" type="text" name="email" value="' . ( isset( $email) ? $email : null ) . '">
    </div>

    <div class="mb-3">
    <label for="firstname">First Name</label>
    <input class="form-control" type="text" name="fname" value="' . ( isset( $first_name) ? $first_name : null ) . '">
    </div>

    <div class="mb-3">
    <label for="firstname">Last Name</label>
    <input class="form-control" type="text" name="lname" value="' . ( isset( $last_name) ? $last_name : null ) . '">
    </div>

    <div class="mb-3">
    <label for="nickname">Display Name</label>
    <input class="form-control" type="text" name="nickname" value="' . ( isset( $nickname) ? $nickname : null ) . '">
    </div>

    <div class="mb-3">
    <input class="btn btn-primary" type="submit" name="submit" value="Register"/>
    </div>
    </form>

    <script>
      let pwBlackList;

      // wait till everything is loaded, to ensure that our php call has already loaded the wp script.
      document.addEventListener("DOMContentLoaded", () => {
        pwBlackList = wp.passwordStrength.userInputBlacklist();
      });

      const checkPWsMatch = () => {
        const pass1 = document.getElementById("password").value;
        const pass2 = document.getElementById("password2").value;
        const pwMatch = document.getElementById("passwords-match");
        if (pass1 === pass2) {
          pwMatch.textContent = "Passwords Match!";
        } else {
          pwMatch.textContent = "Passwords do not match.";
        }

      } // checkPWsMatch

      const pwStrength = () => {
        const pass1 = document.getElementById("password").value;
        const pass2 = document.getElementById("password2").value;
        const pwStrengthResult = document.getElementById("password-strength");
        const strength = wp.passwordStrength.meter(
          pass1,
          pwBlackList,
          pass2
        );
        console.log(strength);

        // Add the strength meter results
        switch ( strength ) {
        case 2:
          pwStrengthResult.classList.add( "bad" )
          pwStrengthResult.textContent = pwsL10n.bad;
          break;
        case 3:
          pwStrengthResult.classList.add( "good" )
          pwStrengthResult.textContent = pwsL10n.good;
          break;
        case 4:
          pwStrengthResult.classList.add( "strong" )
          pwStrengthResult.textContent = pwsL10n.strong;
          break;
        case 5:
          pwStrengthResult.classList.add( "short" )
          pwStrengthResult.textContent = pwsL10n.mismatch;
          break;
        default:
          pwStrengthResult.classList.add( "short" )
          pwStrengthResult.textContent = pwsL10n.short;
        }
      } // pwStrength
    </script>
    ';
  }

  public function registration_validation( $username, $password, $email, $first_name, $last_name, $nickname )  {
    $reg_errors = array();

    // first lets check for the required fields
    if ( empty( $username ) || empty( $password ) || empty( $email ) ) {
      //error_log('Required form field is missing');
      $reg_errors[] = 'Required form field is missing';
    }

    // check that username is good length
    if ( 4 > strlen( $username ) ) {
      //error_log('Username too short. At least 4 characters is required');
      $reg_errors[] =  'Username too short. At least 4 characters is required' ;
    }

    // check that the username doesn't already exist
    if ( username_exists( $username ) ) {
      //error_log('Sorry, that username already exists!');
      $reg_errors[] = 'Sorry, that username already exists!';
    }

    // ensure that username is valid for WP
    if ( ! validate_username( $username ) ) {
      //error_log('Sorry, the username you entered is not valid');
      $reg_errors[] =  'Sorry, the username you entered is not valid' ;
    }

    // ensure PW is longer than 5 char
    if ( 5 > strlen( $password ) ) {
      //error_log('Password length must be greater than 5');
        $reg_errors[] =  'Password length must be greater than 5' ;
    }

    // check that email is valid
    if ( !is_email( $email ) ) {
      //error_log('Email is not valid');
      $reg_errors[] =  'Email is not valid' ;
    }

    // check if email exists in WP
    if ( email_exists( $email ) ) {
      //error_log('Email Already in use');
      $reg_errors[] =  'Email Already in use' ;
    }


    $noErrors = true;
    //error_log("This is before the return");

    // if any errors, display those bad boyz
    if ( is_wp_error( $reg_errors ) ) {

      //foreach ( $reg_errors->get_error_messages() as $error ) {
      foreach ( $reg_errors as $error ) {
        echo '<div>';
        echo '<strong>ERROR</strong>: ';
        echo $error . '<br/>';
        //error_log("Field Validation Error: {$error}");
        echo '</div>';
      }

      $noErrors = false;
      //error_log("Errors found. noErrors is this: {$noErrors}");
    } else {
      //error_log("Everything worked... noErrors is this: {$noErrors}");
      $noErrors = true;
    }

    return $noErrors;


  } // registration_validation

  private function complete_registration( $username, $password, $email, $first_name, $last_name, $nickname ) {
    //global $reg_errors, $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio;
    $userdata = array(
      'user_login'    =>   $username,
      'user_pass'     =>   $password,
      'user_email'    =>   $email,
      'first_name'    =>   $first_name,
      'last_name'     =>   $last_name,
      'display_name'  =>   $nickname,
      'role'          =>   'customer'
    );
    $userID = wp_insert_user( $userdata );
    if ($userID) {
      // header redirect wont work because header has already been sent,
      // so we will do a JS redirect
      echo '<script>window.location.href = "/customer-registration-complete/";</script>';
      //echo 'Registration complete. Goto <a href="' . get_site_url() . '/wp-login.php">login page</a>.';
      //error_log("userID: {$userID}");
    } else {
      echo "User registration failed...";
      //error_log("User registration failed...");
    }
  } // complete_registration

  public function custom_user_registration() {
    if ( isset($_POST['submit'] ) ) {
      // sanitize user form input
      $username   =   sanitize_user( $_POST['username'] );
      $password   =   esc_attr( $_POST['password'] );
      $email      =   sanitize_email( $_POST['email'] );
      $first_name =   sanitize_text_field( $_POST['fname'] );
      $last_name  =   sanitize_text_field( $_POST['lname'] );
      $nickname   =   sanitize_text_field( $_POST['nickname'] );


      // validate user input after it has been sanitized
      $fieldsAreValid = $this->registration_validation(
        $username,
        $password,
        $email,
        $first_name,
        $last_name,
        $nickname
      );


      //error_log("fieldsAreValid: ");
      //error_log(print_r($fieldsAreValid));

      if ($fieldsAreValid == 1) {
        error_log('stuff worked');
      } else {
        error_log('something is wrong with fieldsAreValid');
      }

      // call complete_registration to create the user
      // only when no WP_error is found
      if ($fieldsAreValid == 1) {
        error_log('fields are valid');
        $this->complete_registration(
          $username,
          $password,
          $email,
          $first_name,
          $last_name,
          $nickname
        );
      } else {
        error_log('fields are not valid for some reason...');
      }
    } // if submit

    $this->display_registration_form(
      $username,
      $password,
      $email,
      $first_name,
      $last_name,
      $nickname
    );
  } // custom_user_registration

  // The callback function that will replace [book]
  public function custom_registration_shortcode() {
    ob_start();
    $this->custom_user_registration();
    return ob_get_clean();
  } //custom_registration_shortcode

  public function custom_login_page_logo() {
    $logoURL = get_stylesheet_directory_uri() . "/img/logo/savvy-logo-1.png";
    echo "
      <style>
      body.login #login h1 a, .login h1 a {
        background-image: url({$logoURL});
        width:210px;
        height:88px;
        background-size: 210px 88px;
        background-repeat: no-repeat;
        padding-bottom: 30px;
      }
      body.login #loginform .button {
        background-color: #f1c95c;
        color: #fff;
      }
      </style>
  ";
  }

  public function vendor_check_shortcode() {
    return $this->vendorCheck->vendor_check_shortcode();
  }

  public function reseller_certificate_upload_form_shortcode() {
    return $this->resellerCertificate->upload_form_shortcode();
  } //custom_registration_shortcode

}
