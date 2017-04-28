<?php
if(!defined('WPINC')) // MUST have WordPress.
    exit('Do NOT access this file directly: '.basename(__FILE__));

    use \Stripe\Stripe as Stripe;
    use \Stripe\Charge as Charge;
    use \Stripe\Customer as Customer;


class Stripe_Connect {


  private static $instance;

  public static function get_instance() {
  	if ( ! isset( self::$instance ) ) {
  		self::$instance = new self();
  	}

  	return self::$instance;
  }


    function __construct()  {

      //add_action( 'wp_footer', array($this, 'footer_data') );
      add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
      add_action( 'template_redirect', array($this, 'receieve_stripe_data') );
      add_action( 'template_redirect', array($this, 'direct_buy') );

    }


    public function direct_buy() {

      $id = (int) $_GET['direct_buy'];

      if (empty($id))
        return;

      $titan = TitanFramework::getInstance( 'wc_funnel_nci_opts' );

      $stripe_sk = $titan->getOption('stripe_secret_key');

      Stripe::setApiKey($stripe_sk);


      $this_product = new WC_Product($id);
      $price = ( ( (float) $this_product->get_price() ) * 100 );

      $jsonData = stripslashes(html_entity_decode($_COOKIE['stripeCustomer']));

      $customer = json_decode($jsonData);

      //d($customer);

    try {

      $charge = Charge::create(array(
        "amount" => $price, // Amount in cents
        "currency" => 'usd',
        "customer" => $customer->id,
        "capture" => true
        ));

    } catch (Exception $e) {
      $charge = $e;
    }

    //d($charge);

    }


    public function footer_data() {

      // d($_COOKIE);
      // $customer = json_decode($_COOKIE['stripeCustomer']);
      // d(($_COOKIE['stripeCustomer']));
      //
      // $jsonData = stripslashes(html_entity_decode($_COOKIE['stripeCustomer']));
      //
      // $customer = json_decode($jsonData);
      //
      // d($customer);

      d(get_post_meta(get_the_ID(), 'funnely_io_meta', true));

    }


    public function enqueue_scripts() {

      wp_register_script( 'wc_funnel_nci-general-script-venobox', wc_funnel_nci_PLUGIN_URL.'js/venobox.min.js', array( 'jquery' ), '', false );
      wp_enqueue_script( 'wc_funnel_nci-general-script-venobox' );
      wp_enqueue_style( 'wc_funnel_nci_wp_admin_css-venobox', wc_funnel_nci_PLUGIN_URL."css/venobox.css" );
      wp_enqueue_style( 'wc_funnel_nci_wp_admin_css-style-general', wc_funnel_nci_PLUGIN_URL."css/style-general.css" );


      $titan = TitanFramework::getInstance( 'wc_funnel_nci_opts' );

      $pp_env = $titan->getOption('is_pp_sand');
      $pp_production_key = $titan->getOption('pp_production_c_api_key');
      $pp_sandbox_key = $titan->getOption('pp_sandbox_c_api_key');
      $blog_url = get_site_url();


      wp_register_script( 'wc_funnel_nci-general-script-stripe-custom', wc_funnel_nci_PLUGIN_URL.'js/stripe_paypal_custom.js', array( 'jquery' ), '', false );
      wp_localize_script( 'wc_funnel_nci-general-script-stripe-custom', 'plugin_data_pp_stripe_custom', array( 'ajax_url' => admin_url('admin-ajax.php'), 'pp_env' => $pp_env,

        'pp_production_key' => $pp_production_key,
        'pp_sandbox_key' => $pp_sandbox_key,
        'blog_url' => $blog_url
      ));
      wp_enqueue_script( 'wc_funnel_nci-general-script-stripe-custom' );


    }

    public static function stripe_render($data = null) {

      $titan = TitanFramework::getInstance( 'wc_funnel_nci_opts' );

      $stripe_enabled = $titan->getOption('is_stripe_enabled');

      if (empty($stripe_enabled))
        return;

      $data_key = $titan->getOption('stripe_publishable_key');

      if (empty($data_key))
        return;

      ob_start();
      $this_product = new WC_Product($data['product_id']);

      ?>

      <form action="<?php _e(get_permalink($data['upsell_id'])."?stripe_payment_submit=1&product_id=".$data['product_id'].""); ?>" method="POST">
        <script
          src="https://checkout.stripe.com/checkout.js" class="stripe-button"
          data-key="<?php _e($data_key); ?>"
          data-amount="<?php _e( ( (float) $this_product->get_price() ) * 100 ); ?>"
          data-name="Stripe.com"
          data-description="Widget"
          data-image="<?php $titan->getOption('stripe_checkout_img'); ?>"
          data-locale="auto"
          data-product_id="<?php _e($data['product_id']); ?>"
          data-zip-code="true">
        </script>
      </form>

      <?php
      $output = ob_get_clean();
      return $output;
    }


    public function receieve_stripe_data() {

      if (empty($_GET['stripe_payment_submit']))
        return;

      $product_id = $_GET['product_id'];

      $funnely_meta = get_post_meta($product_id, "funnely_io_meta", true);
      $tParent = 0;
      if (!empty($funnely_meta->$product_id)) {

        $isParent = (int) $funnely_meta->$product_id->isParent;

         if (!empty($isParent)) {
           $tParent = 1;

           setcookie('stripeCustomer',  "", time()-(60*60*24), '/');
         }


      }

        $titan = TitanFramework::getInstance( 'wc_funnel_nci_opts' );

        $stripe_sk = $titan->getOption('stripe_secret_key');

        if (empty($stripe_sk))
          return;

        Stripe::setApiKey($stripe_sk);


        $this_product = new WC_Product((int) $_GET['product_id']);
        $price = ( ( (float) $this_product->get_price() ) * 100 );

        $customer = Customer::create(array(
          "source" => $_POST['stripeToken'],
          "email" => $_POST['stripeEmail']
          )
        );

        //$charge = Charge::create(array('amount' => $price, 'currency' => 'usd', 'source' => $_POST['stripeToken'] ));

        if(!isset($_COOKIE['stripeCustomer']))
          setcookie('stripeCustomer',  json_encode($customer), time()+(60*60*24), '/');
        else {

          $jsonData = stripslashes(html_entity_decode($_COOKIE['stripeCustomer']));

          $customer = json_decode($jsonData);

        }


        try {

          $charge = Charge::create(array(
            "amount" => $price, // Amount in cents
            "currency" => 'usd',
            "customer" => $customer->id,
            "capture" => true
            ));

        } catch (Exception $e) {
          $charge = $e;
        }


        d($charge);

      //file_put_contents("testStripe02.txt", serialize($_POST)."\n\n", FILE_APPEND);
      //file_put_contents("testStripe02.txt", serialize($charge)."\n\n", FILE_APPEND);

    }

}

?>
