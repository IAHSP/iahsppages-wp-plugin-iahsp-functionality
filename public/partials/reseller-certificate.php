<?php 
class Reseller_Certificate {

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct() {
  }


  // The certificate upload form
  private function upload_form_display() {
    echo "

      <div clas='row'>
        <div class='col-sm-6'>
        <div class='card'>
          <div class='card-body'>
            <form class='' method='post'>
              <div class='mb-3'>
                <label for='resell-certificate'>Resell Certificate</label>
                <input class='form-control' type='file' id='resell-certificate' name='resell-certificate'>
              </div>

              <div class='mb-3'>
                <input class='btn btn-primary' type='submit' name='submit' value='Upload'/>
              </div>
            </form>
          </div>
        </div>
        </div>
      </div>
    ";
  } // upload_form_display

  public function upload_form_validation()  {
    $noErrors = true;
    return $noErrors;
  } // upload_form_validation

  public function upload_form_main() {
    if ( isset($_POST['submit'] )) {
      error_log("upload_form_main got called");
      echo "form has been submitted";
    } else {
      $this->upload_form_display();
    } // if submit

  } // custom_user_registration

  // The callback function that will replace [book]
  public function upload_form_shortcode() {
    ob_start();
    $this->upload_form_main();
    return ob_get_clean();
  } //custom_registration_shortcode
}
