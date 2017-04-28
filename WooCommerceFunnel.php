<?php

/*
Plugin Name: WooCommerce Funnel
Plugin URI: https://www.upwork.com/companies/~01caf98798b24dd9af
Description: WooCommerce Funnel Plugin
Version: 0.0.1
Author: Ashik72
Author URI: https://www.upwork.com/companies/~01caf98798b24dd9af
License: GPLv2 or later
Text Domain: wc_funnel_nci
*/

if(!defined('WPINC')) // MUST have WordPress.
    exit('Do NOT access this file directly: '.basename(__FILE__));

if (!class_exists('Kint') && file_exists(dirname( __FILE__ ) . '/kint/Kint.class.php'))
    include_once ( dirname( __FILE__ ) . '/kint/Kint.class.php' );


if (!function_exists('d')) {

    function d($data) {

        ob_start();
        var_dump($data);
        $output = ob_get_clean();
        echo $output;
    }
}

define( 'wc_funnel_nci_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'wc_product_options_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'DS' ) ) define( 'DS', DIRECTORY_SEPARATOR );


if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    require_once( plugin_dir_path( __FILE__ ) . 'plugin_loader.php' );

}
