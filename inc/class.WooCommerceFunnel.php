<?php
if(!defined('WPINC')) // MUST have WordPress.
    exit('Do NOT access this file directly: '.basename(__FILE__));

class WooCommerceFunnel
{

    public function __construct() {

        //add_action('wp_footer', array($this, 'test_func'));
        //add_filter( "single_template", array($this, 'load_plugin_template') );

        add_action('admin_enqueue_scripts', array($this, 'load_custom_wp_admin_style'));
        add_action('woocommerce_single_product_summary' , array($this, 'add_skip_button'), 40);
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );

        add_action( 'wp_ajax_the_product_upsell', array($this, 'the_product_upsell_callback') );
        add_action( 'wp_ajax_nopriv_the_product_upsell', array($this, 'the_product_upsell_callback') );

        add_filter( 'woocommerce_locate_template', array($this, 'custom_woocommerce_locate_template'), 10, 3 );

        add_action('wp_footer', array($this, 'removeUpsellCookie'));

        add_action( 'wp_ajax_remove_upsell_cookie', array($this, 'remove_upsell_cookie_callback') );
        add_action( 'wp_ajax_nopriv_remove_upsell_cookie', array($this, 'remove_upsell_cookie_callback') );

        add_action( 'template_redirect', array($this, 'product_debug') );

        add_action( 'woocommerce_single_product_summary', array($this, 'woocommerce_single_product_summary_func') );
        add_action('template_redirect', array($this, 'init_an_user_id'));

        add_action( 'wp_ajax_set_uid_cookie', array($this, 'set_uid_cookie_callback') );
        add_action( 'wp_ajax_nopriv_set_uid_cookie', array($this, 'set_uid_cookie_callback') );

        add_action('get_header', array($this, 'wp_loaded_now_check_product'));

        add_action( 'wp_ajax_add_upsell_product', array($this, 'add_upsell_product_callback') );
        add_action( 'wp_ajax_nopriv_add_upsell_product', array($this, 'add_upsell_product_callback') );

