<?php 
/**
 * Once user has logged in, this will check fb if the user is a vendor,
 * and what vendor package they have subscribed to.
 * It will then assign the vendor role, and store the pacakge type in the
 * profile.
 */
class Vendor_Verification {

  private $firebaseConfig;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   */
  public function __construct() {
    $this->AWSAccessKey = getenv('AWSAccessKey');
    $this->AWSSecretKey = getenv('AWSSecretKey');
    $this->load_dependencies();

    $this->s3 = new S3($this->AWSAccessKey, $this->AWSSecretKey, false, 's3.amazonaws.com', 'us-west-2');
    $this->s3->setSignatureVersion('v4');

    $this->bucketName = "bading-test";
  }

  private function load_dependencies() {
    //require_once plugin_dir_path( dirname( __FILE__ ) ) . '/partials/S3.php';
  }

  public function check_if_vendor() {
    // before we try anything, are they even logged in?
    if (is_user_logged_in()) {
      // get email
      $base_gcf_URL = getenv("IAHSP_GCF_URL");
      $url = $base_gcf_URL . "/savvy_check_exp/check_if_vendor";
      $currentUserObj = wp_get_current_user();
      $uid = $currentUserObj->ID;
      $userEmail = $currentUserObj->user_email;
      //error_log("url: {$url}  userEmail: {$userEmail}");
      //error_log(print_r($currentUserObj, true));

      $body = array(
        "email" => $userEmail
      );

      //hit the GCF
      $ch = curl_init($url);
      //curl_setopt($ch, CURLOPT_POST, 1);
      //curl_setopt($ch, CURLOPT_URL, $url);
      //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Origin: https://shopsavvy.pro', 'Content-Type: application/json'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );

      $result = curl_exec($ch);
      $resultsJSON = json_decode($result);
      //error_log('results:');
      //error_log(print_r($resultsJSON, true));
      curl_close($ch);


      if ($resultsJSON->status == true) {
        $expDate = $resultsJSON->payload->expDate;
        $isExpired = $resultsJSON->payload->isExpired;
        $isVendor = $resultsJSON->payload->userIsVendor;

        //save the user's expiration date, regaurdless if vendor or not.
        update_user_meta( $uid, 'savvyExpirationDate', $expDate );

        if ($isVendor) {
          //error_log('JSON said this user should be a vendor!');
          $vendorPackage = $resultsJSON->payload->vendorPackage;
          //save the name of the user's vendor package
          update_user_meta( $uid, 'vendorPackage', $vendorPackage );

          // check if hey already have the vendor role
          if ( in_array( 'seller', (array) $currentUserObj->roles ) ) {
            //error_log('current user is already a vendor');

          } else {
            error_log('user vendor role not yet set');
            //add vendor role to user.
            $currentUserObj->set_role('seller');
            //also enable them for selling in dokan
            update_user_meta( $uid, 'dokan_enable_selling', 'yes' );

            //refresh the page so the new role can take
            //have to refresh with JS, because header has already been set in PHP
            echo "
              <script>location.reload();</script>
            ";
          }
        }

      }

      // check if expired
    }

  } // /prevent_checkout_if_user_expired()

  public function upload_form_main() {
    if ( isset($_POST['submit'] )) {
      $this->currentUID = get_current_user_id();
      $this->upload_form_process();
    } else {
      $this->upload_form_display();
    } // if submit

  } // custom_user_registration

  // The callback function that will replace [book]
  public function vendor_check_shortcode() {
    ob_start();
    $this->check_if_vendor();
    return ob_get_clean();
  } //custom_registration_shortcode
}
