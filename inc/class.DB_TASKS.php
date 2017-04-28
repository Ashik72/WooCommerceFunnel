<?php

if(!defined('WPINC')) // MUST have WordPress.
    exit('Do NOT access this file directly: '.basename(__FILE__));


/**
 * DB TASKS
 */
class DB_TASKS
{

  public static $instance;


  public static function get_instance() {
    	if ( ! isset( self::$instance ) ) {
    		self::$instance = new self();
    	}
    	return self::$instance;
    }

  function __construct() {
    add_action( 'wp_ajax_check_connection_with_db', array($this, 'check_connection_with_db_callback') );
    add_action( 'wp_ajax_nopriv_check_connection_with_db', array($this, 'check_connection_with_db_callback') );

    add_action( 'wp_ajax_isUserLoggedIn', array($this, 'isUserLoggedIn_callback') );
    add_action( 'wp_ajax_nopriv_isUserLoggedIn', array($this, 'isUserLoggedIn_callback') );

    //add_action('wp_footer', [$this, 'testFooter']);

    //add_filter("woocommerce_rest_pre_insert_product", [$this, 'add_custom_funnel_id'], 15, 2);

    add_action( 'wp_ajax_process_funnel_save_data', array($this, 'process_funnel_save_data_callback') );
    add_action( 'wp_ajax_nopriv_process_funnel_save_data', array($this, 'process_funnel_save_data_callback') );

    add_action( 'wp_ajax_load_funnel_data', array($this, 'load_funnel_data_callback') );
    add_action( 'wp_ajax_nopriv_load_funnel_data', array($this, 'load_funnel_data_callback') );

  }


  public function load_funnel_data_callback() {

    if (empty($_POST['nonce']) || empty($_POST['funnel_id']))
      wp_die();

    $edit_nonce_verify = wp_verify_nonce( $_POST['nonce'], 'funnel-edit_'.$_POST['funnel_id'] );

    if (!$edit_nonce_verify)
      wp_die();

      $cred = $this->getCredentials();

      $funnel_id = (int) $_POST['funnel_id'];

      $defaults = [
        'request_type' => 'edit_funnel',
        'wc_cs_key' => '',
        'wc_cs_secret' => '',
        'wc_funnel_mail' => '',
        'wc_funnel_password' => '',
        'wc_funnel_usecret' => '',
        'site_url' => '',
        'funnel_id' => $funnel_id
      ];

      $req_args = wp_parse_args( $cred, $defaults );

      if (array_search("", $req_args) !== false )
        wp_die();

        $headers = array('Accept' => 'application/json');
        $query = $req_args;
        Unirest\Request::verifyPeer(false);
        $response = Unirest\Request::post('https://restdata.funnely.io', $headers, $query);

      echo $response->raw_body;

    wp_die();

  }


  public function process_funnel_save_data_callback() {

    if (empty($this->getCredentials()) || empty($_POST['storeConnections']) || empty($_POST['storeConnectionsUuids']))
      wp_die();

      $cred = $this->getCredentials();

      $defaults = [
        'request_type' => 'add_funnel',
        'wc_cs_key' => '',
        'wc_cs_secret' => '',
        'wc_funnel_mail' => '',
        'wc_funnel_password' => '',
        'wc_funnel_usecret' => '',
        'site_url' => '',
        'storeConnections' => json_encode($_POST['storeConnections']),
        'storeConnectionsUuids' => json_encode($_POST['storeConnectionsUuids']),
        'funnel_title'  => $_POST['funnel_title'],
        'funnel_id' => $_POST['funnel_id']
      ];
      $req_args = wp_parse_args( $cred, $defaults );

      if (array_search("", $req_args) !== false )
        wp_die();


      $headers = array('Accept' => 'application/json');
      $query = $req_args;
      Unirest\Request::verifyPeer(false);
      $response = Unirest\Request::post('https://restdata.funnely.io', $headers, $query);

      if ($response->raw_body == "true")
        echo json_encode(self::update_the_wp_product($req_args));
      else
        echo 0;

      //echo ($response->raw_body);

    //echo $response->raw_body;

    wp_die();
  }

  private static function update_the_wp_product($the_data = NULL) {

    if (empty($the_data) || (empty($the_data['storeConnections'])))
      return "no data found";

      $the_data['storeConnections'] = json_decode($the_data['storeConnections']);
      $storeConnections = $the_data['storeConnections'];
      $array_storeConnection = [];
      $main_product = 0;

      foreach ($the_data['storeConnections'] as $key => $storeConnection) {
        $array_storeConnection[] = $storeConnection;

        if (!empty($storeConnection->isParent)) {

          $main_product = $storeConnection->el;

        }

      }

      $main_product = self::parseTag($main_product,'div');
      $main_product = (empty($main_product['data-product_id']) ? "" : ( stripslashes($main_product['data-product_id'])));

      if (empty($main_product))
        return "no main product id";

      $main_product = preg_replace("/[^a-zA-Z0-9]/", "", $main_product);
      $main_product = (int) $main_product;

      $add_meta = update_post_meta($main_product, 'funnely_io_meta', $storeConnections);

    //   $headers = array('Accept' => 'application/json');
    //   $query = $req_args;
    //   Unirest\Request::verifyPeer(false);
    //   $response = Unirest\Request::post('https://restdata.funnely.io', $headers, $query);
    //
    // echo $response->raw_body;


      return $add_meta;

  }


