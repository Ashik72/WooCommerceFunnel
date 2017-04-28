<?php

if(!defined('WPINC')) // MUST have WordPress.
    exit('Do NOT access this file directly: '.basename(__FILE__));

/**
 * Funnel Table
 */
class FunnelWCAdminTable extends WP_List_Table
{

  static $instance;
	public $customers_obj;

  public function __construct()  {


    add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );


    parent::__construct( [
      'singular' => __( 'Funnel', 'wc_funnel_nci' ), //singular name of the listed records
      'plural'   => __( 'Funnels', 'wc_funnel_nci' ), //plural name of the listed records
      'ajax'     => false, //should this table support ajax?
    ] );


    }


    public static function get_funnels() {

      $titan = TitanFramework::getInstance( 'wc_funnel_nci_opts' );
      $wc_cs_key = $titan->getOption('wc_cs_key');
      $wc_cs_secret = $titan->getOption('wc_cs_secret');
      $wc_funnel_mail = $titan->getOption('wc_funnel_mail');
      $wc_funnel_password = $titan->getOption('wc_funnel_password');
      $wc_funnel_usecret = preg_replace("~[^a-z0-9:]~i", "", $titan->getOption('wc_funnel_usecret'));
      $site_url = get_site_url();

      if (empty($wc_cs_key) || empty($wc_cs_secret) || empty($wc_funnel_mail) || empty($wc_funnel_password) || empty($wc_funnel_usecret))
        return;

        $req_args = [
          'request_type' => 'get_funnels',
          'wc_cs_key' => $wc_cs_key,
          'wc_cs_secret' => $wc_cs_secret,
          'wc_funnel_mail' => $wc_funnel_mail,
          'wc_funnel_password' => $wc_funnel_password,
          'wc_funnel_usecret' => $wc_funnel_usecret,
          'site_url' => $site_url
        ];

        if (array_search("", $req_args) !== false )
          wp_die();


          $headers = array('Accept' => 'application/json');
          $query = $req_args;
          Unirest\Request::verifyPeer(false);
          $response = Unirest\Request::post('https://restdata.funnely.io', $headers, $query);
          $response->raw_body = json_decode($response->raw_body);

          if (!is_array($response->raw_body))
            return;

          $response_data = $response->raw_body;
          $response_return_data = array();


          foreach ($response_data as $key => $response_data_single) {

            $main_product_id = 0;

            $funnel_details = json_decode($response_data_single->funnel_detail);

            if (empty($funnel_details))
              continue;

              foreach ($funnel_details as $key => $funnel_detail) {

                if (!empty($funnel_detail->isParent)) {

                  $main_product = stripslashes($funnel_detail->el);

                  $main_product = DB_TASKS::parseTag($main_product, 'div');

                  $main_product_id = $main_product['data-product_id'];

                }
              }

            $response_return_data[] = [ $response_data_single->id, $response_data_single->funnel_name, $main_product_id ];

          }


          //$response_return_data = [[1 , 'funnel 1'], [2 , 'funnel 2'], [3 , 'funnel 3']];

          //d($response_return_data);

      return $response_return_data;
    }

    public static function record_count() {

      return count(self::get_funnels());
    }

    public function no_items() {
      _e( 'No funnel avaliable. Create one.', 'sp' );
    }


function column_name( $item ) {

  // create a nonce
  $delete_nonce = wp_create_nonce( 'sp_delete_customer' );

  $title = '<strong>' . $item['name'] . '</strong>';

  //$title = "my title";
  $actions = [
    'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
  ];

  return $title . $this->row_actions( $actions );
}