        add_action( 'template_redirect', [$this, 'wc_custom_redirect_after_purchase'] );

    }

    function wc_custom_redirect_after_purchase() {
    	global $wp;

      if (empty( $wp->query_vars['order-received']))
        return;

      if (empty($_COOKIE['unique_user_id_wcf']))
        return;

        $get_user_data = get_transient( $_COOKIE['unique_user_id_wcf'] );

        if (empty($get_user_data['add_upsell_product']))
          return;


          $permalink = get_permalink($get_user_data['add_upsell_product']);

          header('Location: '.$permalink."/?ddd=1");

    }


    public function add_upsell_product_callback() {

      if (empty($_COOKIE['unique_user_id_wcf']))
        return;

      $get_user_data = get_transient( $_COOKIE['unique_user_id_wcf'] );



      $funnely_io_meta_data = (array) $get_user_data['funnel_data'];

      $main_parent = "";

      foreach ($funnely_io_meta_data as $key => $single_data) {

        if (empty($single_data->isParent))
          continue;

        $main_parent = $single_data;

      }

      $add_id = ( !empty($main_parent->up->targetID) ? $main_parent->up->targetID : $main_parent->down->targetID);

      $get_user_data['add_upsell_product'] = $add_id;

      set_transient( $_COOKIE['unique_user_id_wcf'], $get_user_data, 12 * HOUR_IN_SECONDS );

      wp_die();

    }

    public function wp_loaded_now_check_product() {


      if (empty($_COOKIE['unique_user_id_wcf']))
        return;

      $get_user_data = get_transient( $_COOKIE['unique_user_id_wcf'] );

      if (empty($get_user_data['funnel_data']))
        return;

        //d($get_user_data);

    }


    public function woocommerce_single_product_summary_func() {

      $funnely_io_meta_data = get_post_meta(get_the_ID(), 'funnely_io_meta', true);

      if (empty($funnely_io_meta_data))
        return;

        $get_user_data = get_transient( $_COOKIE['unique_user_id_wcf'] );

        $get_user_data['funnel_data'] = $funnely_io_meta_data;

        set_transient( $_COOKIE['unique_user_id_wcf'], $get_user_data, 12 * HOUR_IN_SECONDS );

        $get_user_data = get_transient( $_COOKIE['unique_user_id_wcf'] );

        ?>

      <script type="text/javascript">
        jQuery(document).ready(function($) {

          $(".single_add_to_cart_button").text("Checkout");



        })
      </script>

        <?php


    }


    public function set_uid_cookie_callback() {

      if (empty($_POST['the_uid']))
        return;

      //$set_cookie = setcookie('unique_user_id_wcf', $_POST['the_uid'], time()+(60*60*24), '/');

      echo json_encode($set_cookie);

      wp_die();
    }

    public function init_an_user_id() {

      if (isset($_COOKIE['unique_user_id_wcf'])) {

        //d($_COOKIE['unique_user_id_wcf']);

        return;
      }

      $uid_time = uniqid("unique_user_id_wcf_");


      $get_transient = get_transient( $uid_time );

      while (!empty($get_transient)) {

        $uid_time = uniqid("unique_user_id_wcf_");

        $get_transient = get_transient( $uid_time );

      }


      set_transient( $uid_time, [ 'id' => $uid_time ], 12 * HOUR_IN_SECONDS );

      $headers = array('Accept' => 'application/json');
      $query = ['action' => 'set_uid_cookie', 'the_uid' => $uid_time];
      Unirest\Request::verifyPeer(false);
      // $response = Unirest\Request::post(admin_url('admin-ajax.php'), $headers, $query);
      // d($response->raw_body);
      $set_cookie = setcookie('unique_user_id_wcf', $uid_time, time()+(60*60*24), '/');
        //setcookie('unique_user_id_wcf', $uid_time, time()+(60*60*24), '/');
      header("Refresh:0");

    }




    public function product_debug() {

      if (empty($_GET['product_debug']))
        return;


      if (!current_user_can('manage_options'))
        return;

      $product_id = (int) $_GET['product_debug'];

      d(get_post_meta($product_id));

      wp_die();

    }


    public function remove_upsell_cookie_callback() {

        setcookie('upsell_product_current', "", time()-(60*60*24), '/');

        //echo json_encode("done");
        wp_die();

    }

    public function removeUpsellCookie() {

      if (!isset($_COOKIE['upsell_product_current']))
        return;

        ?>

<script type="text/javascript">

var action = 'remove_upsell_cookie',
    xhr = new XMLHttpRequest();

xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>');
xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
xhr.onload = function() {

    console.log(xhr.status);
    console.log(xhr.responseText);

};
xhr.send(encodeURI('action=' + action));

</script>
        <?php

    }

    public function custom_woocommerce_locate_template(  $template, $template_name, $template_path) {
      //d($_COOKIE);
      if (!isset($_COOKIE['upsell_product_current']))
        return $template;

        $productID = (int) $_COOKIE['upsell_product_current'];
        $permalink = get_permalink($productID);

        global $woocommerce;



        $_template = $template;

        if ( ! $template_path ) $template_path = $woocommerce->template_url;


        $plugin_path  = wc_product_options_PLUGIN_DIR . DS . 'templates'. DS;

        $template = locate_template(

          array(

            $template_path . $template_name,

            $template_name

          )
         );
        if ( ! $template && file_exists( $plugin_path . $template_name ) )

          $template = $plugin_path . $template_name;

        if ( ! $template )

        $template = $_template;
        $template = "Template for upsell product ".$permalink;
          echo $template;
          $this->removeUpsellCookie();
          die();

        return $template;
            //$template = "Template for upsell product ".$permalink;

    }

    public function the_product_upsell_callback() {

      if (empty($_POST['productID']))
        wp_die();

        $productID = (int) $_POST['productID'];

        $product = new WC_Product($productID);

        $upsells = $product->get_upsells();
        if (empty($upsells))
          wp_die();

        $upsells = $upsells[0];

        if (isset($_COOKIE['upsell_product_current']))
          setcookie('upsell_product_current', "", time()-(60*60*24), '/');

        $stat = setcookie('upsell_product_current', $upsells, time()+(60*60*24), '/');


        echo json_encode($stat);
        wp_die();

    }

    public function add_skip_button() {

      $funnely_io_meta_data = get_post_meta(get_the_ID(), 'funnely_io_meta', true);

      if (empty($funnely_io_meta_data))
        return;

        $funnely_io_meta_data = (array) $funnely_io_meta_data;

        $main_parent = "";

        foreach ($funnely_io_meta_data as $key => $single_data) {

          if (empty($single_data->isParent))
            continue;

          $main_parent = $single_data;

        }

        $skip_id = ( !empty($main_parent->down->targetID) ? $main_parent->down->targetID : $main_parent->up->targetID);

      echo "<a href='".get_permalink($skip_id)."'>Skip Product</a>";

    }

    public function enqueue_scripts() {

      $user_id = get_current_user_id();

      global $woocommerce;
      $checkout_url = $woocommerce->cart->get_checkout_url();

      wp_register_script( 'wc_funnel_nci-general-script', wc_funnel_nci_PLUGIN_URL.'js/script.js', array( 'jquery' ), '', false );
      wp_localize_script( 'wc_funnel_nci-general-script', 'plugin_data_wcf', array( 'ajax_url' => admin_url('admin-ajax.php'), 'checkout_url' => $checkout_url ));
      wp_enqueue_script( 'wc_funnel_nci-general-script' );


      wp_register_script( 'wc_funnel_nci-magnific-popup', wc_funnel_nci_PLUGIN_URL.'js/jquery.magnific-popup.min.js', array( 'jquery' ), '', false );
      wp_enqueue_script( 'wc_funnel_nci-magnific-popup' );

      wp_enqueue_style( 'wc_funnel_nci_magnific-popup_css', wc_funnel_nci_PLUGIN_URL."css/magnific-popup.css" );


    }


    public function test_func() {

d($_COOKIE);

        if (empty($_GET['funnelCheck']))
          return;

        $id = (int) $_GET['funnelCheck'];

        $product = new WC_Product($id);

        d($product->get_upsells());
        d($product->get_cross_sells());

        d(get_post_meta($id));

          d($_COOKIE['upsell_product_current']);
          d($_COOKIE);

        d($product);



    }

    function load_custom_wp_admin_style($hook) {
        // // Load only on ?page=mypluginname
        // if($hook != 'toplevel_page_mypluginname') {
        //         return;
        // }
        wp_enqueue_style( 'wc_funnel_nci_wp_admin_css', wc_funnel_nci_PLUGIN_URL."css/admin-style.css" );


        wp_register_script( 'wc_funnel_nci_wp_admin_js', wc_funnel_nci_PLUGIN_URL.'js/script_admin.js', array( 'jquery' ), '', false );

        wp_localize_script( 'wc_funnel_nci_wp_admin_js', 'plugin_wc_funnel_admin', array( 'ajax_url' => admin_url('admin-ajax.php') ));

        wp_enqueue_script( 'wc_funnel_nci_wp_admin_js' );


}


    public function get_instance() {

        $class = __CLASS__;

        return new $class;
    }

    public function load_plugin_template( $single_template ) {

        return "afcdsf";

    }

}