  public static function parseTag($content,$tg)
  {
      $dom = new DOMDocument;
      $dom->loadHTML($content);
      $attr = array();
      foreach ($dom->getElementsByTagName($tg) as $tag) {
          foreach ($tag->attributes as $attribName => $attribNodeVal)
          {
             $attr[$attribName]=$tag->getAttribute($attribName);
          }
      }
      return $attr;
  }

  public static function getStaticCred() {

    return self::getCredentials();

  }

  private function getCredentials() {


    $wc_cs_key = $this->getVal('wc_cs_key');
    $wc_cs_secret = $this->getVal('wc_cs_secret');
    $wc_funnel_mail = $this->getVal('wc_funnel_mail');
    $wc_funnel_password = $this->getVal('wc_funnel_password');
    $wc_funnel_usecret = preg_replace("~[^a-z0-9:]~i", "", $this->getVal('wc_funnel_usecret'));

    $site_url = get_site_url();

    if (empty($wc_cs_key) || empty($wc_cs_secret) || empty($wc_funnel_mail) || empty($wc_funnel_password) || empty($wc_funnel_usecret))
      return;

    return [

      'wc_cs_key' => $wc_cs_key,
      'wc_cs_secret' => $wc_cs_secret,
      'wc_funnel_mail' => $wc_funnel_mail,
      'wc_funnel_password' => $wc_funnel_password,
      'wc_funnel_usecret' => $wc_funnel_usecret,
      'site_url' => $site_url,

    ];

  }

  public function add_custom_funnel_id($request) {

    // Post title.
		//if ( isset( $request['fynnelyio_id'] ) )
			//$data->post_title = wp_filter_post_kses( $request['fynnelyio_id'] );


      //return apply_filters( "woocommerce_rest_pre_insert_product_funnelyio", $data, $request );

  }

  public function testFooter() {

    $wc_funnel_usecret = preg_replace("~[^a-z0-9:]~i", "", $this->getVal('wc_funnel_usecret'));

    d($wc_funnel_usecret);


  }

  function isUserLoggedIn_callback() {

    if (!current_user_can( 'manage_options' )) {

      echo json_encode(['stat' => 0]);
      wp_die();

    }

      $wc_funnel_usecret = preg_replace("~[^a-z0-9:]~i", "", $this->getVal('wc_funnel_usecret'));


      echo json_encode(['stat' => 1, 'secret' => $wc_funnel_usecret]);

    wp_die();

  }

  public function check_connection_with_db_callback() {

    $wc_cs_key = $this->getVal('wc_cs_key');
    $wc_cs_secret = $this->getVal('wc_cs_secret');
    $wc_funnel_mail = $this->getVal('wc_funnel_mail');
    $wc_funnel_password = $this->getVal('wc_funnel_password');
    $wc_funnel_usecret = preg_replace("~[^a-z0-9:]~i", "", $this->getVal('wc_funnel_usecret'));

    $site_url = get_site_url();

    if (empty($wc_cs_key) || empty($wc_cs_secret) || empty($wc_funnel_mail) || empty($wc_funnel_password) || empty($wc_funnel_usecret))
      echo json_encode(array('stat' => 0, 'msg' => 'Empty fields, please input all the appropriate values!'));

      $headers = array('Accept' => 'application/json');
      $query = array('user_check' => 1, 'usermail' => $wc_funnel_mail, 'password' => $wc_funnel_password, 'cs_secret' => $wc_cs_secret, 'cs_key' => $wc_cs_key, 'site_url' => $site_url, 'wc_funnel_usecret' => $wc_funnel_usecret);
      Unirest\Request::verifyPeer(false);
      $response = Unirest\Request::post('https://restdata.funnely.io', $headers, $query);


    echo $response->raw_body;
    //echo $response;

    wp_die();

  }


  public function authenticateConnection() {

    $wc_cs_key = $this->getVal('wc_cs_key');
    $wc_cs_secret = $this->getVal('wc_cs_secret');
    $wc_funnel_mail = $this->getVal('wc_funnel_mail');
    $wc_funnel_password = $this->getVal('wc_funnel_password');
    $site_url = get_site_url();

    if (empty($wc_cs_key) || empty($wc_cs_secret) || empty($wc_funnel_mail) || empty($wc_funnel_password))
      echo json_encode(array('stat' => 0, 'msg' => 'Empty fields, please input all the appropriate values!'));

      $headers = array('Accept' => 'application/json');
      $query = array('user_check' => 1, 'usermail' => $wc_funnel_mail, 'password' => $wc_funnel_password, 'cs_secret' => $wc_cs_secret, 'cs_key' => $wc_cs_key, 'site_url' => $site_url);

      $response = Unirest\Request::post('http://restdata.funnely.io', $headers, $query);

    return $response->raw_body;

  }

  private function getVal($key = null) {

    if (empty($key))
      return;

      $titan = TitanFramework::getInstance( 'wc_funnel_nci_opts' );

      return $titan->getOption($key);

  }

}



 ?>
