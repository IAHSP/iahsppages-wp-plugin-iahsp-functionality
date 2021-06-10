<?php 
class Reseller_Certificate {

  private $s3;
  private $AWSAccessKey;
  private $AWSSecretKey;
  private $bucketName;
  private $currentUID;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
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
    require_once plugin_dir_path( dirname( __FILE__ ) ) . '/partials/S3.php';
  }


  // The certificate upload form
  private function upload_form_display() {
    echo "

      <div clas='row'>
        <div class='col-sm-6'>
        <div class='card'>
          <div class='card-body'>
            <form class='' method='post' enctype='multipart/form-data'>
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

  public function upload_form_process()  {
    $noErrors = false;

    $target_dir = "/tmp/";
    $target_file = $target_dir . basename($_FILES["resell-certificate"]["name"]);
    $tmpUploadedFile = $_FILES["resell-certificate"]["tmp_name"];
    //error_log("target_file is this: {$target_file}");

    $currentFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    $finalFilePath = 'savvy-reseller-cert/' . $this->currentUID . '/' . 'certificate.' . $currentFileType;
    if ($this->s3->putObjectFile($tmpUploadedFile, $this->bucketName, $finalFilePath, S3::ACL_PUBLIC_READ)) {
      error_log("S3::putObjectFile(): File copied to {$finalFilePath}".PHP_EOL);

      // Get object info
      $info = $this->s3->getObjectInfo($this->bucketName, $finalFilePath);
      $url = "https://{$this->bucketName}.s3-us-west-2.amazonaws.com/{$finalFilePath}";
      echo "<pre>" . print_r($url, true) . "</pre>";


      $noErrors = true;
    }


    return $noErrors;
  } // upload_form_validation

  public function upload_form_main() {
    if ( isset($_POST['submit'] )) {
      $this->currentUID = get_current_user_id();
      $this->upload_form_process();
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