function column_view_funnels( $item ) {

  $url = ( empty($item[2]) ? "#" : get_permalink( $item[2] )) ;
  //return "<a href='?page=woocommerce-funnel-integration-funnel-design&funnel_id=".$item[0]."&nonce=".wp_create_nonce('funnel-edit_'.$item[0])."' data-product_id='".$item[0]."' class='funnel_edit'>Edit</a> | <a href='?page=woocommerce-funnel-integration-funnel-design&funnel_id=".$item[0]."&nonce=".wp_create_nonce('funnel-delete_'.$item[0])."' data-product_id='".$item[0]."' class='funnel_delete'>Delete</a> | <a href='?page=woocommerce-funnel-integration-funnel-design&funnel_id=".$item[0]."&nonce=".wp_create_nonce('funnel-view_'.$item[0])."' data-product_id='".$item[0]."' class='funnel_view'>View</a>";
  return "<a href='?page=woocommerce-funnel-integration-funnel-design&funnel_id=".$item[0]."&nonce=".wp_create_nonce('funnel-edit_'.$item[0])."' data-product_id='".$item[0]."' class='funnel_edit'>Edit</a> | <a href='?page=woocommerce-funnel-integration-funnel-design&funnel_id=".$item[0]."&nonce=".wp_create_nonce('funnel-delete_'.$item[0])."' data-product_id='".$item[0]."' class='funnel_delete'>Delete</a> | <a href='".$url."' data-product_id='".$item[0]."' class='funnel_view'>View</a>";

}

function column_funnels( $item ) {

  return $item[1];

}

public function column_default( $item, $column_name ) {

  switch ( $column_name ) {
     case 'address':
     case 'view_funnels':
      return $this->generate_menu($val);
     default:
       return print_r( $item, true ); //Show the whole array for troubleshooting purposes
   }
 }

 public function generate_menu() {


   return "";
 }


function get_columns() {
  $columns = [
    'funnels'    => __( 'Funnels', 'wc_funnel_nci' ),
    'view_funnels'    => __( 'Options', 'wc_funnel_nci' ),
  ];

  return $columns;
}


public function get_sortable_columns() {
  $sortable_columns = array(
    'funnels' => array( 'Funnels', true )
  );

  return $sortable_columns;
}


public function prepare_items() {

  $this->_column_headers = $this->get_column_info();

  /** Process bulk action */
  //$this->process_bulk_action();

  $per_page     = $this->get_items_per_page( 'customers_per_page', 5 );
  $current_page = $this->get_pagenum();
  $total_items  = self::record_count();
  //
  // $this->set_pagination_args( [
  //   'total_items' => $total_items, //WE have to calculate the total number of items
  //   'per_page'    => -1 //WE have to determine how many items to show on a page
  // ] );
  //

  $funnel_data = self::get_funnels();


  $this->items = $funnel_data;
}

    public static function set_screen( $status, $option, $value ) {
    	return $value;
    }


    public function plugin_menu() {

      $hook = add_submenu_page("woocommerce-funnel-integration", "Funnel List", "Funnel List", "manage_options", "funnel_list", function() { $this->load_funnel_list(); });


	     add_action( "load-$hook", [ $this, 'screen_option' ] );

  }

  private function load_funnel_list() {

    ?>
    <div class="wrap">
  		<h1>WC Funnel <a href="#" class="page-title-action add_new_funnel">Add New</a> </h1>
      <div class="wc_funnel_connection_status">Checking...</div>
  		<div id="poststuff">
  			<div id="post-body" class="metabox-holder columns-2">
  				<div id="post-body-content">
  					<div class="meta-box-sortables ui-sortable">
  						<form method="post">
  							<?php
  							$this->prepare_items();
  							$this->display(); ?>
  						</form>
  					</div>
  				</div>
  			</div>
  			<br class="clear">
  		</div>
  	</div>

    <?php

  }

  /**
* Screen options
*/
public function screen_option() {

	$option = 'per_page';
	$args   = [
		'label'   => 'Customers',
		'default' => 5,
		'option'  => 'customers_per_page'
	];

	add_screen_option( $option, $args );

	//$this->customers_obj = new Customers_List();

}

      /** Singleton instance */
    public static function get_instance() {
    	if ( ! isset( self::$instance ) ) {
    		self::$instance = new self();
    	}

    	return self::$instance;
    }

}



 ?>
