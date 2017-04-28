<?php

if(!defined('WPINC')) // MUST have WordPress.
    exit('Do NOT access this file directly: '.basename(__FILE__));

    require_once( 'titan-framework-checker.php' );
    require_once( 'titan-framework-options.php' );
    require_once( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php' );
    //require_once( plugin_dir_path( __FILE__ ) . '/wp-routes/wp-routes.php' );
    //require_once( plugin_dir_path( __FILE__ ) . '/wp-mvc/wp_mvc.php' );
    require_once( plugin_dir_path( __FILE__ ) . '/wp-router/wp-router.php' );

require_once( plugin_dir_path( __FILE__ ) . '/inc/class.WooCommerceFunnel.php' );
require_once( plugin_dir_path( __FILE__ ) . '/inc/wc-api-custom-meta.php' );
require_once( plugin_dir_path( __FILE__ ) . '/inc/class.DB_TASKS.php' );
//require_once( plugin_dir_path( __FILE__ ) . '/inc/class.DB_TASKS.php' );

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );//added
  require_once( ABSPATH . 'wp-admin/includes/screen.php' );//added
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
  require_once( ABSPATH . 'wp-admin/includes/template.php' );
}

require_once( plugin_dir_path( __FILE__ ) . '/inc/class.funnel_table.php' );
require_once( plugin_dir_path( __FILE__ ) . '/inc/class.Stripe_Connect.php' );
require_once( plugin_dir_path( __FILE__ ) . '/inc/class.Paypal_Connect.php' );

$WooCommerceFunnel = new WooCommerceFunnel();

add_action( 'plugins_loaded', function () {
	FunnelWCAdminTable::get_instance();
  DB_TASKS::get_instance();
  Stripe_Connect::get_instance();
  Paypal_Connect::get_instance();

} );

function create_routes( $router ) {

  $router->add_route('funnely', array(
    'path' => 'funnely',
    'access_callback' => true,
    'page_callback' => 'funnely_func'
  ));

}
//add_action( 'wp_router_generate_routes', 'create_routes' );

function funnely_func() {

  $product_id = (int) $_GET['product_id'];

  $plugin_path  = wc_product_options_PLUGIN_DIR . DS . 'templates'. DS;

  $type = $_GET['type'];

  if (empty($type))
    $template = $plugin_path . "main_template.php";
  elseif ($type == "up")
    $template = $plugin_path . "upsell.php";
  elseif ($type == "down")
      $template = $plugin_path . "downsell.php";

      //var_dump($type);

  include( $template );

  die();
}
